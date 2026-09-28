document.addEventListener(
    'DOMContentLoaded',
    function () {


        /*
        |--------------------------------------------------------------------------
        | ELEMENT HALAMAN
        |--------------------------------------------------------------------------
        */

        const provinceSelect =
            document.getElementById('province-select');

        const regencySelect =
            document.getElementById('regency-select');

        const districtSelect =
            document.getElementById('district-select');

        const refreshButton =
            document.querySelector('[data-ai-refresh]');

        const toast =
            document.getElementById('toast');


        const PLACEHOLDER = {

            province: 'Pilih Provinsi',

            regency: 'Pilih Kabupaten / Kota',

            district: 'Pilih Kecamatan'

        };




        /*
        |--------------------------------------------------------------------------
        | DATA WILAYAH
        |--------------------------------------------------------------------------
        |
        | window.sigmaRegions dikirim Laravel dan juga dipakai peta (gis-map.js),
        | sehingga dropdown dan peta selalu memakai sumber data yang sama.
        |
        */

        function regions() {

            return Array.isArray(window.sigmaRegions)
                ? window.sigmaRegions
                : [];

        }


        function findRegion(id) {

            if (id === null || id === undefined || id === '') {

                return null;

            }


            return regions().find(
                item => String(item.id) === String(id)
            ) || null;

        }




        /*
        |--------------------------------------------------------------------------
        | DROPDOWN WILAYAH
        |--------------------------------------------------------------------------
        */

        function fillSelect(target, list, placeholder) {

            if (!target) {

                return;

            }


            let html = `<option value="">${placeholder}</option>`;


            list.forEach(item => {

                html += `<option value="${item.id}">${item.name}</option>`;

            });


            target.innerHTML = html;

            /* Dropdown tanpa data tidak bisa dipilih */
            target.disabled = list.length === 0;

        }


        function resetSelect(target, placeholder) {

            fillSelect(target, [], placeholder);

        }


        function loadChildren(parentId, target, placeholder) {

            if (!target) {

                return Promise.resolve([]);

            }


            if (!parentId) {

                resetSelect(target, placeholder);

                return Promise.resolve([]);

            }


            /* Status loading */
            target.disabled = true;

            target.innerHTML = `<option value="">Memuat data...</option>`;


            const baseUrl =
                window.regionChildrenUrl || '/regions';


            return fetch(

                `${baseUrl}/${parentId}/children`,

                {
                    headers: {
                        'Accept': 'application/json'
                    }
                }

            )

                .then(response => {

                    if (!response.ok) {

                        throw new Error('HTTP ' + response.status);

                    }


                    return response.json();

                })

                .then(payload => {

                    const list =

                        Array.isArray(payload)

                        ?

                        payload

                        :

                        (
                            payload && Array.isArray(payload.data)

                            ?

                            payload.data

                            :

                            []
                        );


                    fillSelect(target, list, placeholder);


                    return list;

                })

                .catch(error => {

                    console.error(
                        'Gagal memuat data wilayah:',
                        error
                    );


                    target.innerHTML = `<option value="">Gagal memuat data</option>`;

                    target.disabled = true;


                    return [];

                });

        }


        /*
        |--------------------------------------------------------------------------
        | PETA -> DETAIL & DROPDOWN
        |--------------------------------------------------------------------------
        |
        | Semua perubahan wilayah melewati gis-map.js (window.sigmaFireRiskMap)
        | supaya panel detail dan peta selalu memakai data yang sama.
        |
        */

        function selectRegion(id, options) {

            const mapApi =
                window.sigmaFireRiskMap;


            if (mapApi && typeof mapApi.select === 'function') {

                mapApi.select(id, options);

            }

        }




        /*
        |--------------------------------------------------------------------------
        | SINKRONISASI KE DROPDOWN
        |--------------------------------------------------------------------------
        |
        | Dipanggil ketika wilayah dipilih dari peta (event sigma:region-selected).
        | Rantai provinsi -> kabupaten -> kecamatan disusun dari parent_id.
        |
        */

        function syncDropdowns(id) {

            const region =
                findRegion(id);


            if (!region) {

                return;

            }


            const chain = [];


            let current = region;


            while (current) {

                chain.unshift(current);

                current = current.parent_id
                    ? findRegion(current.parent_id)
                    : null;

            }


            const province =
                chain.find(item => item.level === 'province');

            const regency =
                chain.find(item => item.level === 'regency');

            const district =
                chain.find(item => item.level === 'district');


            if (provinceSelect) {

                provinceSelect.value = province ? province.id : '';

            }


            if (!province) {

                resetSelect(regencySelect, PLACEHOLDER.regency);

                resetSelect(districtSelect, PLACEHOLDER.district);

                return;

            }


            loadChildren(province.id, regencySelect, PLACEHOLDER.regency)

                .then(() => {

                    if (regencySelect && regency) {

                        regencySelect.value = regency.id;

                    }


                    if (!regency) {

                        resetSelect(districtSelect, PLACEHOLDER.district);

                        return null;

                    }


                    return loadChildren(
                        regency.id,
                        districtSelect,
                        PLACEHOLDER.district
                    )
                        .then(() => {

                            if (districtSelect && district) {

                                districtSelect.value = district.id;

                            }

                        });

                });

        }




        /*
        |--------------------------------------------------------------------------
        | EVENT DROPDOWN
        |--------------------------------------------------------------------------
        */

        if (provinceSelect) {

            provinceSelect.addEventListener('change', function () {


                /* Bersihkan kabupaten & kecamatan sebelumnya */
                resetSelect(regencySelect, PLACEHOLDER.regency);

                resetSelect(districtSelect, PLACEHOLDER.district);


                if (!this.value) {

                    return;

                }


                loadChildren(
                    this.value,
                    regencySelect,
                    PLACEHOLDER.regency
                );


                selectRegion(this.value, { focus: true });


            });

        }


        if (regencySelect) {

            regencySelect.addEventListener('change', function () {


                /* Bersihkan kecamatan sebelumnya */
                resetSelect(districtSelect, PLACEHOLDER.district);


                if (!this.value) {

                    return;

                }


                loadChildren(
                    this.value,
                    districtSelect,
                    PLACEHOLDER.district
                );


                selectRegion(this.value, { focus: true });


            });

        }


        if (districtSelect) {

            districtSelect.addEventListener('change', function () {

                if (!this.value) {

                    return;

                }


                selectRegion(this.value, { focus: true });

            });

        }




        /*
        |--------------------------------------------------------------------------
        | EVENT DARI PETA
        |--------------------------------------------------------------------------
        */

        document.addEventListener('sigma:region-selected', function (event) {

            const detail =
                event.detail || {};


            syncDropdowns(detail.id);

        });



        /*
        |--------------------------------------------------------------------------
        | PERBARUI ANALISIS (AI SERVICE)
        |--------------------------------------------------------------------------
        */

        function csrfToken() {

            const input =
                document.querySelector('input[name="_token"]');


            return input ? input.value : '';

        }




        function showToast(message) {

            if (!toast) {

                console.log(message);

                return;

            }


            toast.textContent = message;

            toast.classList.add('show');


            clearTimeout(showToast.timer);


            showToast.timer = setTimeout(

                () => toast.classList.remove('show'),

                4000

            );

        }




        function selectedRegionId() {

            const candidates = [
                districtSelect,
                regencySelect,
                provinceSelect
            ];


            for (let index = 0; index < candidates.length; index++) {

                const select = candidates[index];


                if (select && select.value) {

                    /* Jangan kirim wilayah yang masih loading */
                    if (select.disabled) {

                        continue;

                    }


                    return select.value;

                }

            }


            return '';

        }




        function refreshAnalysis() {

            const url =
                window.fireRiskRefreshUrl;


            if (!url) {

                return;

            }


            const regionId =
                selectedRegionId();


            const originalLabel =
                refreshButton ? refreshButton.textContent : '';


            if (refreshButton) {

                refreshButton.disabled = true;

                refreshButton.textContent = 'Memperbarui...';

            }


            showToast('Menghubungi AI Service...');


            fetch(url, {

                method: 'POST',

                headers: {

                    'Content-Type': 'application/json',

                    'Accept': 'application/json',

                    'X-CSRF-TOKEN': csrfToken()

                },

                body: JSON.stringify({

                    region_id: regionId ? Number(regionId) : null

                })

            })

                .then(response => {

                    if (!response.ok) {

                        throw new Error('HTTP ' + response.status);

                    }


                    return response.json();

                })

                .then(data => {

                    if (refreshButton) {

                        refreshButton.disabled = false;

                        refreshButton.textContent = originalLabel;

                    }


                    if (!data || !data.success) {

                        showToast(

                            data && data.message

                            ?

                            data.message

                            :

                            'AI Service tidak dapat dihubungi.'

                        );


                        return;

                    }


                    const mapApi =
                        window.sigmaFireRiskMap;


                    if (mapApi) {

                        (data.updated || []).forEach(row => {

                            mapApi.updateRegionRisk(
                                row.region_id,
                                row
                            );

                        });


                        if (regionId) {

                            mapApi.updateDetail(regionId);

                        }

                    }


                    showToast(data.message);

                })

                .catch(error => {

                    console.error(
                        'Gagal memperbarui analisis:',
                        error
                    );


                    if (refreshButton) {

                        refreshButton.disabled = false;

                        refreshButton.textContent = originalLabel;

                    }


                    showToast('AI Service tidak dapat dihubungi.');

                });

        }


        if (refreshButton) {

            refreshButton.addEventListener('click', refreshAnalysis);

        }




        /*
        |--------------------------------------------------------------------------
        | STATE AWAL
        |--------------------------------------------------------------------------
        |
        | Blade sudah menampilkan wilayah pertama (provinsi) pada panel detail,
        | jadi peta + dropdown disinkronkan ke wilayah yang sama tanpa
        | menggeser posisi peta.
        |
        */

        const firstProvince =
            regions()

                .filter(region => region.level === 'province')

                .sort((a, b) => String(a.name).localeCompare(String(b.name)))

                .shift();


        if (firstProvince) {

            selectRegion(firstProvince.id, { focus: false });

        }


    }
);

