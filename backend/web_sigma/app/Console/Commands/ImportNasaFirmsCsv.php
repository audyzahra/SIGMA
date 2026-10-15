<?php

namespace App\Console\Commands;

use App\Models\DataSource;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

#[Signature('hotspots:import-nasa-csv
    {path? : Path to the NASA FIRMS CSV file}
    {--origin=nasa_firms_csv : Origin identifier}
    {--status=resolved : Valid status for historical observations}')]
#[Description('Import historical NASA FIRMS observations into SIGMA')]
class ImportNasaFirmsCsv extends Command
{
    public function handle(): int
    {
        $path = $this->argument('path')
            ?: base_path(
                '../../ai_service/ai_service_sigma/dataset/processed/fire_risk_dataset.csv'
            );

        if (! File::exists($path)) {
            $this->error("CSV file not found: {$path}");
            return self::FAILURE;
        }

        $status = $this->option('status');

        if (! in_array($status, ['active', 'resolved'], true)) {
            $this->error('Status must be active or resolved.');
            return self::FAILURE;
        }

        $source = DataSource::query()
            ->where('provider', 'NASA FIRMS')
            ->where('type', 'satellite')
            ->first();

        if (! $source) {
            $this->error('NASA FIRMS DataSource was not found.');
            return self::FAILURE;
        }

        $file = new \SplFileObject($path, 'r');
        $file->setFlags(
            \SplFileObject::READ_CSV |
            \SplFileObject::SKIP_EMPTY |
            \SplFileObject::DROP_NEW_LINE
        );

        $headers = $file->fgetcsv();

        if (! is_array($headers)) {
            $this->error('Unable to read CSV headers.');
            return self::FAILURE;
        }

        $headers = array_map(
            fn ($value) => strtolower(trim((string) $value)),
            $headers
        );

        foreach (['latitude', 'longitude', 'acq_date', 'acq_time'] as $column) {
            if (! in_array($column, $headers, true)) {
                $this->error("Required CSV column missing: {$column}");
                return self::FAILURE;
            }
        }

        $batch = [];
        $processed = 0;
        $skipped = 0;
        $now = now();

        foreach ($file as $values) {
            if (! is_array($values) || count($values) !== count($headers)) {
                $skipped++;
                continue;
            }

            $row = array_combine($headers, $values);

            if (! is_array($row)) {
                $skipped++;
                continue;
            }

            $latitude = $this->number($row['latitude'] ?? null);
            $longitude = $this->number($row['longitude'] ?? null);

            if (
                $latitude === null ||
                $longitude === null ||
                $latitude < -90 || $latitude > 90 ||
                $longitude < -180 || $longitude > 180
            ) {
                $skipped++;
                continue;
            }

            $date = trim((string) ($row['acq_date'] ?? ''));
            $time = $this->formatAcquisitionTime(
                $row['acq_time'] ?? null
            );

            if ($date === '' || $time === null) {
                $skipped++;
                continue;
            }

            try {
                $detectedAt = Carbon::createFromFormat(
                    '!Y-m-d H:i',
                    "{$date} {$time}",
                    'UTC'
                );
            } catch (\Throwable) {
                $skipped++;
                continue;
            }

            if (! $detectedAt) {
                $skipped++;
                continue;
            }

            $satellite = trim((string) ($row['satellite'] ?? 'UNKNOWN'));
            $instrument = trim((string) ($row['instrument'] ?? ''));

            $recordKey = hash('sha256', implode('|', [
                $satellite,
                $instrument,
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
                    $row['brightness'] ?? $row['bright_t31'] ?? null
                ),
                'confidence_level' => $this->number(
                    $row['confidence'] ?? null
                ),
                'frp' => $this->number($row['frp'] ?? null),
                'detected_at' => $detectedAt,
                'status' => $status,
                'data_origin' => $this->option('origin'),
                'record_key' => $recordKey,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) >= 1000) {
                $this->saveBatch($batch);
                $processed += count($batch);
                $batch = [];

                $this->info("Processed: {$processed}");
            }
        }

        if ($batch !== []) {
            $this->saveBatch($batch);
            $processed += count($batch);
        }

        $this->info("Processed/upserted: {$processed}");
        $this->info("Skipped rows: {$skipped}");

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
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        $number = (float) $value;

        return is_finite($number) ? $number : null;
    }

    private function formatAcquisitionTime(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $digits = (string) (int) ((float) $value);

        if (strlen($digits) > 4) {
            return null;
        }

        $digits = str_pad($digits, 4, '0', STR_PAD_LEFT);
        $hour = (int) substr($digits, 0, 2);
        $minute = (int) substr($digits, 2, 2);

        if ($hour > 23 || $minute > 59) {
            return null;
        }

        return sprintf('%02d:%02d', $hour, $minute);
    }
}
