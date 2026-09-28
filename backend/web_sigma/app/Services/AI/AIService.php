<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Client untuk SIGMA AI Service (Python / FastAPI).
 *
 * Endpoint AI Service yang dipakai (lihat ai_service/ai_service_sigma/main.py):
 *
 *  GET  /               -> health check
 *  POST /predict-risk   -> prediksi dari fitur lengkap
 *  POST /predict-region -> prediksi dari koordinat wilayah
 *
 * Semua method mengembalikan array dan TIDAK melempar exception, sehingga
 * halaman tidak pernah crash ketika AI Service sedang offline.
 */
class AIService
{
    protected string $url;

    protected int $timeout;

    public function __construct()
    {
        $this->url = rtrim(
            (string) config('services.ai.url', 'http://127.0.0.1:8000'),
            '/'
        );

        $this->timeout = (int) config('services.ai.timeout', 20);
    }

    /**
     * URL AI Service yang sedang dipakai.
     */
    public function url(): string
    {
        return $this->url;
    }

    /**
     * Health check AI Service (GET /).
     */
    public function health(): array
    {
        try {

            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->get($this->url . '/');

            if ($response->successful()) {

                return [
                    'online' => true,
                    'message' => 'AI Service online',
                    'data' => $response->json(),
                ];

            }

            return [
                'online' => false,
                'message' => 'AI Service membalas HTTP ' . $response->status(),
                'data' => null,
            ];

        } catch (Throwable $e) {

            return [
                'online' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ];

        }
    }

    /**
     * Prediksi memakai fitur lengkap (POST /predict-risk).
     */
    public function predictRisk(array $data): array
    {
        return $this->post(
            '/predict-risk',
            $data,
            'AI Service response error'
        );
    }

    /**
     * Prediksi berdasarkan koordinat wilayah (POST /predict-region).
     */
    public function predictRegion(float $latitude, float $longitude): array
    {
        return $this->post(
            '/predict-region',
            [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ],
            'AI Region prediction error'
        );
    }

    /**
     * Request POST ke AI Service dengan timeout dan error handling.
     */
    protected function post(string $path, array $payload, string $fallbackMessage): array
    {
        try {

            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->asJson()
                ->post($this->url . $path, $payload);

            if ($response->successful()) {

                $data = $response->json();

                if (! is_array($data)) {

                    $data = [];

                }

                return array_merge(['success' => true], $data);

            }

            return [
                'success' => false,
                'risk_level' => 'UNKNOWN',
                'confidence' => 0,
                'message' => $fallbackMessage . ' (HTTP ' . $response->status() . ')',
            ];

        } catch (Throwable $e) {

            return [
                'success' => false,
                'risk_level' => 'UNKNOWN',
                'confidence' => 0,
                'message' => 'AI Service tidak dapat dihubungi (' . $this->url . ')',
                'error' => $e->getMessage(),
            ];

        }
    }
}
