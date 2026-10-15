<?php

namespace App\Http\Controllers\Api\Citizen;

use App\Http\Controllers\Controller;
use App\Http\Requests\Citizen\Report\StoreReportRequest;
use App\Http\Resources\Citizen\ReportResource;
use App\Models\AiModel;
use App\Models\AiReportVerification;
use App\Models\CitizenReport;
use App\Services\Citizen\CitizenReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ReportController extends Controller
{
    public function __construct(
        private readonly CitizenReportService $reports
    ) {}

    public function index(Request $request): JsonResponse
    {
        $reports = $this->reports->forUser($request->user());

        return response()->json([
            'statistics' => $this->reports->statistics($reports),
            'reports' => ReportResource::collection($reports)->resolve(),
        ]);
    }

    public function show(
        Request $request,
        CitizenReport $report
    ): JsonResponse {
        abort_unless($report->user_id === $request->user()->id, 404);

        $report->load(['incident', 'statusHistories.updater']);

        $data = (new ReportResource($report))->resolve();

        $verification = AiReportVerification::query()
            ->where('citizen_report_id', $report->id)
            ->latest('id')
            ->first();

        $annotatedPath = $verification?->annotated_image_path;

        $data['annotated_photo_url'] = $annotatedPath &&
                Storage::disk('public')->exists($annotatedPath)
            ? url("/api/citizen/reports/{$report->id}/photo")
                .'?type=annotated'
            : null;

        return response()->json([
            'report' => $data,
        ]);
    }

    public function photo(Request $request, CitizenReport $report)
    {
        abort_unless($report->user_id === $request->user()->id, 404);

        $type = $request->query('type', 'original');

        if ($type === 'annotated') {
            $verification = AiReportVerification::query()
                ->where('citizen_report_id', $report->id)
                ->latest('id')
                ->first();

            $path = $verification?->annotated_image_path;

            abort_unless(
                $path && Storage::disk('public')->exists($path),
                404
            );

            return response()->file(
                Storage::disk('public')->path($path),
                [
                    'Content-Type' => 'image/jpeg',
                    'Cache-Control' => 'private, max-age=3600',
                ]
            );
        }

        abort_unless($report->photo, 404);

        $path = Storage::disk('public')->path($report->photo);

        abort_unless(is_file($path), 404);

        return response()->file($path, [
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public function store(StoreReportRequest $request): JsonResponse
    {
        $photo = $request->file('photo');

        $report = $this->reports->create(
            $request->user(),
            $request->safe()->only([
                'report_type',
                'latitude',
                'longitude',
                'description',
            ]),
            $photo,
        );

        $this->analyzeReportPhoto($report, $photo);

        $report->refresh();

        $data = (new ReportResource($report))->resolve();

        $verification = AiReportVerification::query()
            ->where('citizen_report_id', $report->id)
            ->latest('id')
            ->first();

        $annotatedPath = $verification?->annotated_image_path;

        $data['annotated_photo_url'] = $annotatedPath &&
                Storage::disk('public')->exists($annotatedPath)
            ? url("/api/citizen/reports/{$report->id}/photo")
                .'?type=annotated'
            : null;

        return response()->json([
            'message' => 'Laporan berhasil dikirim.',
            'report' => $data,
        ], 201);
    }

    private function analyzeReportPhoto(
        CitizenReport $report,
        $photo
    ): void {
        try {
            $aiModel = AiModel::query()
                ->where('code', 'FIRE-SMOKE-DETECTION-YOLO11-V1')
                ->where('status', 'testing')
                ->first();

            if (! $aiModel || ! $photo || ! $photo->isValid()) {
                return;
            }

            $response = Http::timeout(30)
                ->connectTimeout(5)
                ->attach(
                    'file',
                    file_get_contents($photo->getRealPath()),
                    $photo->getClientOriginalName()
                )
                ->post($aiModel->endpoint_url);

            if (! $response->successful()) {
                Log::warning('YOLO detection request failed.', [
                    'report_id' => $report->id,
                    'http_status' => $response->status(),
                ]);

                return;
            }

            $payload = $response->json('data');

            if (! is_array($payload)) {
                Log::warning('YOLO response format is invalid.', [
                    'report_id' => $report->id,
                ]);

                return;
            }

            $detections = $payload['detections'] ?? [];

            $fireConfidence = collect($detections)
                ->where('class_name', 'Fire')
                ->max('confidence');

            $smokeConfidence = collect($detections)
                ->where('class_name', 'Smoke')
                ->max('confidence');

            if ($fireConfidence !== null) {
                $prediction = 'fire';
                $confidence = $fireConfidence;
            } elseif ($smokeConfidence !== null) {
                $prediction = 'smoke';
                $confidence = $smokeConfidence;
            } else {
                $prediction = 'uncertain';
                $confidence = null;
            }

            $annotatedImagePath = $this->saveAnnotatedImage(
                $report->id,
                $payload['annotated_image_base64'] ?? null
            );

            AiReportVerification::create([
                'citizen_report_id' => $report->id,
                'model_id' => $aiModel->id,
                'prediction_result' => $prediction,
                'confidence_score' => $confidence !== null
                    ? round($confidence * 100, 2)
                    : null,
                'detected_objects' => [
                    'model' => $payload['model'] ?? $aiModel->model_file,
                    'fire_detected' => (bool) (
                        $payload['fire_detected'] ?? false
                    ),
                    'smoke_detected' => (bool) (
                        $payload['smoke_detected'] ?? false
                    ),
                    'detections' => $detections,
                ],
                'annotated_image_path' => $annotatedImagePath,
                'verification_status' => 'reviewed',
            ]);
        } catch (Throwable $exception) {
            Log::warning('YOLO report analysis failed.', [
                'report_id' => $report->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function saveAnnotatedImage(
        int $reportId,
        mixed $base64Image
    ): ?string {
        if (! is_string($base64Image) || $base64Image === '') {
            Log::warning('YOLO annotated image is missing.', [
                'report_id' => $reportId,
            ]);

            return null;
        }

        if (str_contains($base64Image, ',')) {
            $base64Image = substr(
                $base64Image,
                strpos($base64Image, ',') + 1
            );
        }

        $imageBytes = base64_decode($base64Image, true);

        if (
            $imageBytes === false ||
            strlen($imageBytes) === 0 ||
            @getimagesizefromstring($imageBytes) === false
        ) {
            Log::warning('YOLO annotated image is invalid.', [
                'report_id' => $reportId,
            ]);

            return null;
        }

        $imageInfo = getimagesizefromstring($imageBytes);

        if (
            $imageInfo === false ||
            $imageInfo['mime'] !== 'image/jpeg'
        ) {
            Log::warning('YOLO annotated image is not a JPEG.', [
                'report_id' => $reportId,
            ]);

            return null;
        }

        $path = "ai-detections/report_{$reportId}.jpg";

        $saved = Storage::disk('public')->put($path, $imageBytes);

        if (! $saved) {
            Log::warning('Failed to save YOLO annotated image.', [
                'report_id' => $reportId,
            ]);

            return null;
        }

        return $path;
    }
}
