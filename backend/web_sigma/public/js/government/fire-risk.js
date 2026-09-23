document.addEventListener('DOMContentLoaded', function () {
    const select = document.querySelector('[data-region-select]');

    if (!select) {
        return;
    }

    const scoreElement = document.querySelector('[data-risk-score]');
    const levelElement = document.querySelector('[data-risk-level]');
    const temperatureElement = document.querySelector('[data-risk-temperature]');
    const humidityElement = document.querySelector('[data-risk-humidity]');
    const windElement = document.querySelector('[data-risk-wind]');
    const rainfallElement = document.querySelector('[data-risk-rainfall]');

    function updateRisk(regionId) {
        if (!window.sigmaRegions || !Array.isArray(window.sigmaRegions)) {
            return;
        }

        const region = window.sigmaRegions.find(function (item) {
            return String(item.id) === String(regionId);
        });

        if (!region) {
            return;
        }

        // =========================
        // RISK SCORE
        // =========================
        if (scoreElement) {
            scoreElement.textContent = region.risk_score ?? 0;
        }

        // =========================
        // RISK LEVEL
        // =========================
        if (levelElement) {
            const level = region.risk_level ?? '-';

            levelElement.textContent = level !== '-'
                ? level.charAt(0).toUpperCase() + level.slice(1)
                : '-';

            levelElement.classList.remove(
                'success',
                'warning',
                'orange-text',
                'danger'
            );

            switch (level) {
                case 'low':
                    levelElement.classList.add('success');
                    break;

                case 'medium':
                    levelElement.classList.add('warning');
                    break;

                case 'high':
                    levelElement.classList.add('orange-text');
                    break;

                case 'extreme':
                    levelElement.classList.add('danger');
                    break;

                default:
                    levelElement.classList.add('danger');
                    break;
            }
        }

        // =========================
        // SUHU
        // =========================
        if (temperatureElement) {
            temperatureElement.textContent =
                region.temperature !== null &&
                region.temperature !== undefined
                    ? Number(region.temperature).toFixed(2) + '°C'
                    : '-';
        }

        // =========================
        // KELEMBAPAN
        // =========================
        if (humidityElement) {
            humidityElement.textContent =
                region.humidity !== null &&
                region.humidity !== undefined
                    ? Number(region.humidity).toFixed(2) + '%'
                    : '-';
        }

        // =========================
        // KECEPATAN ANGIN
        // =========================
        if (windElement) {
            windElement.textContent =
                region.wind_speed !== null &&
                region.wind_speed !== undefined
                    ? Number(region.wind_speed).toFixed(2) + ' km/jam'
                    : '-';
        }

        // =========================
        // CURAH HUJAN
        // =========================
        if (rainfallElement) {
            rainfallElement.textContent =
                region.rainfall !== null &&
                region.rainfall !== undefined
                    ? Number(region.rainfall).toFixed(2) + ' mm'
                    : '-';
        }
    }

    // =========================
    // EVENT DROPDOWN
    // =========================
    select.addEventListener('change', function () {
        updateRisk(this.value);
    });

    // =========================
    // DATA AWAL
    // =========================
    updateRisk(select.value);
});