"""
Ingestion dataset fasilitas publik (OpenStreetMap via HOTOSM raw-data-api)
ke tabel `facilities` pada database SIGMA.

Fitur utama:
  - STREAMING: file .geojson bisa > 2 GB sehingga dibaca fitur demi fitur
    memakai ijson (bukan json.load yang butuh puluhan GB RAM).
  - FILTER: hanya fitur yang benar-benar fasilitas (sekolah, kesehatan,
    pemerintahan, ibadah, transportasi, dsb.) yang disimpan. Sebagian besar
    fitur OSM (jalan, bangunan, air terjun, dll.) dibuang.
  - BATCH INSERT: tulis per batch dengan ON DUPLICATE KEY UPDATE sehingga
    skrip aman dijalankan ulang (idempotent) untuk melanjutkan impor.

Contoh pemakaian:
    python data_pipeline/facilities_import.py --dry-run --max-features 2000
    python data_pipeline/facilities_import.py --folder Bali
    python data_pipeline/facilities_import.py --batch 2000
"""

from __future__ import annotations

import argparse
import json
import numbers
import sys
import time
from decimal import Decimal
from pathlib import Path

import ijson

# agar `from db import ...` bekerja walau dijalankan dari folder lain
sys.path.insert(0, str(Path(__file__).resolve().parent))

from db import FACILITIES_DIR, connect  # noqa: E402


# ======================================================================
# KLASIFIKASI FASILITAS (tag OSM -> kategori SIGMA)
# ======================================================================

EDUCATION_AMENITY = {
    "school", "college", "university", "kindergarten", "library",
    "driving_school", "language_school", "music_school", "prep_school",
    "research_institute", "childcare",
}

HEALTH_AMENITY = {
    "hospital", "clinic", "doctors", "dentist", "pharmacy", "health_post",
    "nursing_home", "veterinary", "baby_hatch", "blood_donation",
}

EMERGENCY_AMENITY = {"fire_station", "police", "ambulance_station"}

GOVERNMENT_AMENITY = {"townhall", "courthouse", "prison", "post_office", "embassy"}

TRANSPORT_AMENITY = {"bus_station", "taxi", "ferry_terminal", "parking"}

SPORT_LEISURE = {"sports_centre", "stadium", "pitch", "swimming_pool", "track"}

COMMUNITY_AMENITY = {"community_centre", "social_facility"}


def classify(properties: dict):
    """Kembalikan (category, facility_type) atau None bila bukan fasilitas."""

    amenity = properties.get("amenity")
    healthcare = properties.get("healthcare")
    health_facility_type = properties.get("health_facility_type")
    isced_level = properties.get("isced_level")
    office = properties.get("office")
    leisure = properties.get("leisure")
    railway = properties.get("railway")
    public_transport = properties.get("public_transport")

    if amenity in EDUCATION_AMENITY or isced_level is not None:
        return "education", amenity or "school"

    if amenity in HEALTH_AMENITY or healthcare is not None or health_facility_type is not None:
        return "health", health_facility_type or amenity or healthcare

    if amenity in EMERGENCY_AMENITY:
        return "emergency", amenity

    if amenity in GOVERNMENT_AMENITY or office in ("government", "administrative", "diplomatic"):
        return "government", amenity or office

    if amenity == "place_of_worship":
        return "worship", properties.get("religion") or "place_of_worship"

    if amenity in TRANSPORT_AMENITY or railway in ("station", "halt", "tram_stop") or public_transport == "station":
        return "transport", amenity or railway or public_transport

    if amenity == "marketplace":
        return "market", "marketplace"

    if amenity in COMMUNITY_AMENITY or leisure == "park":
        return "community", amenity or leisure

    if leisure in SPORT_LEISURE:
        return "sport", leisure

    return None


def representative_point(geometry: dict):
    """Satu koordinat (longitude, latitude) dari geometri GeoJSON."""

    if not geometry:
        return None

    gtype = geometry.get("type")
    coords = geometry.get("coordinates")

    if not coords:
        return None

    if gtype == "Point":
        return float(coords[0]), float(coords[1])

    if gtype in ("Polygon", "MultiPolygon"):

        xs: list = []
        ys: list = []

        def walk(node):
            if isinstance(node, (list, tuple)):
                # ijson mengembalikan decimal.Decimal untuk bilangan pecahan,
                # sehingga pengecekan memakai numbers.Number (bukan int/float saja).
                if len(node) >= 2 and all(isinstance(v, numbers.Number) for v in node[:2]):
                    xs.append(node[0])
                    ys.append(node[1])
                else:
                    for item in node:
                        walk(item)

        walk(coords)

        if not xs:
            return None

        return (min(xs) + max(xs)) / 2, (min(ys) + max(ys)) / 2

    if gtype == "LineString" and len(coords) >= 1:
        mid = coords[len(coords) // 2]
        return float(mid[0]), float(mid[1])

    return None


def compact_properties(properties: dict) -> dict:
    """Simpan hanya atribut OSM yang terisi (memperkecil kolom JSON).

    Nilai Decimal (hasil parsing ijson) diubah ke float agar dapat
    diserialisasi menjadi JSON.
    """

    compact = {}

    for key, value in properties.items():

        if value is None or value == "":
            continue

        compact[key] = float(value) if isinstance(value, Decimal) else value

    return compact


# ======================================================================
# PENULISAN DATABASE
# ======================================================================

INSERT_SQL = """
INSERT INTO facilities
    (region_id, category, facility_type, name, osm_id, osm_type,
     latitude, longitude, location, source, source_file, properties,
     created_at, updated_at)
VALUES
    (%s, %s, %s, %s, %s, %s, %s, %s,
     ST_SRID(POINT(%s, %s), 4326),
     %s, %s, %s, NOW(), NOW())
AS new
ON DUPLICATE KEY UPDATE
    category = new.category,
    facility_type = new.facility_type,
    name = new.name,
    latitude = new.latitude,
    longitude = new.longitude,
    location = new.location,
    properties = new.properties,
    updated_at = NOW()
"""


def feature_files(folder: str | None):
    """Daftar file fasilitas yang akan diproses."""

    if folder:
        return sorted(
            (FACILITIES_DIR / folder).glob("SIGMA_Fasilitas_Umum_*.geojson")
        )

    return sorted(
        FACILITIES_DIR.glob("*/SIGMA_Fasilitas_Umum_*.geojson")
    )


def stream_file(path: Path, max_features, stats: dict):
    """Hasilkan baris siap-insert dari satu file GeoJSON secara streaming."""

    read = 0
    source_file = f"{path.parent.name}/{path.name}"

    with path.open("rb") as handle:

        for feature in ijson.items(handle, "features.item"):

            read += 1

            if max_features and read > max_features:
                break

            if read % 200000 == 0:
                print(
                    f"   ...{read:,} fitur dibaca / {stats['kept']:,} disimpan",
                    flush=True,
                )

            properties = feature.get("properties") or {}

            classified = classify(properties)

            if not classified:
                stats["read"] += 1
                continue

            point = representative_point(feature.get("geometry"))

            if not point:
                stats["read"] += 1
                continue

            longitude, latitude = point
            category, facility_type = classified

            stats["read"] += 1
            stats["kept"] += 1
            stats["by_category"][category] = stats["by_category"].get(category, 0) + 1

            yield (
                None,                     # region_id (dapat diisi pada tahap lanjutan)
                category,
                facility_type,
                properties.get("name"),
                properties.get("osm_id"),
                properties.get("osm_type"),
                latitude,
                longitude,
                longitude,                # POINT(longitude, latitude)
                latitude,
                "osm_hotosm",
                source_file,
                json.dumps(compact_properties(properties), ensure_ascii=False),
            )

    print(f"   dibaca total: {read:,} fitur", flush=True)


def run(args) -> int:
    files = feature_files(args.folder)

    if not files:
        print("Tidak ada file geojson ditemukan di dataset/raw/facilities.")
        return 1

    print(f"Jumlah file: {len(files)}")
    print(f"Mode       : {'DRY-RUN (tanpa simpan)' if args.dry_run else 'SIMPAN KE DATABASE'}")

    connection = None if args.dry_run else connect()
    cursor = connection.cursor() if connection else None

    stats = {"read": 0, "kept": 0, "by_category": {}}
    started = time.time()

    try:

        for path in files:

            print(f"\n== {path.parent.name} :: {path.name} ==", flush=True)

            batch = []

            for row in stream_file(path, args.max_features, stats):

                if args.dry_run:
                    if stats["kept"] <= 3:
                        print("   contoh:", row[1], row[2], row[6], row[7])
                    continue

                batch.append(row)

                if len(batch) >= args.batch:
                    cursor.executemany(INSERT_SQL, batch)
                    connection.commit()
                    batch.clear()

            if batch:
                cursor.executemany(INSERT_SQL, batch)
                connection.commit()

        elapsed = time.time() - started

        print("\n================ RINGKASAN ================")
        print(f"Fitur dibaca      : {stats['read']:,}")
        print(f"Fasilitas disimpan: {stats['kept']:,}")
        print(f"Durasi            : {elapsed:,.1f} detik")
        print("Per kategori      :")

        for category, total in sorted(stats["by_category"].items(), key=lambda kv: -kv[1]):
            print(f"   - {category:<12}: {total:,}")

        print("===========================================")

    except Exception as error:  # noqa: BLE001

        if connection:
            connection.rollback()

        print(f"\nGagal: {error}")
        return 1

    finally:

        if connection:
            connection.close()

    return 0


def main() -> int:
    parser = argparse.ArgumentParser(
        description="Import dataset fasilitas publik ke tabel `facilities` SIGMA."
    )

    parser.add_argument("--folder", default=None, help="Hanya satu subfolder (mis. Bali).")
    parser.add_argument("--batch", type=int, default=1000, help="Ukuran batch insert.")
    parser.add_argument(
        "--max-features",
        type=int,
        default=None,
        help="Batasi jumlah fitur per file (untuk uji coba).",
    )
    parser.add_argument("--dry-run", action="store_true", help="Hitung saja, tanpa simpan.")

    return run(parser.parse_args())


if __name__ == "__main__":
    raise SystemExit(main())
