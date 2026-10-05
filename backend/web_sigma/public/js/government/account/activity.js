document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('activityFilterForm');

    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');

    /*
    |--------------------------------------------------------------------------
    | Validate Date
    |--------------------------------------------------------------------------
    */

    if (form && dateFrom && dateTo) {

        form.addEventListener('submit', (event) => {

            if (
                dateFrom.value &&
                dateTo.value &&
                dateFrom.value > dateTo.value
            ) {
                event.preventDefault();

                alert(
                    'Tanggal awal tidak boleh lebih besar dari tanggal akhir.'
                );

                dateFrom.focus();
            }

        });

    }


    /*
    |--------------------------------------------------------------------------
    | Initialize Lucide
    |--------------------------------------------------------------------------
    */

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

});