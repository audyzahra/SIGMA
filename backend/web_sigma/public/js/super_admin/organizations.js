document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('organization-filter');

    if (!form) return;


    const search = document.getElementById('search-organization');
    const type = document.getElementById('type-filter');
    const status = document.getElementById('status-filter');


    let timer;


    function autoSubmit() {

        clearTimeout(timer);

        timer = setTimeout(() => {

            form.submit();

        }, 500);

    }


    search.addEventListener(
        'input',
        autoSubmit
    );


    type.addEventListener(
        'change',
        autoSubmit
    );


    status.addEventListener(
        'change',
        autoSubmit
    );


});