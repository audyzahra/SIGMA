"""
Ingestion raster tutupan lahan ESA WorldCover 2021
(dataset/raw/land/ESA_WorldCover_10m_2021_v200_*.tif) menjadi agregasi per
wilayah administratif pada tabel `region_land_covers`.

Sumber: ESA WorldCover 2021 v200, resolusi ~10 m, EPSG:4326.

Kelas yang dihitung (standar ESA WorldCover):
  - 10 : Tree cover          -> Hutan
  - 90 : Herbaceous wetland  -> Lahan basah / gambut

Metode (zonal statistics) mengikuti population_import.py: baca window
berdasarkan bounds polygon + geometry mask, hitung jumlah piksel per kelas,
lalu kalikan luas piksel (fungsi lintang) menjadi hektar.

Catatan: tile yang tersedia hanya mencakup bujur 120-123, lintang -6 s.d. 3
(Sulawesi/Maluku). Wilayah di luar cakupan tidak dihitung.

Contoh pemakaian:
    python data_pipeline/landcover_import.py --dry-run --limit 20
    python data_pipeline/landcover_import.py --level district
"""

from __future__ import annotations

import argparse
import glob
import json
import math
import sys
import time
from pathlib import Path

import numpy as np
import rasterio
import rasterio.features
import rasterio.windows
from shapely.geometry import box, mapping, shape

sys.path.insert(0, str(Path(__file__).resolve().parent))

from db import BASE_DIR, connect  # noqa: E402


FOREST_CLASS = 10

WETLAND_CLASS = 90

# Perairan permanen (laut/danau) dikeluarkan dari penyebut "luas daratan"
# agar cakupan hutan tidak terlihat kecil hanya karena wilayah pesisir.
WATER_CLASS = 80

LAND_DIR = BASE_DIR / "dataset" / "raw" / "land"

SOURCE = "esa_worldcover_2021"

M_PER_DEG_LAT = 110574.0

M_PER_DEG_LON_EQUATOR = 111320.0


def raster_files():
    """Daftar file raster ESA WorldCover yang tersedia."""

    return sorted(glob.glob(str(LAND_DIR / "*.tif")))


def region_rows(connection, level: str, limit=None):
    """Ambil wilayah (id, parent_id, nama, GeoJSON)."""

    sql = (
        "SELECT id, parent_id, name, ST_AsGeoJSON(geometry) AS geojson "
        "FROM regions WHERE level = %s AND geometry IS NOT NULL ORDER BY id"
    )

    params = [level]

    if limit:
        sql += " LIMIT %s"
        params.append(int(limit))

    with connection.cursor() as cursor:
        cursor.execute(sql, params)
        columns = [column[0] for column in cursor.description]
        return [dict(zip(columns, row)) for row in cursor.fetchall()]


def pixel_area_ha(res_x: float, res_y: float, latitude: float) -> float:
    """Luas satu piksel (hektar) pada lintang tertentu."""

    metres_lon = res_x * M_PER_DEG_LON_EQUATOR * math.cos(math.radians(latitude))
    metres_lat = res_y * M_PER_DEG_LAT

    return (metres_lon * metres_lat) / 10000.0


def zonal_classes(source, geometry, nodata):
    """Hitung jumlah piksel hutan/basah/valid di dalam polygon.

    Kembalikan dict {forest, wetland, valid, pixel_ha} atau None.
    """

    minx, miny, maxx, maxy = geometry.bounds

    full = rasterio.windows.Window(0, 0, source.width, source.height)

    window = rasterio.windows.from_bounds(
        minx, miny, maxx, maxy, transform=source.transform
    )

    window = window.round_offsets().round_lengths().intersection(full)

    if window.width < 1 or window.height < 1:
        return None

    data = source.read(1, window=window)

    if data.size == 0:
        return None

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
        return None

    if nodata is not None:
        values = values[values != nodata]

    if values.size == 0:
        return None

    centre = rasterio.transform.xy(transform, data.shape[0] // 2, data.shape[1] // 2)

    # Luas daratan = piksel terpetakan tanpa perairan permanen
    land = values[values != WATER_CLASS]

    return {
        "forest": int(np.count_nonzero(values == FOREST_CLASS)),
        "wetland": int(np.count_nonzero(values == WETLAND_CLASS)),
        "valid": int(land.size),
        "pixel_ha": pixel_area_ha(source.res[0], source.res[1], centre[1]),
    }


def hierarchy(connection):
    """Peta id -> parent_id untuk seluruh wilayah."""

    with connection.cursor() as cursor:

        cursor.execute("SELECT id, parent_id FROM regions")

        return {
            int(region_id): (int(parent_id) if parent_id is not None else None)
            for region_id, parent_id in cursor.fetchall()
        }


def upsert_rows(connection, rows):
    """Simpan/menimpa agregasi tutupan lahan (idempotent)."""

    sql = """
    INSERT INTO region_land_covers
        (region_id, valid_ha, forest_ha, wetland_ha, forest_percent,
         wetland_percent, source, source_file, imported_at,
         created_at, updated_at)
    VALUES (%s, %s, %s, %s, %s, %s, %s, %s, NOW(), NOW(), NOW())
    AS new
    ON DUPLICATE KEY UPDATE
        valid_ha = new.valid_ha,
        forest_ha = new.forest_ha,
        wetland_ha = new.wetland_ha,
        forest_percent = new.forest_percent,
        wetland_percent = new.wetland_percent,
        source = new.source,
        source_file = new.source_file,
        imported_at = NOW(),
        updated_at = NOW()
    """

    with connection.cursor() as cursor:
        cursor.executemany(sql, rows)

    connection.commit()


def run(args) -> int:

    files = raster_files()

    if not files:
        print("Tidak ada file ESA WorldCover di dataset/raw/land.")
        return 1

    connection = connect()

    try:

        rows = region_rows(connection, args.level, args.limit)

        if not rows:
            print(f"Tidak ada wilayah level '{args.level}' yang punya geometry.")
            return 1

        print(f"Level analisis : {args.level}")
        print(f"Jumlah wilayah : {len(rows)}")
        print(f"Jumlah tile    : {len(files)}")

        info = hierarchy(connection)

        computed = {}
        started = time.time()

        for path in files:

            name = Path(path).stem
            tile_key = name.split("_")[-2]

            with rasterio.open(path) as source:

                bbox = box(*source.bounds)
                nodata = source.nodata
                processed = 0

                for row in rows:

                    geometry = shape(json.loads(row["geojson"]))

                    if not bbox.intersects(geometry):
                        continue

                    result = zonal_classes(source, geometry, nodata)

                    if not result:
                        continue

                    region_id = int(row["id"])

                    agg = computed.setdefault(region_id, {
                        "forest_ha": 0.0,
                        "wetland_ha": 0.0,
                        "valid_ha": 0.0,
                        "tiles": set(),
                    })

                    agg["forest_ha"] += result["forest"] * result["pixel_ha"]
                    agg["wetland_ha"] += result["wetland"] * result["pixel_ha"]
                    agg["valid_ha"] += result["valid"] * result["pixel_ha"]
                    agg["tiles"].add(tile_key)

                    processed += 1

                print(
                    f"  {name}: {processed} wilayah ({time.time() - started:,.0f}s)",
                    flush=True,
                )

        if not computed:
            print("Tidak ada wilayah yang tercakup oleh tile ESA WorldCover.")
            return 1

        # Agregasi ke wilayah induk (kecamatan -> kabupaten -> provinsi)
        agg_forest = {}
        agg_wetland = {}
        agg_valid = {}
        agg_tiles = {}

        for region_id, data in computed.items():

            current = region_id

            while current is not None:

                agg_forest[current] = agg_forest.get(current, 0.0) + data["forest_ha"]
                agg_wetland[current] = agg_wetland.get(current, 0.0) + data["wetland_ha"]
                agg_valid[current] = agg_valid.get(current, 0.0) + data["valid_ha"]
                agg_tiles.setdefault(current, set()).update(data["tiles"])

                current = info.get(current)

        stored = []

        for region_id in agg_forest:

            valid = agg_valid.get(region_id, 0.0)
            forest = agg_forest[region_id]
            wetland = agg_wetland[region_id]

            stored.append((
                region_id,
                round(valid, 2),
                round(forest, 2),
                round(wetland, 2),
                round(forest / valid * 100, 3) if valid > 0 else None,
                round(wetland / valid * 100, 3) if valid > 0 else None,
                SOURCE,
                ",".join(sorted(agg_tiles.get(region_id, set()))),
            ))

        stored.sort(key=lambda item: -item[2])

        print(f"\nBaris agregasi siap disimpan: {len(stored)} wilayah (semua level)")
        print("Teratas (luas hutan):")
        for item in stored[:5]:
            print(f"   region_id={item[0]} hutan_ha={item[2]:,.0f} gambut_ha={item[3]:,.0f}")

        if args.dry_run:
            print("DRY-RUN: tidak menyimpan ke database.")
            return 0

        upsert_rows(connection, stored)

        print("Selesai menyimpan region_land_covers.")

    except Exception as error:  # noqa: BLE001

        connection.rollback()
        print(f"Gagal: {error}")
        return 1

    finally:

        connection.close()

    return 0


def main() -> int:

    parser = argparse.ArgumentParser(
        description="Agregasi ESA WorldCover ke tabel `region_land_covers` SIGMA."
    )

    parser.add_argument(
        "--level",
        default="district",
        choices=["province", "regency", "district"],
        help="Level wilayah yang dihitung langsung dari raster (default: district).",
    )
    parser.add_argument("--limit", type=int, default=None, help="Batasi jumlah wilayah (uji coba).")
    parser.add_argument("--dry-run", action="store_true", help="Hitung saja, tanpa simpan.")

    return run(parser.parse_args())


if __name__ == "__main__":
    raise SystemExit(main())
