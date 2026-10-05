"""
Helper koneksi database + lokasi dataset untuk pipeline ingestion SIGMA.

Skrip ingestion (facilities_import.py, population_import.py) memakai modul ini
supaya konfigurasi koneksi tidak ditulis ulang dan selalu mengikuti file .env
milik backend Laravel (backend/web_sigma/.env).

Database SIGMA memakai MySQL 8 (bukan PostgreSQL), sehingga driver yang
dipakai adalah PyMySQL.
"""

from __future__ import annotations

import os
from pathlib import Path


# ai_service/ai_service_sigma/data_pipeline/db.py  ->  .../ai_service_sigma
BASE_DIR = Path(__file__).resolve().parent.parent

# .../SIGMA
REPO_ROOT = BASE_DIR.parent.parent

# .env milik Laravel (satu-satunya sumber kredensial database)
LARAVEL_ENV = REPO_ROOT / "backend" / "web_sigma" / ".env"

# Lokasi dataset mentah
RAW_DIR = BASE_DIR / "dataset" / "raw"

FACILITIES_DIR = RAW_DIR / "facilities"

POPULATION_RASTER = RAW_DIR / "population" / "idn_ppp_2020_UNadj.tif"


def _parse_env(path: Path) -> dict:
    """Parser .env sederhana (KEY=VALUE, abaikan komentar)."""

    values: dict = {}

    if not path.exists():
        return values

    for raw_line in path.read_text(encoding="utf-8", errors="ignore").splitlines():

        line = raw_line.strip()

        if not line or line.startswith("#") or "=" not in line:
            continue

        key, _, value = line.partition("=")

        values[key.strip()] = value.strip().strip('"').strip("'")

    return values


def db_config() -> dict:
    """Kredensial database dari .env Laravel (variabel DB_*)."""

    env = _parse_env(LARAVEL_ENV)

    # variabel environment proses menang (memudahkan override saat uji)
    env.update({key: value for key, value in os.environ.items() if key.startswith("DB_")})

    return {
        "host": env.get("DB_HOST", "127.0.0.1"),
        "port": int(env.get("DB_PORT", "3306")),
        "user": env.get("DB_USERNAME", "root"),
        "password": env.get("DB_PASSWORD", ""),
        "database": env.get("DB_DATABASE", "sigma"),
    }


def connect():
    """Koneksi PyMySQL ke database SIGMA (autocommit dimatikan)."""

    import pymysql

    config = db_config()

    return pymysql.connect(
        host=config["host"],
        port=config["port"],
        user=config["user"],
        password=config["password"],
        database=config["database"],
        charset="utf8mb4",
        autocommit=False,
        cursorclass=pymysql.cursors.Cursor,
    )
