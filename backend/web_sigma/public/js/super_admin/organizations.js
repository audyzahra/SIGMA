document.addEventListener("DOMContentLoaded", function () {

    const form = document.querySelector("#organization-filter");

    const search = document.querySelector("#search-organization");

    const type = document.querySelector("#type-filter");

    const status = document.querySelector("#status-filter");


    if (!form) return;


    let timer;


    function submitFilter(){

        form.submit();

    }



    search.addEventListener(
        "input",
        function(){

            clearTimeout(timer);


            timer = setTimeout(
                submitFilter,
                500
            );

        }
    );



    type.addEventListener(
        "change",
        submitFilter
    );



    status.addEventListener(
        "change",
        submitFilter
    );


});