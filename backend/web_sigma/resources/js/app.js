const regionSelect = document.querySelector('[data-region-select]');

if (regionSelect && window.sigmaRegions) {
    const updateRegionDetail = () => {
        const region = window.sigmaRegions.find((item) => item.id === regionSelect.value);

        if (!region) return;

        document.querySelector('[data-risk-score]').textContent = Math.round(region.risk * 0.967);
        document.querySelector('[data-risk-level]').textContent = region.priority === 'Kritis' ? 'Ekstrem' : region.priority;
        document.querySelector('[data-risk-temperature]').textContent = region.temperature;
        document.querySelector('[data-risk-humidity]').textContent = region.humidity;
        document.querySelector('[data-risk-wind]').textContent = region.wind_speed;
        document.querySelector('[data-risk-rainfall]').textContent = region.rainfall;
    };

    regionSelect.addEventListener('change', updateRegionDetail);
}

/*
|--------------------------------------------------------------------------
| Penyaringan tabel prioritas
|--------------------------------------------------------------------------
|
| Penyaringan dan pencarian tabel Prioritas Penanganan kini dikerjakan
| server (PriorityCalculationService) lewat query database, sehingga baris
| tabel tidak lagi disaring di sisi klien. Script halaman tersebut ada di
| public/js/government/priority.js.
|
*/

