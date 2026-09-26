document.addEventListener("DOMContentLoaded", function () {

    const deleteForms = document.querySelectorAll(".delete-form");


    deleteForms.forEach(form => {


        form.addEventListener("submit", function (e) {

            e.preventDefault();


            Swal.fire({

                title: "Hapus Data?",

                text: "Data yang dihapus tidak dapat dikembalikan.",

                icon: "warning",

                showCancelButton: true,

                confirmButtonColor: "#C82828",

                cancelButtonColor: "#6B7280",

                confirmButtonText: "Ya, Hapus",

                cancelButtonText: "Batal",

                reverseButtons: true


            }).then((result) => {


                if (result.isConfirmed) {

                    form.submit();

                }


            });


        });


    });


});