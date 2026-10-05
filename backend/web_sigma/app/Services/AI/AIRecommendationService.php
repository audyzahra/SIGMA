<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;

/**
 * AI Recommendation Service — siap dipakai, penyedia belum dipasang.
 *
 * Modul Prioritas Penanganan HANYA menyediakan data. Kelas ini menetapkan
 * kontrak data yang stabil supaya penambahan model bahasa (Gemini/LLM) nanti
 * tidak mengubah modul prioritas, controller, maupun tampilan:
 *
 *   INPUT
 *   {
 *     region, risk_score, impact_score, weather, land_cover, population
 *   }
 *
 *   OUTPUT
 *   {
 *     recommendation, priority_action
 *   }
 *
 * Selama config('sigma_priority.ai_recommendation.enabled') masih false,
 * recommend() mengembalikan status + recommendation/priority_action bernilai
 * null sehingga UI menampilkan "Data belum tersedia" (bukan teks karangan).
 *
 * Titik sambung penyedia: method requestProvider() di bawah. Mengaktifkan
 * Gemini cukup dengan mengisi endpoint pada config dan mengimplementasikan
 * satu method tersebut.
 */
class AIRecommendationService
{
    public const STATUS_DISABLED = 'disabled';

    public const STATUS_ENDPOINT_MISSING = 'endpoint_missing';

    public const STATUS_NOT_IMPLEMENTED = 'not_implemented';

    public function enabled(): bool
    {
        return (bool) config('sigma_priority.ai_recommendation.enabled', false);
    }

    public function provider(): ?string
    {
        return config('sigma_priority.ai_recommendation.provider');
    }

    public function endpoint(): ?string
    {
        return config('sigma_priority.ai_recommendation.endpoint');
    }

    /**
     * Susun masukan AI dari data detail prioritas satu wilayah.
     *
     * Nilai yang datasetnya belum tersedia tetap dikirim sebagai struktur
     * dengan available = false, supaya penyedia rekomendasi dapat membedakan
     * "nol" dari "belum ada data".
     *
     * @param  array<string, mixed>  $detail  keluaran PriorityCalculationService::detail()
     * @return array<string, mixed>
     */
    public function payload(array $detail): array
    {
        $region = (array) ($detail['region'] ?? []);
        $risk = (array) ($detail['risk'] ?? []);
        $impact = (array) ($detail['impact'] ?? []);
        $priority = (array) ($detail['priority'] ?? []);
        $sources = (array) ($detail['sources'] ?? []);

        return [
            'region' => [
                'id' => $region['id'] ?? null,
                'name' => $region['name'] ?? null,
                'code' => $region['code'] ?? null,
                'level' => $region['level'] ?? null,
                'parent' => $region['parent_name'] ?? null,
            ],

            'risk_score' => $risk['score'] ?? null,

            'impact_score' => $impact['score'] ?? null,

            'weather' => $risk['weather'] ?? null,

            'land_cover' => $this->datasetState('land_cover', $sources),

            'population' => $this->datasetState('population', $sources),

            /* Konteks tambahan yang sudah tersedia, tidak mengubah kontrak */
            'priority_score' => $priority['score'] ?? null,
            'priority_level' => $priority['level'] ?? null,
        ];
    }

    /**
     * Minta rekomendasi tindakan untuk satu wilayah.
     *
     * @param  array<string, mixed>  $payload  keluaran payload()
     * @return array{available: bool, status: string, provider: string|null, recommendation: string|null, priority_action: string|null}
     */
    public function recommend(array $payload): array
    {
        if (! $this->enabled()) {
            return $this->result(self::STATUS_DISABLED);
        }

        if ($this->endpoint() === null || $this->endpoint() === '') {
            return $this->result(self::STATUS_ENDPOINT_MISSING);
        }

        $response = $this->requestProvider($payload);

        if ($response === null) {
            return $this->result(self::STATUS_NOT_IMPLEMENTED);
        }

        return [
            'available' => true,
            'status' => 'available',
            'provider' => $this->provider(),
            'recommendation' => $response['recommendation'] ?? null,
            'priority_action' => $response['priority_action'] ?? null,
        ];
    }

    /**
     * Panggilan ke penyedia rekomendasi (Gemini/LLM).
     *
     * BELUM DIIMPLEMENTASIKAN sesuai keputusan modul: modul prioritas dulu
     * hanya menyiapkan struktur data. Saat penyedia diaktifkan, cukup isi
     * method ini agar mengembalikan:
     *   ['recommendation' => string, 'priority_action' => string]
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null
     */
    protected function requestProvider(array $payload): ?array
    {
        Log::notice('AI Recommendation diminta, tetapi penyedia belum dipasang.', [
            'provider' => $this->provider(),
            'region' => $payload['region']['name'] ?? null,
        ]);

        return null;
    }

    /**
     * Struktur keluaran standar ketika rekomendasi belum tersedia.
     *
     * @return array{available: bool, status: string, provider: string|null, recommendation: string|null, priority_action: string|null}
     */
    protected function result(string $status): array
    {
        return [
            'available' => false,
            'status' => $status,
            'provider' => $this->provider(),
            'recommendation' => null,
            'priority_action' => null,
        ];
    }

    /**
     * Status ketersediaan satu dataset (penduduk, tutupan lahan, ...).
     *
     * @param  array<string, mixed>  $sources
     * @return array<string, mixed>
     */
    protected function datasetState(string $key, array $sources): array
    {
        $source = (array) ($sources[$key] ?? []);

        return [
            'key' => $key,
            'label' => $source['label'] ?? $key,
            'available' => (bool) ($source['available'] ?? false),
            'note' => $source['note'] ?? null,
        ];
    }
}
