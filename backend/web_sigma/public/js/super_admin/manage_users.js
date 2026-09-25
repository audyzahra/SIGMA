document.addEventListener("DOMContentLoaded", function () {


    const form = document.getElementById('filterForm');

    const search = document.getElementById('searchInput');

    const role = document.getElementById('roleFilter');

    const perPage = document.getElementById('perPage');


    if (!form) {
        return;
    }



    let timer = null;



    if (search) {

        search.addEventListener(
            'input',
            function () {

                clearTimeout(timer);


                timer = setTimeout(function () {

                    form.submit();

                }, 500);


            }
        );

    }



    if (role) {

        role.addEventListener(
            'change',
            function () {

                form.submit();

            }
        );

    }



    if (perPage) {

        perPage.addEventListener(
            'change',
            function () {

                form.submit();

            }
        );

    }



});