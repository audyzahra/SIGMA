/*
|--------------------------------------------------------------------------
| Analisis Dampak — Government
|--------------------------------------------------------------------------
|
| Menghubungkan filter (wilayah / hotspot / insiden / radius) ke endpoint
| JSON Analisis Dampak, lalu menggambar hasilnya ke peta Leaflet dan panel
| dampak.
|
| Semua angka berasal dari respons server (ImpactAnalysisService). Tidak ada
| perhitungan dampak yang dikarang di sisi klien.
|
*/

document.addEventListener("DOMContentLoaded", () => {


    /*
    |--------------------------------------------------------------------------
    | GUARD
    |--------------------------------------------------------------------------
    |
    | Hentikan eksekusi bila container peta atau Leaflet tidak tersedia.
    |
    */

    const mapElement =
        document.getElementById("sigma-impact-map");


    if (!mapElement || typeof L === "undefined") {

        return;

    }



    /*
    |--------------------------------------------------------------------------
    | KONFIGURASI DARI SERVER
    |--------------------------------------------------------------------------
    */

    const endpoints =
        window.sigmaImpactEndpoints || {};

    const metricDecimals =
        window.sigmaImpactMetricDecimals || {};

    const levelLabels =
        window.sigmaImpactLevelLabels || {};

    const adminLabels =
        window.sigmaImpactAdminLevelLabels || {};



    /*
    |--------------------------------------------------------------------------
    | ELEMEN
    |--------------------------------------------------------------------------
    */

    const provinceSelect =
        document.getElementById("impact-province");

    const regencySelect =
        document.getElementById("impact-regency");

    const districtSelect =
        document.getElementById("impact-district");

    const radiusSelect =
        document.getElementById("impact-radius");

    const pointSelect =
        document.getElementById("impact-point");

    const runButton =
        document.querySelector("[data-impact-run]");

    const saveButton =
        document.querySelector("[data-impact-save]");

    const statusElement =
        document.getElementById("impact-status");



    /*
    |--------------------------------------------------------------------------
    | PETA
    |--------------------------------------------------------------------------
    */

    const map =
        L.map(
            mapElement,
            {
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
            maxZoom: 18,
            attribution: "&copy; OpenStreetMap"
        }

    )
        .addTo(map);


    const zoneLayer =
        L.layerGroup().addTo(map);

    const regionLayer =
        L.layerGroup().addTo(map);

    const hotspotLayer =
        L.layerGroup().addTo(map);

    const incidentLayer =
        L.layerGroup().addTo(map);

    const reportLayer =
        L.layerGroup().addTo(map);



    /*
    |--------------------------------------------------------------------------
    | STATE
    |--------------------------------------------------------------------------
    */

    let analysis = null;

    let loading = false;



    /*
    |--------------------------------------------------------------------------
    | UTILITAS
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value) {

        return String(value === null || value === undefined ? "" : value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;");

    }


    function decimalsFor(key) {

        return Number(metricDecimals[key] || 0);

    }


    function formatNumber(value, digits) {

        const number = Number(value);

        if (!isFinite(number)) {

            return "-";

        }

        return number.toLocaleString(
            "id-ID",
            {
                minimumFractionDigits: digits,
                maximumFractionDigits: digits
            }
        );

    }


    /* Format nilai metrik mengikuti status ketersediaannya. */
    function formatMetric(metric) {

        if (!metric || !metric.available || metric.value === null) {

            return "Data tidak tersedia";

        }

        return formatNumber(
            metric.value,
            decimalsFor(metric.key)
        )
            + (metric.unit ? " " + metric.unit : "");

    }


    function setText(id, value) {

        const element =
            document.getElementById(id);

        if (element) {

            element.textContent = value;

        }

    }


    function notify(message) {

        const toast =
            document.getElementById("toast");

        if (!toast) {

            return;

        }

        toast.textContent = message;

        toast.classList.add("show");

        setTimeout(() => {

            toast.classList.remove("show");

        }, 2600);

    }



    /*
    |--------------------------------------------------------------------------
    | DROPDOWN WILAYAH (provinsi -> kabupaten -> kecamatan)
    |--------------------------------------------------------------------------
    */

    const PLACEHOLDER = {
        regency: "Pilih Kabupaten / Kota",
        district: "Pilih Kecamatan"
    };


    function fillSelect(target, list, placeholder) {

        if (!target) {

            return;

        }

        let html =
            `<option value="">${placeholder}</option>`;

        list.forEach(item => {

            html += `<option value="${item.id}">${escapeHtml(item.name)}</option>`;

        });

        target.innerHTML = html;

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

        target.disabled = true;

        target.innerHTML = `<option value="">Memuat data...</option>`;

        const baseUrl =
            window.regionChildrenUrl || "/regions";

        return fetch(
            `${baseUrl}/${parentId}/children`,
            {
                headers: {
                    Accept: "application/json"
                }
            }
        )
            .then(response => {

                if (!response.ok) {

                    throw new Error("HTTP " + response.status);

                }

                return response.json();

            })
            .then(payload => {

                const list = Array.isArray(payload)
                    ? payload
                    : (payload && Array.isArray(payload.data) ? payload.data : []);

                fillSelect(target, list, placeholder);

                return list;

            })
            .catch(error => {

                console.error("Gagal memuat data wilayah:", error);

                target.innerHTML = `<option value="">Gagal memuat data</option>`;

                target.disabled = true;

                return [];

            });

    }


    function clearPointSelect() {

        if (pointSelect) {

            pointSelect.value = "";

        }

    }


    function clearRegionSelects() {

        if (provinceSelect) {

            provinceSelect.value = "";

        }

        resetSelect(regencySelect, PLACEHOLDER.regency);

        resetSelect(districtSelect, PLACEHOLDER.district);

    }


    /* Wilayah terdalam yang dipilih: kecamatan > kabupaten > provinsi. */
    function selectedRegionId() {

        if (districtSelect && districtSelect.value) {

            return districtSelect.value;

        }

        if (regencySelect && regencySelect.value) {

            return regencySelect.value;

        }

        if (provinceSelect && provinceSelect.value) {

            return provinceSelect.value;

        }

        return "";

    }


    function currentSelection() {

        const point =
            pointSelect && pointSelect.value
                ? pointSelect.value.split(":")
                : null;

        return {
            region_id: selectedRegionId(),
            hotspot_id: point && point[0] === "hotspot" ? point[1] : "",
            incident_id: point && point[0] === "incident" ? point[1] : "",
            radius_km: radiusSelect ? radiusSelect.value : ""
        };

    }



    /*
    |--------------------------------------------------------------------------
    | REQUEST ANALISIS
    |--------------------------------------------------------------------------
    */

    function runAnalysis(options) {

        const settings = options || {};

        const selection = currentSelection();


        if (!selection.region_id && !selection.hotspot_id && !selection.incident_id) {

            if (statusElement) {

                statusElement.textContent =
                    "Pilih wilayah atau hotspot / insiden terlebih dahulu.";

            }

            return Promise.resolve(null);

        }


        if (loading) {

            return Promise.resolve(null);

        }


        const params = new URLSearchParams();

        if (selection.region_id) {

            params.set("region_id", selection.region_id);

        }

        if (selection.hotspot_id) {

            params.set("hotspot_id", selection.hotspot_id);

        }

        if (selection.incident_id) {

            params.set("incident_id", selection.incident_id);

        }

        if (selection.radius_km) {

            params.set("radius_km", selection.radius_km);

        }

        if (settings.save) {

            params.set("save", "1");

        }


        loading = true;

        if (statusElement) {

            statusElement.textContent = "Menghitung dampak...";

        }


        return fetch(
            `${endpoints.analysis}?${params.toString()}`,
            {
                headers: {
                    Accept: "application/json"
                }
            }
        )
            .then(response => {

                if (!response.ok) {

                    throw new Error("HTTP " + response.status);

                }

                return response.json();

            })
            .then(payload => {

                applyAnalysis(payload);

                if (settings.save) {

                    if (payload && payload.saved) {

                        notify(`Hasil analisis disimpan (#${payload.saved.id}).`);

                    } else {

                        notify("Hasil analisis tidak dapat disimpan.");

                    }

                }

                return payload;

            })
            .catch(error => {

                console.error("Analisis dampak gagal:", error);

                if (statusElement) {

                    statusElement.textContent =
                        "Analisis gagal dijalankan. Periksa koneksi atau log server.";

                }

                notify("Analisis dampak gagal dijalankan.");

                return null;

            })
            .finally(() => {

                loading = false;

            });

    }



    /*
    |--------------------------------------------------------------------------
    | TERAPKAN HASIL KE UI
    |--------------------------------------------------------------------------
    */

    function applyAnalysis(payload) {

        if (!payload || payload.success !== true) {

            if (statusElement) {

                statusElement.textContent =
                    (payload && payload.message)
                        ? payload.message
                        : "Analisis tidak menghasilkan data.";

            }

            return;

        }


        analysis = payload;

        updateHeader(payload);
        updateMetrics(payload.metrics || []);
        updateComponents(payload.impact_score || null);
        updateRegions(payload.affected_regions || null);
        updateObjects(payload);
        renderMap(payload);
        pushUrl();

    }


    function updateHeader(payload) {

        const center =
            payload.center || {};

        const region =
            payload.region || {};


        setText(
            "impact-center",
            `${formatNumber(center.latitude, 5)}, ${formatNumber(center.longitude, 5)}`
        );

        setText(
            "impact-region",
            region.name || "-"
        );

        setText(
            "impact-radius-label",
            `${formatNumber(center.radius_km, 2)} km`
        );


        const sourceLabel = center.source === "region_centroid"
            ? "titik pusat wilayah"
            : `titik ${center.source}`;


        if (statusElement) {

            statusElement.textContent =
                `Analisis ${payload.calculated_at} · ${sourceLabel}`
                + ` · metodologi ${payload.methodology_version}`;

        }


        const score =
            payload.impact_score || {};

        setText("impact-score", score.score === undefined ? "-" : score.score);

        setText("impact-level", levelLabels[score.level] || "-");


        if (score.weights_used) {

            setText(
                "impact-completeness",
                `${formatNumber(score.weights_used.data_completeness_percent, 2)}%`
            );

        }

    }


    function updateMetrics(metrics) {

        metrics.forEach(metric => {

            const row =
                document.querySelector(`[data-metric="${metric.key}"]`);

            if (!row) {

                return;

            }

            row.classList.toggle("is-unavailable", !metric.available);

            row.dataset.available = metric.available ? "1" : "0";


            const value =
                row.querySelector("[data-metric-value]");

            if (value) {

                value.textContent = formatMetric(metric);

            }


            const note =
                row.querySelector("[data-metric-note]");

            if (note) {

                note.textContent = metric.reason || metric.unit || "";

            }

        });

    }


    function updateComponents(score) {

        if (!score || !score.components) {

            return;

        }

        Object.keys(score.components).forEach(key => {

            const component =
                score.components[key];

            const block =
                document.querySelector(`[data-component="${key}"]`);

            if (!block) {

                return;

            }

            block.classList.toggle("is-unavailable", !component.available);


            const value =
                block.querySelector("[data-component-value]");

            if (value) {

                value.textContent = component.available
                    ? `${formatNumber(component.normalized, 2)}%`
                    : "Data tidak tersedia";

            }


            const bar =
                block.querySelector(".component-bar i");

            if (bar) {

                const width = component.available
                    ? Math.min(Math.max(Number(component.normalized) || 0, 0), 100)
                    : 0;

                bar.style.width = `${width}%`;

            }


            const note =
                block.querySelector("[data-component-note]");

            if (note) {

                note.textContent = component.note;
            }

        });

    }



    /*
    |--------------------------------------------------------------------------
    | TABEL WILAYAH TERDAMPAK
    |--------------------------------------------------------------------------
    */

    const LEVEL_ORDER = ["district", "regency", "province"];


    function updateRegions(affected) {

        const body =
            document.getElementById("impact-region-rows");

        const summary =
            document.querySelector("[data-affected-summary]");

        if (!body) {

            return;

        }


        const rows = [];

        LEVEL_ORDER.forEach(level => {

            const entries =
                (affected && affected.by_level && affected.by_level[level])
                    ? affected.by_level[level]
                    : [];

            entries.forEach(entry => {

                rows.push(`
                    <tr class="impact-region-row" data-region-id="${entry.id}">
                        <td>${escapeHtml(entry.name)}</td>
                        <td>${escapeHtml(adminLabels[level] || level)}</td>
                        <td>${formatNumber(entry.region_km2, 2)} km²</td>
                        <td>${formatNumber(entry.intersect_km2, 4)} km²</td>
                        <td>${formatNumber(entry.intersect_percent_of_region, 2)}%</td>
                    </tr>
                `);

            });

        });


        if (rows.length === 0) {

            body.innerHTML = `
                <tr>
                    <td colspan="5">
                        ${affected && affected.available === false && affected.reason
                            ? escapeHtml(affected.reason)
                            : "Tidak ada wilayah administratif yang beririsan dengan zona analisis."}
                    </td>
                </tr>
            `;

        } else {

            body.innerHTML = rows.join("");

        }


        if (summary) {

            summary.textContent = (affected && rows.length > 0)
                ? `${affected.total} wilayah · ${formatNumber(affected.area_km2, 2)} km²`
                : "Belum ada wilayah terdampak";

        }


        /* klik baris tabel -> analisis wilayah tersebut */
        body.querySelectorAll(".impact-region-row").forEach(row => {

            row.addEventListener("click", () => {

                focusRegion(row.dataset.regionId);

            });

        });

    }


    /*
    |--------------------------------------------------------------------------
    | TABEL OBJEK DI DALAM RADIUS
    |--------------------------------------------------------------------------
    */

    function renderRows(bodyId, rows, emptyMessage) {

        const body =
            document.getElementById(bodyId);

        if (!body) {

            return;

        }

        body.innerHTML = rows.length > 0
            ? rows.join("")
            : `<tr><td colspan="6">${escapeHtml(emptyMessage)}</td></tr>`;

    }


    function updateObjects(payload) {

        const hotspots =
            payload.hotspots || {};

        const incidents =
            payload.incidents || {};

        const reports =
            payload.reports || {};


        renderRows(
            "impact-hotspot-rows",
            (hotspots.items || []).map(item => `
                <tr>
                    <td>${escapeHtml(item.satellite_name || "Hotspot")}</td>
                    <td>${item.frp !== null ? formatNumber(item.frp, 2) + " MW" : "-"}</td>
                    <td>${item.brightness_temperature !== null
                        ? formatNumber(item.brightness_temperature, 2) + " K"
                        : "-"}</td>
                    <td>${formatNumber(item.distance_km, 2)} km</td>
                    <td>${escapeHtml(item.status)}</td>
                </tr>
            `),
            "Tidak ada hotspot di dalam radius ini."
        );


        renderRows(
            "impact-incident-rows",
            (incidents.items || []).map(item => `
                <tr>
                    <td>${escapeHtml(item.location_description || "Insiden #" + item.id)}</td>
                    <td>${escapeHtml(item.fire_status)}</td>
                    <td>${escapeHtml(item.severity_level)}</td>
                    <td>${formatNumber(item.distance_km, 2)} km</td>
                </tr>
            `),
            "Tidak ada insiden di dalam radius ini."
        );


        renderRows(
            "impact-report-rows",
            (reports.items || []).map(item => `
                <tr>
                    <td>${escapeHtml(item.description || "Laporan #" + item.id)}</td>
                    <td>${escapeHtml(item.verification_status)}</td>
                    <td>${formatNumber(item.distance_km, 2)} km</td>
                </tr>
            `),
            "Tidak ada laporan masyarakat di dalam radius ini."
        );


        const hotspotSummary =
            document.querySelector("[data-hotspot-summary]");

        if (hotspotSummary) {

            hotspotSummary.textContent =
                `${hotspots.count || 0} titik · ${hotspots.active_count || 0} aktif`;

        }


        const incidentSummary =
            document.querySelector("[data-incident-summary]");

        if (incidentSummary) {

            incidentSummary.textContent =
                `${incidents.count || 0} insiden · ${incidents.ongoing_count || 0} berjalan`;

        }

    }



    /*
    |--------------------------------------------------------------------------
    | PETA: ZONA, WILAYAH TERDAMPAK, HOTSPOT, INSIDEN, LAPORAN
    |--------------------------------------------------------------------------
    */

    const AFFECTED_COLORS = [
        { min: 90, color: "#b91c1c" },
        { min: 50, color: "#ea580c" },
        { min: 10, color: "#f59e0b" },
        { min: 0, color: "#fcd34d" }
    ];


    function affectedColor(percent) {

        const value =
            Number(percent) || 0;

        const found =
            AFFECTED_COLORS.find(item => value >= item.min);

        return found ? found.color : "#fcd34d";

    }


    /* Peta id wilayah -> entri hasil analisis (beserta levelnya). */
    function regionLookup(affected) {

        const lookup = {};

        LEVEL_ORDER.forEach(level => {

            const entries =
                (affected && affected.by_level && affected.by_level[level])
                    ? affected.by_level[level]
                    : [];

            entries.forEach(entry => {

                lookup[entry.id] = Object.assign(
                    {
                        level: level
                    },
                    entry
                );

            });

        });

        return lookup;

    }


    function renderMap(payload) {

        zoneLayer.clearLayers();
        regionLayer.clearLayers();
        hotspotLayer.clearLayers();
        incidentLayer.clearLayers();
        reportLayer.clearLayers();


        const center =
            payload.center || {};

        const radiusKm =
            Number(center.radius_km) || 0;


        if (center.latitude === undefined || center.longitude === undefined) {

            return;

        }


        /* --- zona radius analisis --- */

        const circle =
            L.circle(
                [center.latitude, center.longitude],
                {
                    radius: radiusKm * 1000,
                    color: "#b45309",
                    weight: 2,
                    dashArray: "6 6",
                    fillColor: "#f59e0b",
                    fillOpacity: 0.12
                }
            );

        circle.bindPopup(
            `<b>Zona analisis</b><br>`
            + `Radius ${formatNumber(radiusKm, 2)} km<br>`
            + `Luas zona ${formatNumber(center.zone_area_km2, 2)} km²`
        );

        circle.addTo(zoneLayer);


        /* --- titik analisis --- */

        L.circleMarker(
            [center.latitude, center.longitude],
            {
                radius: 7,
                color: "#7f1d1d",
                weight: 2,
                fillColor: "#d62828",
                fillOpacity: 1
            }
        )
            .bindPopup(
                `<b>Titik analisis</b><br>`
                + `${formatNumber(center.latitude, 5)}, ${formatNumber(center.longitude, 5)}<br>`
                + `Sumber: ${escapeHtml(center.source)}`
            )
            .addTo(zoneLayer);


        /* --- wilayah terdampak (geometri hasil klip server) --- */

        const affected =
            payload.affected_regions || null;


        LEVEL_ORDER.forEach(level => {

            const entries =
                (affected && affected.by_level && affected.by_level[level])
                    ? affected.by_level[level]
                    : [];

            entries.forEach(entry => {

                if (!entry.geometry) {

                    return;

                }

                const color =
                    affectedColor(entry.intersect_percent_of_region);


                const layer =
                    L.geoJSON(
                        entry.geometry,
                        {
                            style: {
                                color: color,
                                weight: 1,
                                fillColor: color,
                                fillOpacity: 0.35
                            }
                        }
                    );


                layer.bindPopup(
                    `<b>${escapeHtml(entry.name)}</b><br>`
                    + `${escapeHtml(adminLabels[level] || level)}<br>`
                    + `Luas terdampak ${formatNumber(entry.intersect_km2, 4)} km²`
                    + ` (${formatNumber(entry.intersect_percent_of_region, 2)}% wilayah)<br>`
                    + `Luas wilayah ${formatNumber(entry.region_km2, 2)} km²`
                );


                layer.on("click", () => {

                    focusRegion(entry.id);

                });


                layer.addTo(regionLayer);

            });

        });


        /* --- hotspot NASA FIRMS --- */

        ((payload.hotspots && payload.hotspots.items) || []).forEach(item => {

            L.circleMarker(
                [item.latitude, item.longitude],
                {
                    radius: 5,
                    color: "#7f1d1d",
                    weight: 1.5,
                    fillColor: "#dc2626",
                    fillOpacity: 0.9
                }
            )
                .bindPopup(
                    `<b>Hotspot ${escapeHtml(item.satellite_name || "")}</b><br>`
                    + `FRP ${item.frp !== null ? formatNumber(item.frp, 2) + " MW" : "-"}<br>`
                    + `Suhu kecerahan ${item.brightness_temperature !== null
                        ? formatNumber(item.brightness_temperature, 2) + " K"
                        : "-"}<br>`
                    + `Jarak ${formatNumber(item.distance_km, 2)} km<br>`
                    + `Status ${escapeHtml(item.status)}<br>`
                    + `${escapeHtml(item.detected_at || "")}`
                )
                .addTo(hotspotLayer);

        });


        /* --- insiden --- */

        ((payload.incidents && payload.incidents.items) || []).forEach(item => {

            L.circleMarker(
                [item.latitude, item.longitude],
                {
                    radius: 6,
                    color: "#4c1d95",
                    weight: 2,
                    fillColor: "#7c3aed",
                    fillOpacity: 0.9
                }
            )
                .bindPopup(
                    `<b>${escapeHtml(item.location_description || "Insiden #" + item.id)}</b><br>`
                    + `Status api ${escapeHtml(item.fire_status)}<br>`
                    + `Severity ${escapeHtml(item.severity_level)}<br>`
                    + `Jarak ${formatNumber(item.distance_km, 2)} km<br>`
                    + `${escapeHtml(item.detected_at || "")}`
                )
                .addTo(incidentLayer);

        });


        /* --- laporan masyarakat --- */

        ((payload.reports && payload.reports.items) || []).forEach(item => {

            L.circleMarker(
                [item.latitude, item.longitude],
                {
                    radius: 5,
                    color: "#1e3a8a",
                    weight: 1.5,
                    fillColor: "#2563eb",
                    fillOpacity: 0.9
                }
            )
                .bindPopup(
                    `<b>Laporan #${item.id}</b><br>`
                    + `${escapeHtml(item.report_type)}<br>`
                    + `Verifikasi ${escapeHtml(item.verification_status)}<br>`
                    + `Jarak ${formatNumber(item.distance_km, 2)} km`
                )
                .addTo(reportLayer);

        });


        map.fitBounds(
            circle.getBounds(),
            {
                padding: [24, 24],
                maxZoom: 13
            }
        );

    }



    /*
    |--------------------------------------------------------------------------
    | SINKRONISASI PETA -> DROPDOWN
    |--------------------------------------------------------------------------
    |
    | Klik wilayah pada peta (atau baris tabel) menyusun ulang rantai
    | provinsi -> kabupaten -> kecamatan, lalu analisis dijalankan untuk
    | wilayah tersebut.
    |
    */

    function focusRegion(regionId) {

        if (!regionId) {

            return;

        }

        clearPointSelect();

        syncDropdowns(regionId)
            .then(() => runAnalysis());

    }


    function syncDropdowns(regionId) {

        const lookup =
            regionLookup(analysis && analysis.affected_regions);


        const entry =
            lookup[regionId] || null;


        let district = null;
        let regency = null;
        let province = null;


        if (entry) {

            if (entry.level === "district") {

                district = entry;
                regency = lookup[entry.parent_id] || null;

            } else if (entry.level === "regency") {

                regency = entry;

            } else if (entry.level === "province") {

                province = entry;

            }

        }


        if (!province && regency) {

            province = lookup[regency.parent_id] || null;

        }


        /* Wilayah induk tidak ikut terhitung: pilih langsung tanpa dropdown. */
        if (!province) {

            if (provinceSelect) {

                provinceSelect.value = "";

            }

            resetSelect(regencySelect, PLACEHOLDER.regency);

            resetSelect(districtSelect, PLACEHOLDER.district);

            return Promise.resolve();

        }


        if (provinceSelect) {

            provinceSelect.value = province.id;

        }


        return loadChildren(province.id, regencySelect, PLACEHOLDER.regency)
            .then(() => {

                if (!regency) {

                    resetSelect(districtSelect, PLACEHOLDER.district);

                    return null;

                }

                regencySelect.value = regency.id;

                return loadChildren(regency.id, districtSelect, PLACEHOLDER.district);

            })
            .then(() => {

                if (district) {

                    districtSelect.value = district.id;

                }

            });

    }



    /*
    |--------------------------------------------------------------------------
    | URL
    |--------------------------------------------------------------------------
    |
    | Analisis yang sedang tampil disimpan pada query string supaya bisa
    | di-bookmark / dibagikan (server menghitung ulang saat halaman dibuka).
    |
    */

    function pushUrl() {

        if (!window.history || typeof window.history.replaceState !== "function") {

            return;

        }

        const selection =
            currentSelection();

        const params =
            new URLSearchParams();

        if (selection.region_id) {

            params.set("region_id", selection.region_id);

        }

        if (selection.hotspot_id) {

            params.set("hotspot_id", selection.hotspot_id);

        }

        if (selection.incident_id) {

            params.set("incident_id", selection.incident_id);

        }

        if (selection.radius_km) {

            params.set("radius", selection.radius_km);

        }

        const query =
            params.toString();

        window.history.replaceState(
            {},
            "",
            query
                ? `${window.location.pathname}?${query}`
                : window.location.pathname
        );

    }



    /*
    |--------------------------------------------------------------------------
    | EVENT
    |--------------------------------------------------------------------------
    */

    if (provinceSelect) {

        provinceSelect.addEventListener("change", () => {

            clearPointSelect();

            resetSelect(districtSelect, PLACEHOLDER.district);

            if (!provinceSelect.value) {

                resetSelect(regencySelect, PLACEHOLDER.regency);

                return;

            }

            loadChildren(provinceSelect.value, regencySelect, PLACEHOLDER.regency);

            runAnalysis();

        });

    }


    if (regencySelect) {

        regencySelect.addEventListener("change", () => {

            clearPointSelect();

            if (!regencySelect.value) {

                resetSelect(districtSelect, PLACEHOLDER.district);

                if (provinceSelect && provinceSelect.value) {

                    runAnalysis();

                }

                return;

            }

            loadChildren(regencySelect.value, districtSelect, PLACEHOLDER.district);

            runAnalysis();

        });

    }


    if (districtSelect) {

        districtSelect.addEventListener("change", () => {

            clearPointSelect();

            if (districtSelect.value || (regencySelect && regencySelect.value)) {

                runAnalysis();

            }

        });

    }


    if (radiusSelect) {

        radiusSelect.addEventListener("change", () => {

            if (selectedRegionId() || (pointSelect && pointSelect.value)) {

                runAnalysis();

                return;

            }

            notify("Pilih wilayah atau titik analisis terlebih dahulu.");

        });

    }


    if (pointSelect) {

        pointSelect.addEventListener("change", () => {

            if (!pointSelect.value) {

                return;

            }

            clearRegionSelects();

            runAnalysis();

        });

    }


    if (runButton) {

        runButton.addEventListener("click", () => {

            runAnalysis();

        });

    }


    if (saveButton) {

        saveButton.addEventListener("click", () => {

            runAnalysis({ save: true });

        });

    }



    /*
    |--------------------------------------------------------------------------
    | STATE AWAL
    |--------------------------------------------------------------------------
    |
    | Analisis yang sudah dihitung server (dari query string) langsung
    | digambar, tanpa request tambahan.
    |
    */

    const initialAnalysis =
        window.sigmaImpactAnalysis;

    const initialSelection =
        window.sigmaImpactSelected || {};


    if (initialAnalysis && initialAnalysis.success === true) {

        applyAnalysis(initialAnalysis);

        if (initialSelection.region_id) {

            syncDropdowns(initialSelection.region_id);

        }

        if (initialSelection.hotspot_id && pointSelect) {

            pointSelect.value = `hotspot:${initialSelection.hotspot_id}`;

        }

        if (initialSelection.incident_id && pointSelect) {

            pointSelect.value = `incident:${initialSelection.incident_id}`;

        }

    } else if (initialSelection.region_id
        || initialSelection.hotspot_id
        || initialSelection.incident_id) {

        applyAnalysis(initialAnalysis);

    }


    /* Peta berada di dalam grid; pastikan ukurannya benar setelah layout. */
    setTimeout(() => {

        map.invalidateSize();

    }, 200);

});







