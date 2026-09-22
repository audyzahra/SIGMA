<?php

namespace App\Http\Controllers\Api\Citizen;

use App\Http\Controllers\Controller;
use App\Http\Requests\Citizen\Report\StoreReportRequest;
use App\Http\Resources\Citizen\ReportResource;
use App\Models\CitizenReport;
use App\Services\Citizen\CitizenReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private readonly CitizenReportService $reports) {}

    public function index(Request $request): JsonResponse
    {
        $reports = $this->reports->forUser($request->user());

        return response()->json([
            'statistics' => $this->reports->statistics($reports),
            'reports' => ReportResource::collection($reports)->resolve(),
        ]);
    }

    public function show(Request $request, CitizenReport $report): JsonResponse
    {
        abort_unless($report->user_id === $request->user()->id, 404);
        $report->load(['incident', 'statusHistories.updater']);

        return response()->json(['report' => (new ReportResource($report))->resolve()]);
    }

    public function store(StoreReportRequest $request): JsonResponse
    {
        $report = $this->reports->create($request->user(), $request->safe()->only([
            'report_type', 'latitude', 'longitude', 'description',
        ]));

        return response()->json([
            'message' => 'Laporan berhasil dikirim.',
            'report' => (new ReportResource($report))->resolve(),
        ], 201);
    }
}
