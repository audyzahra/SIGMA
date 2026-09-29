"""
NASA FIRMS service (hotspot / active fire aktual).

Endpoint area API:
{NASA_FIRMS_BASE_URL}{MAP_KEY}/{SOURCE}/{west,south,east,north}/{DAYS}

Data yang dipakai model:
brightness  <- bright_ti4
confidence  <- confidence (huruf untuk VIIRS: l/n/h)
frp         <- frp

Radius & periode diambil dari konfigurasi (FIRMS_RADIUS_KM, FIRMS_DAYS).
API key TIDAK pernah dicatat pada log.
"""

import csv
import io
import logging
import math

from datetime import datetime, timezone

import requests

from config import (
    AI_REQUEST_TIMEOUT,
    FIRMS_CONFIDENCE_MAP,
    FIRMS_DAYS,
    FIRMS_RADIUS_KM,
    NASA_CACHE_MINUTES,
    NASA_FIRMS_BASE_URL,
    NASA_FIRMS_MAP_KEY,
    NASA_FIRMS_SOURCE,
    redacted_key,
)

from services import ttl_cache


logger = logging.getLogger("sigma.nasafirms")

EARTH_RADIUS_KM = 6371.0088

KM_PER_DEGREE_LATITUDE = 110.574


def haversine_km(lat1: float, lng1: float, lat2: float, lng2: float) -> float:
    """Jarak dua koordinat dalam kilometer."""

    phi1 = math.radians(lat1)
    phi2 = math.radians(lat2)

    delta_phi = math.radians(lat2 - lat1)
    delta_lambda = math.radians(lng2 - lng1)

    a = (
        math.sin(delta_phi / 2) ** 2
        + math.cos(phi1) * math.cos(phi2) * math.sin(delta_lambda / 2) ** 2
    )

    return 2 * EARTH_RADIUS_KM * math.asin(min(1.0, math.sqrt(a)))


def bounding_box(latitude: float, longitude: float, radius_km: float) -> str:
    """Bounding box 'west,south,east,north' dari radius km."""

    delta_latitude = radius_km / KM_PER_DEGREE_LATITUDE

    cos_latitude = max(math.cos(math.radians(latitude)), 0.01)

    delta_longitude = radius_km / (KM_PER_DEGREE_LATITUDE * cos_latitude)

    west = max(longitude - delta_longitude, -180.0)
    east = min(longitude + delta_longitude, 180.0)

    south = max(latitude - delta_latitude, -90.0)
    north = min(latitude + delta_latitude, 90.0)

    return f"{west:.5f},{south:.5f},{east:.5f},{north:.5f}"


def parse_confidence(raw):
    """Confidence FIRMS -> angka (VIIRS memakai huruf) + nilai asli."""

    if raw is None:
        return None, None

    text = str(raw).strip()

    if text == "":
        return None, None

    lowered = text.lower()

    if lowered in FIRMS_CONFIDENCE_MAP:
        return float(FIRMS_CONFIDENCE_MAP[lowered]), text

    try:
        return float(text), text
    except ValueError:
        return None, text


class NasaFirmsClient:
    """Client tipis untuk NASA FIRMS area API."""

    def __init__(
        self,
        api_key: str | None = None,
        base_url: str | None = None,
        source: str | None = None,
        timeout: float | None = None,
    ):
        self.api_key = (api_key if api_key is not None else NASA_FIRMS_MAP_KEY) or ""

        self.base_url = (base_url or NASA_FIRMS_BASE_URL).rstrip("/")

        self.source = source or NASA_FIRMS_SOURCE

        self.timeout = float(timeout or AI_REQUEST_TIMEOUT)

    def fetch(
        self,
        latitude: float,
        longitude: float,
        radius_km: float | None = None,
        days: int | None = None,
    ) -> dict:
        """Ambil hotspot terdekat di sekitar koordinat."""

        radius = float(radius_km or FIRMS_RADIUS_KM)

        period = int(days or FIRMS_DAYS)

        cache_key = (
            f"nasa_firms:{latitude:.4f}:{longitude:.4f}:{radius:.1f}:{period}"
        )

        payload, cached = ttl_cache.get_or_set(
            cache_key,
            ttl_cache.ttl_seconds(NASA_CACHE_MINUTES),
            lambda: self._request(latitude, longitude, radius, period),
        )

        result = dict(payload)

        result["cached"] = cached

        return result

    def _request(
        self,
        latitude: float,
        longitude: float,
        radius_km: float,
        days: int,
    ) -> dict:
        """Request FIRMS (dipanggil fetch(), hasil disimpan ke cache)."""

        base = {
            "found": False,
            "hotspot": None,
            "count": 0,
            "total_rows": 0,
            "nearest_distance_km": None,
            "radius_km": radius_km,
            "days": days,
            "source": "NASA_FIRMS",
            "fetched_at": datetime.now(timezone.utc).isoformat(timespec="seconds"),
        }

        if not self.api_key:
            logger.warning(
                "FIRMS dilewati lat=%.4f lng=%.4f : API key %s",
                latitude,
                longitude,
                redacted_key(self.api_key),
            )

            return dict(
                base,
                success=False,
                source="FIRMS_ERROR",
                message="NASA_FIRMS_MAP_KEY belum dikonfigurasi",
            )

        url = (
            f"{self.base_url}/{self.api_key}/{self.source}/"
            f"{bounding_box(latitude, longitude, radius_km)}/{days}"
        )

        try:
            response = requests.get(url, timeout=self.timeout)
        except requests.RequestException as error:
            logger.warning(
                "FIRMS gagal lat=%.4f lng=%.4f radius=%.1fkm : %s",
                latitude,
                longitude,
                radius_km,
                type(error).__name__,
            )

            return dict(
                base,
                success=False,
                source="FIRMS_ERROR",
                message=f"NASA FIRMS tidak dapat dihubungi ({type(error).__name__})",
            )

        if response.status_code != 200:
            logger.warning(
                "FIRMS HTTP %s lat=%.4f lng=%.4f radius=%.1fkm days=%s",
                response.status_code,
                latitude,
                longitude,
                radius_km,
                days,
            )

            return dict(
                base,
                success=False,
                source="FIRMS_ERROR",
                message=f"NASA FIRMS membalas HTTP {response.status_code}",
            )

        rows = self._parse_csv(response.text, latitude, longitude)

        if rows is None:
            return dict(
                base,
                success=False,
                source="FIRMS_ERROR",
                message="Response NASA FIRMS tidak dapat dibaca",
            )

        inside = [row for row in rows if row["distance_km"] <= radius_km]

        nearest = None

        if inside:
            nearest = min(inside, key=lambda row: row["distance_km"])
        elif rows:
            nearest = min(rows, key=lambda row: row["distance_km"])

        logger.info(
            "FIRMS ok lat=%.4f lng=%.4f radius=%.1fkm days=%s total=%s dalam_radius=%s",
            latitude,
            longitude,
            radius_km,
            days,
            len(rows),
            len(inside),
        )

        if not inside:
            return dict(
                base,
                success=True,
                found=False,
                count=0,
                total_rows=len(rows),
                nearest_distance_km=(
                    round(nearest["distance_km"], 2) if nearest else None
                ),
                source="NASA_FIRMS_NO_HOTSPOT",
                message="Tidak ada hotspot dalam radius pencarian",
            )

        return dict(
            base,
            success=True,
            found=True,
            count=len(inside),
            total_rows=len(rows),
            nearest_distance_km=round(nearest["distance_km"], 2),
            hotspot=nearest,
            source="NASA_FIRMS",
            message=f"{len(inside)} hotspot dalam radius {radius_km:.0f} km",
        )


    @staticmethod
    def _parse_csv(text: str, latitude: float, longitude: float):
        """Parse CSV FIRMS menjadi list dict (None jika malformed)."""

        if text is None:
            return None

        cleaned = text.strip()

        if cleaned == "":
            return []

        if cleaned.lower().startswith("<!doctype") or "<html" in cleaned.lower():
            return None

        try:
            reader = csv.DictReader(io.StringIO(cleaned))
        except csv.Error:
            return None

        rows = []

        for row in reader:
            try:
                row_latitude = float(row.get("latitude"))
                row_longitude = float(row.get("longitude"))
            except (TypeError, ValueError):
                continue

            brightness = None

            for column in ("bright_ti4", "brightness"):
                value = row.get(column)

                if value is None or str(value).strip() == "":
                    continue

                try:
                    brightness = float(value)
                    break
                except ValueError:
                    continue

            try:
                frp = float(row.get("frp"))
            except (TypeError, ValueError):
                frp = None

            confidence, confidence_raw = parse_confidence(row.get("confidence"))

            rows.append(
                {
                    "latitude": row_latitude,
                    "longitude": row_longitude,
                    "brightness": brightness,
                    "confidence": confidence,
                    "confidence_raw": confidence_raw,
                    "frp": frp,
                    "distance_km": haversine_km(
                        latitude,
                        longitude,
                        row_latitude,
                        row_longitude,
                    ),
                    "acq_date": row.get("acq_date"),
                    "acq_time": row.get("acq_time"),
                    "satellite": row.get("satellite"),
                }
            )

        return rows


_client: NasaFirmsClient | None = None


def get_client() -> NasaFirmsClient:
    """Singleton client (konsisten dengan cache TTL)."""

    global _client

    if _client is None:
        _client = NasaFirmsClient()

    return _client

