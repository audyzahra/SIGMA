<?php

namespace App\Services\AI;

use App\Models\FireRisk;
use App\Models\FireRiskHistory;
use App\Models\Region;
use App\Services\GIS\RegionGeometry;
use Illuminate\Support\Facades\Log;

/**
 * Jembatan Laravel -> AI Service -> tabel fire_risks.
 *
 * Alur:
 *   Region (geometry)
 *     -> centroid (RegionGeometry)
 *     -> AIService::predictRegion()
 *     -> FastAPI (NASA POWER + NASA FIRMS + fire_risk_model.pkl)
 *     -> normalisasi risk_level
 *     -> fire_risks + fire_risk_histories
 *
 * Catatan: kolom latitude/longitude TIDAK ditambahkan ke tabel regions;
 * koordinat selalu dihitung dari geometry yang sudah ada.
 */
class FireRiskPredictionService
{
    /**
     * Skor risiko per level (konvensi data fire_risks SIGMA).
     */
    public const RISK_SCORES = [
        'LOW' => 30,
        'MEDIUM' => 60,
        'HIGH' => 90,
    ];

    /**
     * Level yang didukung enum kolom fire_risks.risk_level.
     */
    public const SUPPORTED_LEVELS = ['LOW', 'MEDIUM', 'HIGH'];

    protected AIService $ai;

    public function __construct(AIService $ai)
    {
        $this->ai = $ai;
    }

    /**
     * Prediksi satu wilayah memakai koordinat dari geometry.
     *
     * Mengembalikan null jika wilayah tidak punya geometry atau
     * AI Service tidak dapat dihubungi.
     */
    public function predict(Region $region, bool $persist = true): ?array
    {
        $center = RegionGeometry::centerForRegion($region);

        if (! $center) {

            Log::warning('Centroid wilayah tidak tersedia', [
                'region_id' => $region->id,
            ]);

            return null;

        }

        $result = $this->ai->predictRegion(
            $center['latitude'],
            $center['longitude']
        );

        return $this->store($region, $result, $center, $persist);
    }

    /**
     * Normalisasi hasil AI + simpan ke database.
     *
     * @param  array  $result  response AIService::predictRegion()
     * @param  array|null  $center  ['latitude' => float, 'longitude' => float]
     */
    public function store(
        Region $region,
        array $result,
        ?array $center = null,
        bool $persist = true
    ): ?array {

        if (! ($result['success'] ?? false)) {
            return null;
        }

        $level = $this->normalizeLevel($result['risk_level'] ?? null);

        if ($level === null) {

            Log::warning('risk_level AI tidak dikenali', [
                'region_id' => $region->id,
                'risk_level' => $result['risk_level'] ?? null,
            ]);

            return null;

        }

        $score = self::RISK_SCORES[$level];

        $attributes = [
            'risk_score' => $score,
            'risk_level' => $level,
            'calculated_at' => now(),
        ];

        if (isset($result['confidence']) && is_numeric($result['confidence'])) {
            $attributes['ai_confidence'] = round((float) $result['confidence'], 2);
        }

        /*
        |--------------------------------------------------------------------------
        | Parameter cuaca
        |--------------------------------------------------------------------------
        |
        | Nilai diambil apa adanya dari AI Service (NASA POWER / fallback).
        |
        | - rainfall 0 mm adalah nilai yang sah.
        | - temperature / humidity / wind_speed 0 hanya ditulis jika sumber
        |   cuaca memang dipercaya (NASA_POWER / CSV_FALLBACK).
        |
        */

        $weatherSource = $result['data_source']['weather'] ?? null;

        $trustedWeather = in_array(
            $weatherSource,
            ['NASA_POWER', 'CSV_FALLBACK'],
            true
        );

        foreach (['temperature', 'humidity', 'rainfall', 'wind_speed'] as $parameter) {

            $value = $result[$parameter] ?? null;

            if ($value === null || ! is_numeric($value)) {
                continue;
            }

            $value = (float) $value;

            if ($value == 0.0 && ! $trustedWeather && $parameter !== 'rainfall') {
                continue;
            }

            $attributes[$parameter] = $value;

        }

        $fireRisk = null;

        if ($persist) {

            $fireRisk = FireRisk::updateOrCreate(
                ['region_id' => $region->id],
                $attributes
            );

            FireRiskHistory::create([
                'region_id' => $region->id,
                'risk_score' => $score,
                'risk_level' => $level,
                'calculated_at' => $attributes['calculated_at'],
            ]);

        }

        return [
            'region_id' => $region->id,
            'name' => $region->name,
            'level' => $region->level,
            'risk_score' => $score,
            'risk_level' => $level,
            'temperature' => $attributes['temperature'] ?? null,
            'humidity' => $attributes['humidity'] ?? null,
            'wind_speed' => $attributes['wind_speed'] ?? null,
            'rainfall' => $attributes['rainfall'] ?? null,
            'confidence' => $attributes['ai_confidence'] ?? null,
            'calculated_at' => $attributes['calculated_at']->format('Y-m-d H:i:s'),
            'latitude' => isset($center['latitude'])
                ? round($center['latitude'], 4)
                : null,
            'longitude' => isset($center['longitude'])
                ? round($center['longitude'], 4)
                : null,
            'data_source' => $result['data_source'] ?? null,
            'hotspot' => $result['hotspot'] ?? null,
            'fire_risk_id' => $fireRisk?->id,
        ];
    }

    /**
     * Normalisasi risk_level model ke enum database.
     *
     * Enum fire_risks hanya mendukung LOW/MEDIUM/HIGH. Jika model
     * mengembalikan EXTREME maka dipetakan ke HIGH supaya tidak
     * menyebabkan SQL enum error.
     */
    public function normalizeLevel(mixed $level): ?string
    {
        $normalized = strtoupper(trim((string) $level));

        if ($normalized === 'EXTREME') {

            Log::warning('risk_level EXTREME dipetakan ke HIGH (enum database)');

            $normalized = 'HIGH';

        }

        return in_array($normalized, self::SUPPORTED_LEVELS, true)
            ? $normalized
            : null;
    }
}

