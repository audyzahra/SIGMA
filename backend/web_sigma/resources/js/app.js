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

const prioritySearch = document.querySelector('[data-priority-search]');
const priorityFilter = document.querySelector('[data-priority-filter]');
const priorityRows = document.querySelectorAll('[data-priority-rows] tr');

const filterPriorityRows = () => {
    const query = prioritySearch?.value.toLowerCase() ?? '';
    const priority = priorityFilter?.value ?? '';

    priorityRows.forEach((row) => {
        row.hidden = !(row.dataset.region.includes(query) && (!priority || row.dataset.priority === priority));
    });
};

prioritySearch?.addEventListener('input', filterPriorityRows);
priorityFilter?.addEventListener('change', filterPriorityRows);
document.querySelector('[data-priority-reset]')?.addEventListener('click', () => {
    prioritySearch.value = '';
    priorityFilter.value = '';
    filterPriorityRows();
});

document.querySelectorAll('[data-detail]').forEach((button) => {
    button.addEventListener('click', () => {
        document.querySelector('[data-priority-detail]').textContent = button.dataset.detail;
    });
});
