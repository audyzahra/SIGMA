<?php

/*
|--------------------------------------------------------------------------
| Data Contoh (DEMO) — DINONAKTIFKAN
|--------------------------------------------------------------------------
|
| Berkas ini sebelumnya menyimpan angka contoh (mis. hotspot 124, risiko
| wilayah 90, impact 95) dan sempat dipakai sebagai cadangan (fallback)
| dashboard. Angka seperti itu membuat halaman tampak menampilkan hasil
| analisis nyata, jadi isinya dihapus.
|
| Halaman Government sekarang membaca data nyata dari database:
|  - statistik : tabel fire_statistics
|  - risiko    : fire_risks (AI Service: NASA POWER + NASA FIRMS + model)
|  - dampak    : dihitung ImpactAnalysisService (spatial + data nyata)
|  - prioritas : incident_priorities
|
| Struktur kunci dipertahankan (array kosong) agar pemanggil lama tidak
| error bila masih ada yang membaca config('sigma_data').
|
*/

return [
    'users' => [],
    'roles' => [],
    'organizations' => [],
    'regions' => [],
    'government_metrics' => [],
    'recent_activities' => [],
    'incidents' => [],
    'hotspots' => [],
    'recommendations' => [],
];

