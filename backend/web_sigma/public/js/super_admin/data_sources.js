document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.querySelector("#search-data-source");
    const typeFilter = document.querySelector("#type-filter");
    const statusFilter = document.querySelector("#status-filter");


    if (!searchInput) {
        return;
    }


    let timeout = null;


    function submitSearch() {


        const url = new URL(window.location.href);


        url.searchParams.set(
            "search",
            searchInput.value
        );


        url.searchParams.set(
            "type",
            typeFilter.value
        );


        url.searchParams.set(
            "status",
            statusFilter.value
        );


        window.location.href = url.toString();

    }



    searchInput.addEventListener(
        "input",
        function(){

            clearTimeout(timeout);


            timeout = setTimeout(
                submitSearch,
                500
            );

        }
    );



    typeFilter.addEventListener(
        "change",
        submitSearch
    );


    statusFilter.addEventListener(
        "change",
        submitSearch
    );


});