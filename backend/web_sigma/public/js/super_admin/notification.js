document.addEventListener("DOMContentLoaded", function () {


    const Toast = Swal.mixin({

        toast: true,

        position: "top-end",

        showConfirmButton: false,

        timer: 3000,

        timerProgressBar: true,

        customClass: {

            popup: "sigma-toast"

        }

    });



    if (window.successMessage) {

        Toast.fire({

            icon: "success",

            title: window.successMessage

        });

    }



    if (window.errorMessage) {

        Toast.fire({

            icon: "error",

            title: window.errorMessage

        });

    }


});