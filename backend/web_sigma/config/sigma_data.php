<?php

return [
    'users' => [
        ['id' => 'USR-001', 'name' => 'Ahmad Fauzi, S.Hut', 'role' => 'government', 'organization' => 'BPBD Provinsi Jawa Barat'],
    ],
    'roles' => ['super_admin', 'government', 'field_team', 'citizen'],
    'organizations' => [
        ['id' => 'ORG-001', 'name' => 'BPBD Provinsi Jawa Barat'],
        ['id' => 'ORG-002', 'name' => 'Ditjen Gakkum & PKHL KLHK'],
    ],
    'regions' => [
        ['id' => 'REG-001', 'name' => 'Kalimantan Tengah', 'risk' => 90, 'impact' => 95, 'priority' => 'Kritis', 'temperature' => '32°C', 'humidity' => '40%', 'wind_speed' => '20 km/jam', 'rainfall' => '10 mm'],
        ['id' => 'REG-002', 'name' => 'Riau', 'risk' => 75, 'impact' => 80, 'priority' => 'Tinggi', 'temperature' => '31°C', 'humidity' => '46%', 'wind_speed' => '16 km/jam', 'rainfall' => '14 mm'],
        ['id' => 'REG-003', 'name' => 'Kalimantan Barat', 'risk' => 70, 'impact' => 65, 'priority' => 'Tinggi', 'temperature' => '30°C', 'humidity' => '52%', 'wind_speed' => '13 km/jam', 'rainfall' => '18 mm'],
        ['id' => 'REG-004', 'name' => 'Sumatera Selatan', 'risk' => 55, 'impact' => 60, 'priority' => 'Sedang', 'temperature' => '29°C', 'humidity' => '58%', 'wind_speed' => '10 km/jam', 'rainfall' => '24 mm'],
        ['id' => 'REG-005', 'name' => 'Jambi', 'risk' => 40, 'impact' => 35, 'priority' => 'Rendah', 'temperature' => '28°C', 'humidity' => '68%', 'wind_speed' => '8 km/jam', 'rainfall' => '31 mm'],
    ],
    'government_metrics' => [
        ['label' => 'Hotspot Aktif', 'value' => '124', 'icon' => '♨', 'accent' => 'red'],
        ['label' => 'Risiko Tinggi', 'value' => '18', 'icon' => '△', 'accent' => 'orange'],
        ['label' => 'Laporan Masuk', 'value' => '8', 'icon' => '▤', 'accent' => 'red'],
        ['label' => 'Tim Bertugas', 'value' => '42', 'icon' => '♙', 'accent' => 'green'],
    ],
    'recent_activities' => [
        ['time' => '10:24', 'label' => 'Hotspot terdeteksi', 'type' => 'danger'],
        ['time' => '09:56', 'label' => 'Laporan diperbarui', 'type' => 'orange-text'],
        ['time' => '09:30', 'label' => 'Analisis risiko selesai', 'type' => 'success'],
        ['time' => '08:15', 'label' => 'Tim diberangkatkan', 'type' => 'orange-text'],
    ],
    'incidents' => [
        ['id' => 'INC-001', 'region_id' => 'REG-001', 'type' => 'Hotspot', 'status' => 'Aktif'],
    ],
    'hotspots' => [
        ['id' => 'HSP-001', 'region_id' => 'REG-001', 'latitude' => -1.6815, 'longitude' => 113.3824],
    ],
    'recommendations' => [
        ['region_id' => 'REG-001', 'action' => 'Kirim Tim Pemadam', 'detail' => '2 tim'],
        ['region_id' => 'REG-001', 'action' => 'Patroli Udara', 'detail' => 'Drone / Helikopter'],
        ['region_id' => 'REG-001', 'action' => 'Peringatan Masyarakat', 'detail' => 'Wilayah sekitar'],
        ['region_id' => 'REG-001', 'action' => 'Cek Sumber Air', 'detail' => 'Radius 10 km'],
    ],
];
