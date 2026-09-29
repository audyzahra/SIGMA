"""
Konfigurasi terpusat SIGMA AI Service.

Semua nilai dibaca dari environment (.env pada folder ai_service_sigma)
supaya tidak ada hardcode radius / tanggal / URL di banyak tempat.

Catatan: modul ini juga menyiapkan logging standar service.
"""

import logging
import os

from pathlib import Path

from dotenv import load_dotenv


# ======================================================================
# PATH
# ======================================================================

BASE_DIR = Path(__file__).resolve().parent

MODEL_PATH = BASE_DIR / "models" / "fire_risk_model.pkl"

DATASET_PATH = BASE_DIR / "dataset" / "processed" / "fire_risk_dataset.csv"

ENV_PATH = BASE_DIR / ".env"


load_dotenv(ENV_PATH)


# ======================================================================
# HELPER ENV
# ======================================================================

def env_str(key: str, default: str = "") -> str:
    value = os.getenv(key)

    if value is None or str(value).strip() == "":
        return default

    return str(value).strip()


def env_int(key: str, default: int) -> int:
    try:
        return int(float(env_str(key, str(default))))
    except (TypeError, ValueError):
        return default


def env_float(key: str, default: float) -> float:
    try:
        return float(env_str(key, str(default)))
    except (TypeError, ValueError):
        return default


# ======================================================================
# NASA POWER
# ======================================================================

NASA_POWER_BASE_URL = env_str(
    "NASA_POWER_BASE_URL",
    "https://power.larc.nasa.gov/api/temporal/daily/",
)

NASA_POWER_COMMUNITY = env_str("NASA_POWER_COMMUNITY", "AG")

# Parameter yang dipakai model (nama harus sama dengan feature model)
NASA_POWER_PARAMETERS = [
    "T2M",
    "RH2M",
    "PRECTOTCORR",
    "WS10M",
    "ALLSKY_SFC_SW_DWN",
]

# NASA POWER memakai -999 untuk data hilang
NASA_POWER_FILL_VALUE = -999.0

# Berapa hari ke belakang dicari jika data terbaru belum lengkap
NASA_POWER_LOOKBACK_DAYS = max(env_int("NASA_POWER_LOOKBACK_DAYS", 10), 1)


# ======================================================================
# NASA FIRMS
# ======================================================================

NASA_FIRMS_BASE_URL = env_str(
    "NASA_FIRMS_BASE_URL",
    "https://firms.modaps.eosdis.nasa.gov/api/area/csv/",
)

NASA_FIRMS_MAP_KEY = env_str("NASA_FIRMS_MAP_KEY", "")

NASA_FIRMS_SOURCE = env_str("NASA_FIRMS_SOURCE", "VIIRS_SNPP_NRT")

# Radius pencarian hotspot (km) dan periode data (hari, 1-5 sesuai API FIRMS)
FIRMS_RADIUS_KM = env_float("FIRMS_RADIUS_KM", 10.0)

FIRMS_DAYS = min(max(env_int("FIRMS_DAYS", 2), 1), 5)

# Confidence FIRMS untuk VIIRS berupa huruf, model memerlukan angka
FIRMS_CONFIDENCE_MAP = {
    "l": 30,
    "n": 60,
    "h": 90,
}


# ======================================================================
# CACHE & TIMEOUT
# ======================================================================

NASA_CACHE_MINUTES = max(env_int("NASA_CACHE_MINUTES", 30), 1)

AI_REQUEST_TIMEOUT = max(env_float("AI_REQUEST_TIMEOUT", 20.0), 1.0)


# ======================================================================
# FEATURE MODEL
# ======================================================================

FEATURE_NAMES = [
    "T2M",
    "RH2M",
    "PRECTOTCORR",
    "WS10M",
    "ALLSKY_SFC_SW_DWN",
    "brightness",
    "confidence",
    "frp",
    "latitude",
    "longitude",
]


# ======================================================================
# LOGGING
# ======================================================================

def setup_logging() -> None:
    """Konfigurasi logging standar (sekali saja, tidak spam)."""

    root = logging.getLogger()

    if root.handlers:
        return

    logging.basicConfig(
        level=os.getenv("AI_LOG_LEVEL", "INFO").upper(),
        format="%(asctime)s | %(levelname)s | %(name)s | %(message)s",
    )


def redacted_key(key: str) -> str:
    """Tampilkan status key tanpa pernah membocorkan nilainya."""

    if not key:
        return "TIDAK ADA"

    return f"terpasang ({len(key)} karakter)"
