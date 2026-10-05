<?php

namespace App\Console\Commands;

use App\Services\PriorityCalculationService;
use Illuminate\Console\Command;

/**
 * Hitung ulang Prioritas Penanganan.
 *
 * Memakai PriorityCalculationService yang sama dengan tombol
 * "Hitung Ulang Prioritas" di halaman Government, sehingga hasil CLI dan web
 * selalu identik. Aman dijadwalkan (mis. setelah impor dataset hotspot,
 * cuaca, atau hasil analisis AI/GIS selesai).
 */
class RecalculatePriority extends Command
{
    protected $signature = 'priority:recalculate
                            {--region=* : ID wilayah tertentu (boleh diulang, kosong = seluruh wilayah)}';

    protected $description = 'Hitung ulang skor dan ranking prioritas penanganan (tabel priority_results)';

    public function handle(PriorityCalculationService $priority): int
    {
        $regionIds = array_values(array_filter(
            array_map('intval', (array) $this->option('region'))
        ));

        $this->info(
            $regionIds === []
                ? 'Menghitung prioritas seluruh wilayah ...'
                : 'Menghitung prioritas ' . count($regionIds) . ' wilayah terpilih ...'
        );

        $result = $priority->recalculate($regionIds === [] ? null : $regionIds);

        $rows = [
            ['Wilayah dihitung', (string) $result['processed']],
            ['Tanpa data (tidak diranking)', (string) $result['skipped']],
        ];

        foreach ($priority->levels() as $key => $meta) {
            $rows[] = ['Wilayah ' . $meta['label'], (string) ($result['levels'][$key] ?? 0)];
        }

        $rows[] = ['Durasi', $result['duration_seconds'] . ' detik'];
        $rows[] = ['Versi metodologi', $result['methodology_version']];
        $rows[] = ['Waktu perhitungan', $result['calculated_at']->toDateTimeString()];

        $this->table(['Keterangan', 'Nilai'], $rows);

        if (! empty($result['top'])) {
            $this->info(
                'Peringkat 1: '
                . $result['top']['_region_name']
                . ' (skor ' . $result['top']['priority_score'] . ')'
            );
        }

        $this->info($result['message']);

        return Command::SUCCESS;
    }
}
