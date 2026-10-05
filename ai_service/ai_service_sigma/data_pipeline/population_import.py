"""
Ingestion raster penduduk WorldPop (dataset/raw/population/idn_ppp_2020_UNadj.tif)
menjadi agregasi per wilayah administratif pada tabel `region_populations`.

Sumber: WorldPop UN-adjusted Population Counts 2020 (~100 m, EPSG:4326).

Metode (zonal statistics):
  Untuk setiap polygon wilayah, jumlahkan nilai piksel raster yang berada di
  dalam polygon (windowed read + geometry mask). Hasil pada level terpilih
  (default: kecamatan) dijumlahkan ulang ke kabupaten lalu provinsi melalui
  kolom parent_id, sehingga semua level siap dipakai Analisis Dampak.

Contoh pemakaian:
    python data_pipeline/population_import.py --dry-run --limit 20
    python data_pipeline/population_import.py --level district
"""

from __future__ import annotations

import argparse
import json
import sys
import time
from pathlib import Path

import numpy as np
import rasterio
import rasterio.features
import rasterio.windows
from pyproj import Geod
from shapely.geometry import mapping, shape

sys.path.insert(0, str(Path(__file__).resolve().parent))

from db import POPULATION_RASTER, connect  # noqa: E402


GEOD = Geod(ellps="WGS84")

SOURCE = "worldpop_ppp_2020_unadj"

SOURCE_FILE = "idn_ppp_2020_UNadj.tif"


def region_rows(connection, level: str, limit=None):
    """Ambil wilayah (id, parent_id, nama, GeoJSON, luas km2)."""

    sql = (
        "SELECT id, parent_id, name, "
        "ST_AsGeoJSON(geometry) AS geojson, "
        "ST_Area(geometry) / 1000000 AS area_km2 "
        "FROM regions "
        "WHERE level = %s AND geometry IS NOT NULL "
        "ORDER BY id"
    )

    params = [level]

    if limit:
        sql += " LIMIT %s"
        params.append(int(limit))

    with connection.cursor() as cursor:
        cursor.execute(sql, params)
        columns = [column[0] for column in cursor.description]
        return [dict(zip(columns, row)) for row in cursor.fetchall()]


def zonal_sum(source, geometry, nodata):
    """Jumlahkan nilai raster di dalam polygon. Kembalikan (total, sel_valid)."""

    minx, miny, maxx, maxy = geometry.bounds

    full = rasterio.windows.Window(0, 0, source.width, source.height)

    window = rasterio.windows.from_bounds(
        minx, miny, maxx, maxy, transform=source.transform
    )

    window = window.round_offsets().round_lengths().intersection(full)

    if window.width < 1 or window.height < 1:
        return 0.0, 0

    data = source.read(1, window=window)

    if data.size == 0:
        return 0.0, 0

    transform = source.window_transform(window)

    mask = rasterio.features.geometry_mask(
        [mapping(geometry)],
        out_shape=data.shape,
        transform=transform,
        invert=True,
        all_touched=False,
    )

    values = data[mask]

    if values.size == 0:
        return 0.0, 0

    if nodata is not None:
        values = values[values != nodata]

    values = values[np.isfinite(values)]

    if values.size == 0:
        return 0.0, 0

    return float(values.sum()), int(values.size)


def hierarchy(connection):
    """Peta id -> (parent_id, level, area_km2) untuk seluruh wilayah."""

    with connection.cursor() as cursor:

        cursor.execute(
            "SELECT id, parent_id, level, ST_Area(geometry) / 1000000 FROM regions"
        )

        info = {}

        for region_id, parent_id, level, area in cursor.fetchall():

            info[int(region_id)] = (
                int(parent_id) if parent_id is not None else None,
                level,
                float(area) if area is not None else None,
            )

        return info


def upsert_rows(connection, rows):
    """Simpan/menimpa agregasi penduduk (idempotent)."""

    sql = """
    INSERT INTO region_populations
        (region_id, population, area_km2, population_density, grid_cells,
         source, source_file, imported_at, created_at, updated_at)
    VALUES (%s, %s, %s, %s, %s, %s, %s, NOW(), NOW(), NOW())
    AS new
    ON DUPLICATE KEY UPDATE
        population = new.population,
        area_km2 = new.area_km2,
        population_density = new.population_density,
        grid_cells = new.grid_cells,
        source = new.source,
        source_file = new.source_file,
        imported_at = NOW(),
        updated_at = NOW()
    """

    with connection.cursor() as cursor:
        cursor.executemany(sql, rows)

    connection.commit()


def run(args) -> int:

    connection = connect()

    try:

        rows = region_rows(connection, args.level, args.limit)

        if not rows:
            print(f"Tidak ada wilayah level '{args.level}' yang punya geometry.")
            return 1

        print(f"Level analisis : {args.level}")
        print(f"Jumlah wilayah : {len(rows)}")
        print(f"Raster         : {POPULATION_RASTER.name}")

        info = hierarchy(connection)

        computed = {}   # region_id -> {population, cells, area_km2}

        started = time.time()

        with rasterio.open(POPULATION_RASTER) as source:

            print(f"Ukuran raster  : {source.width} x {source.height}")

            nodata = source.nodata

            for index, row in enumerate(rows, start=1):

                geometry = shape(json.loads(row["geojson"]))

                total, cells = zonal_sum(source, geometry, nodata)

                area = row["area_km2"]

                if not area:
                    area = abs(GEOD.geometry_area_perimeter(geometry)[0]) / 1_000_000

                computed[int(row["id"])] = {
                    "population": int(round(total)),
                    "cells": cells,
                    "area_km2": round(float(area), 2) if area else None,
                }

                if index % 200 == 0 or index == len(rows):
                    print(
                        f"   {index}/{len(rows)} wilayah dihitung "
                        f"({time.time() - started:,.0f}s)",
                        flush=True,
                    )

        # --- Agregasi ke wilayah induk (kecamatan -> kabupaten -> provinsi) ---

        agg_pop = {}
        agg_cells = {}

        for region_id, data in computed.items():

            current = region_id

            while current is not None:

                agg_pop[current] = agg_pop.get(current, 0) + data["population"]
                agg_cells[current] = agg_cells.get(current, 0) + data["cells"]

                current = info.get(current, (None, None, None))[0]

        stored = []

        for region_id, population in agg_pop.items():

            area = info.get(region_id, (None, None, None))[2]

            if area is None and region_id in computed:
                area = computed[region_id]["area_km2"]

            density = round(population / area, 3) if area else None

            stored.append(
                (
                    region_id,
                    population,
                    round(area, 2) if area else None,
                    density,
                    agg_cells.get(region_id),
                    SOURCE,
                    SOURCE_FILE,
                )
            )

        stored.sort(key=lambda item: -item[1])

        print(f"\nBaris agregasi siap disimpan: {len(stored)} wilayah (semua level)")
        print("Teratas:")
        for item in stored[:5]:
            print(f"   region_id={item[0]} penduduk={item[1]:,} kepadatan={item[3]}")

        if args.dry_run:
            print("DRY-RUN: tidak menyimpan ke database.")
            return 0

        upsert_rows(connection, stored)

        print("Selesai menyimpan region_populations.")

    except Exception as error:  # noqa: BLE001

        connection.rollback()
        print(f"Gagal: {error}")
        return 1

    finally:

        connection.close()

    return 0


def main() -> int:

    parser = argparse.ArgumentParser(
        description="Agregasi raster penduduk WorldPop ke tabel `region_populations` SIGMA."
    )

    parser.add_argument(
        "--level",
        default="district",
        choices=["province", "regency", "district"],
        help="Level wilayah yang dihitung langsung dari raster (default: district).",
    )
    parser.add_argument(
        "--limit",
        type=int,
        default=None,
        help="Batasi jumlah wilayah (untuk uji coba).",
    )
    parser.add_argument("--dry-run", action="store_true", help="Hitung saja, tanpa simpan.")

    return run(parser.parse_args())


if __name__ == "__main__":
    raise SystemExit(main())
