document.addEventListener("DOMContentLoaded", function () {


    const form = document.querySelector("#region-filter");

    const search = document.querySelector("#search-region");

    const level = document.querySelector("#level-filter");


    if(!form) return;



    let timer;



    search.addEventListener(
        "input",
        function(){

            clearTimeout(timer);


            timer=setTimeout(
                function(){

                    form.submit();

                },
                500
            );


        }
    );



    level.addEventListener(
        "change",
        function(){

            form.submit();

        }
    );


});