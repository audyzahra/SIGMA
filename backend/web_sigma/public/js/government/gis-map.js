document.addEventListener(
    "DOMContentLoaded",
    () => {


        const mapElement =
            document.getElementById(
                "sigma-map"
            );


        /*
        |--------------------------------------------------------------------------
        | GUARD
        |--------------------------------------------------------------------------
        |
        | Hentikan eksekusi jika container peta tidak tersedia.
        |
        */

        if (!mapElement || typeof L === "undefined") {

            return;

        }




        /*
        |--------------------------------------------------------------------------
        | SUMBER DATA TUNGGAL
        |--------------------------------------------------------------------------
        |
        | window.sigmaRegions dikirim Laravel dan dipakai bersama oleh peta dan
        | dropdown wilayah (fire-risk.js), sehingga keduanya memakai state
        | yang sama.
        |
        */

        const regions =
            Array.isArray(window.sigmaRegions)
            ?
            window.sigmaRegions
            :
            [];


        const layers = {};

        let activeId = null;




        /*
        |--------------------------------------------------------------------------
        | PETA
        |--------------------------------------------------------------------------
        */

        const map =
            L.map(
                mapElement,
                {

                    /* Ribuan polygon lebih ringan dirender sebagai canvas */
                    preferCanvas: true

                }
            )
                .setView(
                    [-2.5, 118],
                    5
                );


        L.tileLayer(

            "https://tile.openstreetmap.org/{z}/{x}/{y}.png",

            {
                maxZoom: 18
            }

        )
            .addTo(map);




        /*
        |--------------------------------------------------------------------------
        | LAYER GEOJSON
        |--------------------------------------------------------------------------
        */

        regions.forEach(region => {


            const geometry =
                parseGeometry(
                    region.geometry
                );


            if (!geometry) {

                return;

            }


            L.geoJSON(

                geometry,

                {

                    style: baseStyle(region),

                    onEachFeature(feature, polygon) {

                        layers[region.id] = polygon;

                        polygon.regionData = region;

                        polygon.bindPopup(
                            popupContent(region)
                        );

                        polygon.on(

                            "click",

                            () => selectRegion(
                                region.id,
                                { focus: false }
                            )

                        );

                    }

                }

            )
                .addTo(map);


        });


        /*
        |--------------------------------------------------------------------------
        | SELECT REGION
        |--------------------------------------------------------------------------
        |
        | Dipakai oleh klik polygon maupun dropdown wilayah (fire-risk.js).
        | Setelah detail diperbarui, event sigma:region-selected dikirim agar
        | dropdown wilayah ikut sinkron.
        |
        */

        function selectRegion(id, options) {


            const region =
                findRegion(id);


            if (!region) {

                return false;

            }


            const settings =
                options || {};


            renderDetail(region);


            highlight(
                region.id,
                settings.focus !== false
            );


            document.dispatchEvent(

                new CustomEvent(

                    "sigma:region-selected",

                    {
                        detail: {
                            id: region.id
                        }
                    }

                )

            );


            return true;

        }




        /*
        |--------------------------------------------------------------------------
        | HIGHLIGHT WILAYAH AKTIF
        |--------------------------------------------------------------------------
        */

        function highlight(id, focus) {


            if (activeId !== null && layers[activeId]) {

                layers[activeId].setStyle(

                    baseStyle(
                        findRegion(activeId) || {}
                    )

                );

            }


            activeId = id;


            const layer =
                layers[id];


            if (!layer) {

                return;

            }


            layer.setStyle({

                color: "#dc2626",

                weight: 3,

                fillOpacity: .65

            });


            if (focus && typeof layer.getBounds === "function") {

                map.fitBounds(
                    layer.getBounds()
                );

            }

        }




        /*
        |--------------------------------------------------------------------------
        | DETAIL WILAYAH
        |--------------------------------------------------------------------------
        */

        function renderDetail(region) {


            setValue(
                "risk-score",
                region.risk_score ?? 0
            );


            const level =
                String(
                    region.risk_level ?? "-"
                )
                .toUpperCase();


            const levelElement =
                document.getElementById(
                    "risk-level"
                );


            if (levelElement) {

                levelElement.textContent = level;


                levelElement.classList.remove(
                    "success",
                    "warning",
                    "orange-text",
                    "danger"
                );


                if (level === "LOW") {

                    levelElement.classList.add("success");

                } else if (level === "MEDIUM") {

                    levelElement.classList.add("warning");

                } else if (level === "HIGH") {

                    levelElement.classList.add("orange-text");

                } else if (level === "EXTREME") {

                    levelElement.classList.add("danger");

                }

            }


            setValue(
                "risk-temperature",
                formatNumber(region.temperature, "°C")
            );


            setValue(
                "risk-humidity",
                formatNumber(region.humidity, "%")
            );


            setValue(
                "risk-wind",
                formatNumber(region.wind_speed, " km/jam")
            );


            setValue(
                "risk-rainfall",
                formatNumber(region.rainfall, " mm")
            );

        }




        function setValue(id, value) {


            const element =
                document.getElementById(id);


            if (element) {

                element.textContent = value;

            }

        }




        function formatNumber(value, suffix) {


            if (value === null || value === undefined || value === "") {

                return "-";

            }


            const number =
                Number(value);


            if (Number.isNaN(number)) {

                return "-";

            }


            return number.toFixed(2) + suffix;

        }



        /*
        |--------------------------------------------------------------------------
        | UPDATE DATA RISIKO (hasil AI Service)
        |--------------------------------------------------------------------------
        */

        function updateRegionRisk(id, data) {


            const region =
                findRegion(id);


            if (!region || !data) {

                return;

            }


            [
                "risk_score",
                "risk_level",
                "temperature",
                "humidity",
                "wind_speed",
                "rainfall"
            ]
                .forEach(key => {

                    if (data[key] !== undefined && data[key] !== null) {

                        region[key] = data[key];

                    }

                });


            const layer =
                layers[id];


            if (layer) {

                layer.regionData = region;

                layer.setStyle(
                    baseStyle(region)
                );


                if (typeof layer.setPopupContent === "function") {

                    layer.setPopupContent(
                        popupContent(region)
                    );

                }

            }


            if (activeId !== null && String(activeId) === String(id)) {

                renderDetail(region);

            }

        }




        /*
        |--------------------------------------------------------------------------
        | HELPER
        |--------------------------------------------------------------------------
        */

        function findRegion(id) {


            if (id === null || id === undefined || id === "") {

                return null;

            }


            return regions.find(
                item => String(item.id) === String(id)
            ) || null;

        }




        function baseStyle(region) {


            const color =
                getColor(
                    region.risk_level
                );


            return {

                color: color,

                weight: 2,

                fillColor: color,

                fillOpacity: .45

            };

        }




        function popupContent(region) {


            return `

<b>${region.name}</b>

<br>

Risiko :
${region.risk_level ?? "-"}

<br>

Score :
${region.risk_score ?? 0}

`;

        }




        /*
        |--------------------------------------------------------------------------
        | PARSE GEOJSON STRING
        |--------------------------------------------------------------------------
        |
        | Geometry dikirim controller sebagai GeoJSON string (hemat memory),
        | sehingga perlu di-parse lebih dulu.
        |
        */

        function parseGeometry(geometry) {


            if (!geometry) {

                return null;

            }


            if (typeof geometry === "string") {

                try {

                    return JSON.parse(geometry);

                } catch (error) {

                    console.error(

                        "GeoJSON tidak valid",

                        error

                    );

                    return null;

                }

            }


            return geometry;

        }




        function getColor(level) {


            switch (
                String(level ?? "").toUpperCase()
            ) {

                case "LOW":

                    return "#22c55e";

                case "MEDIUM":

                    return "#eab308";

                case "HIGH":

                    return "#f97316";

                case "EXTREME":

                    return "#dc2626";

                default:

                    return "#22c55e";

            }

        }




        /*
        |--------------------------------------------------------------------------
        | API UNTUK fire-risk.js
        |--------------------------------------------------------------------------
        */

        window.sigmaFireRiskMap = {

            map: map,

            regions: regions,

            find: findRegion,

            select: selectRegion,

            updateDetail: id => {

                const region =
                    findRegion(id);


                if (region) {

                    renderDetail(region);

                }

            },

            updateRegionRisk: updateRegionRisk

        };




        setTimeout(

            () => {

                map.invalidateSize();

            },

            700

        );


    }
);

