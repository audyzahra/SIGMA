/*
|--------------------------------------------------------------------------
| Prioritas Penanganan — Government
|--------------------------------------------------------------------------
|
| Hanya menangani interaksi tampilan:
|   1. filter kabupaten/kota yang mengikuti provinsi terpilih
|      (memakai endpoint /regions/{id}/children yang sudah ada)
|   2. konfirmasi sebelum menjalankan "Hitung Ulang Prioritas"
|
| TIDAK ada perhitungan prioritas di sisi klien. Seluruh skor, kategori,
| ranking, filter, dan pagination dihitung server (PriorityCalculationService)
| dan dikirim sebagai HTML yang sudah jadi.
|
*/

document.addEventListener("DOMContentLoaded", () => {

    /*
    |--------------------------------------------------------------------------
    | FILTER KABUPATEN / KOTA
    |--------------------------------------------------------------------------
    */

    const provinceSelect = document.getElementById("priority-province");

    const regencySelect = document.getElementById("priority-regency");

    const baseUrl =
        provinceSelect?.dataset.regionChildrenUrl
        || window.regionChildrenUrl
        || "/regions";

    const placeholderRegency = "Semua Kabupaten / Kota";

    const placeholderProvinceFirst = "Pilih provinsi terlebih dahulu";

    /* Kosongkan pilihan kabupaten ketika provinsi belum/ tidak dipilih */
    function resetRegency() {

        if (!regencySelect) {
            return;
        }

        regencySelect.innerHTML = "";

        regencySelect.append(new Option(placeholderProvinceFirst, ""));

        regencySelect.disabled = true;
    }

    /* Isi pilihan kabupaten dari wilayah anak provinsi (data dari database) */
    function loadRegencies(provinceId) {

        if (!regencySelect) {
            return;
        }

        if (!provinceId) {
            resetRegency();
            return;
        }

        fetch(`${baseUrl}/${provinceId}/children`, {
            headers: {
                Accept: "application/json"
            }
        })
            .then(response => {

                if (!response.ok) {
                    throw new Error("HTTP " + response.status);
                }

                return response.json();

            })
            .then(payload => {

                regencySelect.innerHTML = "";

                regencySelect.append(new Option(placeholderRegency, ""));

                (payload.data || []).forEach(region => {
                    regencySelect.append(new Option(region.name, region.id));
                });

                regencySelect.disabled = false;

            })
            .catch(() => {
                resetRegency();
            });
    }

    if (provinceSelect && regencySelect) {

        provinceSelect.addEventListener("change", () => {
            loadRegencies(provinceSelect.value);
        });

    }

    /*
    |--------------------------------------------------------------------------
    | KONFIRMASI HITUNG ULANG
    |--------------------------------------------------------------------------
    |
    | Perhitungan ulang menyentuh seluruh wilayah, jadi dikonfirmasi lebih
    | dahulu supaya tidak terpicu tanpa sengaja.
    |
    */

    document.querySelectorAll("[data-priority-recalculate]").forEach(form => {

        form.addEventListener("submit", event => {

            const confirmed = window.confirm(
                "Hitung ulang prioritas seluruh wilayah sekarang?"
            );

            if (!confirmed) {
                event.preventDefault();
            }

        });

    });

});
