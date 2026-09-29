"""
NASA POWER service (data cuaca harian per titik koordinat).

Endpoint: {NASA_POWER_BASE_URL}/point

Parameter (sesuai feature model):
T2M, RH2M, PRECTOTCORR, WS10M, ALLSKY_SFC_SW_DWN

Nilai -999 dari NASA POWER berarti data hilang, sehingga service ini
memilih tanggal terakhir yang SEMUA parameternya valid.
"""

import logging

from datetime import datetime, timedelta, timezone

import requests

from config import (
    AI_REQUEST_TIMEOUT,
    NASA_CACHE_MINUTES,
    NASA_POWER_BASE_URL,
    NASA_POWER_COMMUNITY,
    NASA_POWER_FILL_VALUE,
    NASA_POWER_LOOKBACK_DAYS,
    NASA_POWER_PARAMETERS,
)

from services import ttl_cache


logger = logging.getLogger("sigma.nasapower")


class NasaPowerClient:
    """Client tipis untuk NASA POWER point API."""

    def __init__(
        self,
        base_url: str | None = None,
        community: str | None = None,
        timeout: float | None = None,
    ):
        self.base_url = (base_url or NASA_POWER_BASE_URL).rstrip("/")

        self.community = community or NASA_POWER_COMMUNITY

        self.timeout = float(timeout or AI_REQUEST_TIMEOUT)

        self.parameters = list(NASA_POWER_PARAMETERS)

    def fetch(self, latitude: float, longitude: float) -> dict:
        """Ambil cuaca terakhir yang lengkap untuk satu koordinat."""

        cache_key = f"nasa_power:{latitude:.4f}:{longitude:.4f}"

        payload, cached = ttl_cache.get_or_set(
            cache_key,
            ttl_cache.ttl_seconds(NASA_CACHE_MINUTES),
            lambda: self._request(latitude, longitude),
        )

        result = dict(payload)

        result["cached"] = cached
        result["source"] = (
            "NASA_POWER" if result.get("success") else "NASA_POWER_FAILED"
        )

        return result

    def _request(self, latitude: float, longitude: float) -> dict:
        """Request NASA POWER (hasil disimpan ke cache oleh fetch())."""

        end = datetime.now(timezone.utc).date()

        start = end - timedelta(days=NASA_POWER_LOOKBACK_DAYS)

        url = f"{self.base_url}/point"

        params = {
            "parameters": ",".join(self.parameters),
            "community": self.community,
            "longitude": longitude,
            "latitude": latitude,
            "start": start.strftime("%Y%m%d"),
            "end": end.strftime("%Y%m%d"),
            "format": "JSON",
        }

        try:
            response = requests.get(url, params=params, timeout=self.timeout)
        except requests.RequestException as error:
            logger.warning(
                "NASA POWER gagal lat=%.4f lng=%.4f : %s",
                latitude,
                longitude,
                type(error).__name__,
            )

            return {
                "success": False,
                "values": None,
                "date": None,
                "message": f"NASA POWER tidak dapat dihubungi ({type(error).__name__})",
            }

        if response.status_code != 200:
            logger.warning(
                "NASA POWER HTTP %s lat=%.4f lng=%.4f",
                response.status_code,
                latitude,
                longitude,
            )

            return {
                "success": False,
                "values": None,
                "date": None,
                "message": f"NASA POWER membalas HTTP {response.status_code}",
            }

        try:
            payload = response.json()
            parameters = payload["properties"]["parameter"]
        except (ValueError, KeyError, TypeError) as error:
            logger.warning("NASA POWER response tidak valid: %s", type(error).__name__)

            return {
                "success": False,
                "values": None,
                "date": None,
                "message": "Response NASA POWER tidak dapat dibaca",
            }

        latest = self._latest_complete_row(parameters)

        if latest is None:
            logger.info(
                "NASA POWER tanpa data lengkap lat=%.4f lng=%.4f",
                latitude,
                longitude,
            )

            return {
                "success": False,
                "values": None,
                "date": None,
                "message": "NASA POWER tidak menyediakan data lengkap pada periode pencarian",
            }

        date, values = latest

        logger.info(
            "NASA POWER ok lat=%.4f lng=%.4f tanggal=%s",
            latitude,
            longitude,
            date,
        )

        return {
            "success": True,
            "values": values,
            "date": date,
            "message": "NASA POWER data diterima",
        }

    def _latest_complete_row(self, parameters: dict):
        """Cari tanggal terakhir dengan seluruh parameter valid."""

        dates = set()

        for name in self.parameters:
            dates.update(parameters.get(name, {}).keys())

        for date in sorted(dates, reverse=True):
            values = {}

            for name in self.parameters:
                number = self._to_float(parameters.get(name, {}).get(date))

                if number is None:
                    values = None
                    break

                values[name] = number

            if values:
                return date, values

        return None

    @staticmethod
    def _to_float(raw) -> float | None:
        """Konversi nilai NASA POWER; -999 / kosong dianggap hilang."""

        if raw is None:
            return None

        try:
            number = float(raw)
        except (TypeError, ValueError):
            return None

        if number == NASA_POWER_FILL_VALUE:
            return None

        return number


_client: NasaPowerClient | None = None


def get_client() -> NasaPowerClient:
    """Singleton client (konsisten dengan cache TTL)."""

    global _client

    if _client is None:
        _client = NasaPowerClient()

    return _client

