<?php

namespace App\Console\Commands;

use App\Models\DataSource;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

class SyncNasaFirmsHotspots extends Command
{
    protected $signature = 'hotspots:sync-nasa-firms';

    protected $description = 'Sinkronisasi hotspot NASA FIRMS melalui SIGMA AI Service';

    public function handle(): int
    {
        $aiUrl = rtrim((string) config('services.ai.url'), '/');

        if ($aiUrl === '') {
            $this->error('AI_SERVICE_URL belum dikonfigurasi.');
            return self::FAILURE;
        }

        $source = DataSource::query()
            ->where('provider', 'NASA FIRMS')
            ->where('type', 'satellite')
            ->first();

        if (!$source) {
            $this->error('DataSource NASA FIRMS tidak ditemukan.');
            return self::FAILURE;
        }

        try {
            $response = Http::timeout(120)
                ->get($aiUrl . '/hotspots')
                ->throw();

            $payload = $response->json();

            if (
                !is_array($payload)
                || ($payload['success'] ?? false) !== true
                || !is_array($payload['hotspots'] ?? null)
            ) {
                $this->error('Respons AI Service tidak valid.');
                return self::FAILURE;
            }
        } catch (Throwable $e) {
            $this->error(
                'Gagal mengambil hotspot dari AI Service: ' . $e->getMessage()
            );
            return self::FAILURE;
        }

        $batch = [];
        $processed = 0;
        $skipped = 0;
        $cutoff = now('UTC')->subDays(3);

        foreach ($payload['hotspots'] as $row) {
            if (!is_array($row)) {
                $skipped++;
                continue;
            }

            $latitude = $this->number($row['latitude'] ?? null);
            $longitude = $this->number($row['longitude'] ?? null);
            $date = trim((string) ($row['acq_date'] ?? ''));
            $time = $this->formatAcquisitionTime($row['acq_time'] ?? null);

            if (
                $latitude === null || $longitude === null
                || abs($latitude) > 90 || abs($longitude) > 180
                || $time === null || $date === ''
            ) {
                $skipped++;
                continue;
            }

            try {
                $detectedAt = Carbon::createFromFormat(
                    '!Y-m-d H:i',
                    "{$date} {$time}",
                    'UTC'
                );
            } catch (Throwable) {
                $skipped++;
                continue;
            }

            if (!$detectedAt || $detectedAt->lt($cutoff)) {
                $skipped++;
                continue;
            }

            $satellite = trim(
                (string) ($row['satellite'] ?? 'NASA FIRMS')
            );

            $recordKey = hash('sha256', implode('|', [
                'AI_SERVICE_NASA_FIRMS',
                $satellite,
                $detectedAt->format('Y-m-d H:i'),
                sprintf('%.5f', $latitude),
                sprintf('%.5f', $longitude),
            ]));

            $batch[] = [
                'source_id' => $source->id,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'satellite_name' => $satellite,
                'brightness_temperature' => $this->number(
                    $row['brightness'] ?? null
                ),
                'confidence_level' => $this->confidence(
                    $row['confidence'] ?? null
                ),
                'frp' => $this->number($row['frp'] ?? null),
                'detected_at' => $detectedAt->format('Y-m-d H:i:s'),
                'status' => 'active',
                'data_origin' => 'nasa_firms_api',
                'record_key' => $recordKey,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= 500) {
                $this->saveBatch($batch);
                $processed += count($batch);
                $batch = [];
            }
        }

        if ($batch !== []) {
            $this->saveBatch($batch);
            $processed += count($batch);
        }

        $this->info('Baris hotspot diproses: ' . $processed);
        $this->info('Baris dilewati: ' . $skipped);

        return self::SUCCESS;
    }

    private function saveBatch(array $batch): void
    {
        DB::table('hotspots')->upsert(
            $batch,
            ['record_key'],
            [
                'source_id',
                'latitude',
                'longitude',
                'satellite_name',
                'brightness_temperature',
                'confidence_level',
                'frp',
                'detected_at',
                'status',
                'data_origin',
                'updated_at',
            ]
        );
    }

    private function number(mixed $value): ?float
    {
        if (
            $value === null
            || trim((string) $value) === ''
            || !is_numeric($value)
        ) {
            return null;
        }

        $number = (float) $value;

        return is_finite($number) ? $number : null;
    }

    private function confidence(mixed $value): ?int
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) round((float) $value);
        }

        return match (strtolower(trim((string) $value))) {
            'l' => 30,
            'n' => 70,
            'h' => 90,
            default => null,
        };
    }

    private function formatAcquisitionTime(mixed $value): ?string
    {
        if ($value === null || !is_numeric($value)) {
            return null;
        }

        $digits = str_pad(
            (string) (int) $value,
            4,
            '0',
            STR_PAD_LEFT
        );

        if (strlen($digits) !== 4) {
            return null;
        }

        $hour = (int) substr($digits, 0, 2);
        $minute = (int) substr($digits, 2, 2);

        if ($hour > 23 || $minute > 59) {
            return null;
        }

        return sprintf('%02d:%02d', $hour, $minute);
    }
}
