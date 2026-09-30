<?php

namespace Database\Seeders;

use App\Models\Incident;
use App\Services\Impact\ImpactAnalysisService;
use Illuminate\Database\Seeder;

/**
 * Analisis dampak awal untuk insiden yang sudah ada.
 *
 * CATATAN PENTING
 * Seeder ini TIDAK mengisi angka dampak secara manual. Nilai seperti jumlah
 * penduduk terdampak, rumah tangga, luas hutan, atau impact score tidak boleh
 * dikarang karena akan tampak sebagai hasil analisis nyata di dashboard.
 *
 * Yang dilakukan: menjalankan ImpactAnalysisService (perhitungan spatial
 * MySQL + data nyata) untuk setiap insiden, lalu menyimpan hasilnya
 * (impact_assessments) apa adanya — termasuk laporan bahwa dataset
 * penduduk/permukiman belum tersedia.
 */
class ImpactAssessmentSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(ImpactAnalysisService::class);

        $radius = $service->defaultRadius();

        $incidents = Incident::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('id')
            ->take(5)
            ->get();

        foreach ($incidents as $incident) {

            $analysis = $service->analyzeForIncident($incident, $radius);

            if (! ($analysis['success'] ?? false)) {

                continue;

            }

            $service->persist($analysis, $incident->id);

        }
    }
}

