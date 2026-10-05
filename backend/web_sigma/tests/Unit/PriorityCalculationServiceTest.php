<?php

namespace Tests\Unit;

use App\Services\PriorityCalculationService;
use PHPUnit\Framework\TestCase;

/**
 * Uji mesin perhitungan prioritas (PriorityCalculationService).
 *
 * Pengujian ini murni logika: service dibangun dengan konfigurasi eksplisit
 * dan tidak menyentuh database, sehingga rumus skor, penormalan ulang bobot,
 * dan ambang kategori dapat diuji terpisah dari data produksi.
 */
class PriorityCalculationServiceTest extends TestCase
{
    protected function service(array $config = []): PriorityCalculationService
    {
        return new PriorityCalculationService($config);
    }

    public function test_priority_score_memakai_bobot_risiko_60_dan_dampak_40(): void
    {
        $result = $this->service()->calculate(80.0, 50.0);

        /* 80 x 0.6 + 50 x 0.4 = 68 */
        $this->assertSame(68.0, $result['score']);
        $this->assertSame('high', $result['level']);
        $this->assertSame(100.0, $result['data_completeness']);
        $this->assertSame([], $result['unavailable']);
    }

    public function test_bobot_dapat_diubah_melalui_konfigurasi(): void
    {
        $service = $this->service([
            'weights' => [
                'risk' => 0.3,
                'impact' => 0.7,
            ],
        ]);

        /* 100 x 0.3 + 0 x 0.7 = 30 */
        $this->assertSame(30.0, $service->calculate(100.0, 0.0)['score']);
    }

    public function test_bobot_dinormalisasi_bila_totalnya_tidak_satu(): void
    {
        $service = $this->service([
            'weights' => [
                'risk' => 3,
                'impact' => 7,
            ],
        ]);

        $this->assertSame(['risk' => 0.3, 'impact' => 0.7], $service->weights());
    }

    public function test_bobot_dampak_dinormalisasi_ulang_bila_dampak_belum_tersedia(): void
    {
        $result = $this->service()->calculate(88.0, null);

        /* Hanya risiko tersedia: bobot risiko menjadi 100% */
        $this->assertSame(88.0, $result['score']);
        $this->assertSame('critical', $result['level']);
        $this->assertSame(60.0, $result['data_completeness']);
        $this->assertSame(1.0, $result['effective_weights']['risk']);
        $this->assertSame(['impact'], $result['unavailable']);
        $this->assertFalse($result['components']['impact']['available']);
        $this->assertNull($result['components']['impact']['contribution']);
    }

    public function test_bobot_risiko_dinormalisasi_ulang_bila_risiko_belum_tersedia(): void
    {
        $result = $this->service()->calculate(null, 40.0);

        $this->assertSame(40.0, $result['score']);
        $this->assertSame('medium', $result['level']);
        $this->assertSame(40.0, $result['data_completeness']);
        $this->assertSame(['risk'], $result['unavailable']);
    }

    public function test_tanpa_data_tidak_menghasilkan_skor(): void
    {
        $result = $this->service()->calculate(null, null);

        $this->assertNull($result['score']);
        $this->assertNull($result['level']);
        $this->assertSame(0.0, $result['data_completeness']);
        $this->assertSame(['risk', 'impact'], $result['unavailable']);
        $this->assertSame([], $result['effective_weights']);
    }

    public function test_kontribusi_komponen_mengikuti_bobot_efektif(): void
    {
        $result = $this->service()->calculate(80.0, 50.0);

        $this->assertSame(48.0, $result['components']['risk']['contribution']);
        $this->assertSame(20.0, $result['components']['impact']['contribution']);
        $this->assertSame('Risiko', $result['components']['risk']['label']);
        $this->assertSame('Dampak', $result['components']['impact']['label']);
    }

    public function test_kategori_prioritas_mengikuti_ambang_80_60_40(): void
    {
        $service = $this->service();

        $this->assertSame('critical', $service->levelFromScore(80.0));
        $this->assertSame('critical', $service->levelFromScore(100.0));
        $this->assertSame('high', $service->levelFromScore(79.99));
        $this->assertSame('high', $service->levelFromScore(60.0));
        $this->assertSame('medium', $service->levelFromScore(59.99));
        $this->assertSame('medium', $service->levelFromScore(40.0));
        $this->assertSame('low', $service->levelFromScore(39.99));
        $this->assertSame('low', $service->levelFromScore(0.0));
        $this->assertNull($service->levelFromScore(null));
    }

    public function test_ambang_kategori_dapat_diubah_melalui_konfigurasi(): void
    {
        $service = $this->service([
            'levels' => [
                'critical' => ['threshold' => 90],
            ],
        ]);

        $this->assertSame('high', $service->levelFromScore(85.0));
        $this->assertSame('critical', $service->levelFromScore(95.0));
    }

    public function test_label_dan_lencana_kategori_berasal_dari_konfigurasi(): void
    {
        $service = $this->service();

        $this->assertSame('Kritis', $service->levelLabel('critical'));
        $this->assertSame('Tinggi', $service->levelLabel('high'));
        $this->assertSame('Sedang', $service->levelLabel('medium'));
        $this->assertSame('Rendah', $service->levelLabel('low'));
        $this->assertSame('priority-kritis', $service->levelBadge('critical'));
        $this->assertSame('Belum dihitung', $service->levelLabel(null));
    }

    public function test_urutan_kategori_mengikuti_konfigurasi(): void
    {
        $this->assertSame(
            ['critical', 'high', 'medium', 'low'],
            array_keys($this->service()->levels())
        );
    }

    public function test_versi_metodologi_dapat_ditimpa(): void
    {
        $this->assertSame('priority-hybrid-v1.0', $this->service()->methodologyVersion());

        $this->assertSame(
            'priority-v2',
            $this->service(['methodology_version' => 'priority-v2'])->methodologyVersion()
        );
    }

    public function test_nilai_bawaan_sesuai_spesifikasi_modul(): void
    {
        $service = $this->service();

        $this->assertSame(['risk' => 0.6, 'impact' => 0.4], $service->weights());

        $levels = $service->levels();

        $this->assertSame(80, $levels['critical']['threshold']);
        $this->assertSame(60, $levels['high']['threshold']);
        $this->assertSame(40, $levels['medium']['threshold']);
        $this->assertSame(0, $levels['low']['threshold']);

        $this->assertSame(['province', 'regency', 'district'], $service->regionLevels());
    }

    public function test_sumber_komponen_menyebut_model_dan_dataset(): void
    {
        $service = $this->service();

        $this->assertSame('Fire Risk Model', $service->componentMeta('risk')['source']);

        $this->assertSame(
            'Population Dataset + GIS Analysis',
            $service->componentMeta('impact')['source']
        );
    }
}

