"""
Region Predictor (SIGMA AI).

Alur prediksi:

    koordinat region
        -> NASA POWER  (cuaca aktual)
        -> NASA FIRMS  (hotspot aktual)
        -> 10 feature  (urutan sesuai model)
        -> fire_risk_model.pkl
        -> risk_level + confidence + metadata sumber data

Fallback:

    NASA POWER gagal        -> statistik dataset (CSV_FALLBACK)
    NASA FIRMS gagal        -> hotspot terdekat di dataset (CSV_FALLBACK)
    FIRMS tanpa hotspot     -> nilai persentil rendah dataset
                               (NASA_FIRMS_NO_HOTSPOT)

Model (fire_risk_model.pkl) dan dataset TIDAK diubah oleh proses ini.
"""

import logging

from datetime import datetime, timezone

import joblib
import pandas as pd

from config import (
    DATASET_PATH,
    FEATURE_NAMES,
    FIRMS_DAYS,
    FIRMS_RADIUS_KM,
    MODEL_PATH,
)

from services import nasa_firms, nasa_power


logger = logging.getLogger("sigma.predictor")


class RegionPredictor:
    """Prediksi risiko karhutla berbasis cuaca & hotspot aktual."""

    def __init__(self, model_path=None, dataset_path=None):
        self.model_path = str(model_path or MODEL_PATH)

        self.dataset_path = str(dataset_path or DATASET_PATH)

        self.model_data = joblib.load(self.model_path)

        self.model = self.model_data["model"]

        self.encoder = self.model_data["encoder"]

        self.features = list(self.model_data["features"])

        self._validate_model_features()

        # df_raw  : seluruh dataset (termasuk baris cuaca tanpa koordinat)
        # df      : hanya baris yang punya koordinat (untuk fallback hotspot)
        self.df_raw = self._load_dataset(self.dataset_path)

        self.df = self.df_raw.dropna(
            subset=["latitude", "longitude"]
        ).reset_index(drop=True)

        self.dataset_statistics = self._dataset_statistics()

        logger.info(
            "RegionPredictor siap | model=%s | dataset=%s baris",
            self.model_path,
            len(self.df),
        )

    # ==================================================================
    # MODEL & DATASET
    # ==================================================================

    def _validate_model_features(self) -> None:
        """Feature model harus sama dengan daftar feature yang didukung."""

        if self.features != FEATURE_NAMES:
            raise ValueError(
                "Feature model tidak sesuai dengan yang diharapkan. "
                f"model={self.features} diharapkan={FEATURE_NAMES}"
            )

    def _load_dataset(self, path: str) -> pd.DataFrame:
        """
        Dataset dipakai hanya untuk fallback & referensi.

        Baris TIDAK dibuang di sini: dataset berisi dua kelompok baris
        (riwayat hotspot dengan koordinat, dan riwayat cuaca tanpa
        koordinat). Baris cuaca tetap diperlukan untuk fallback cuaca.
        """

        try:
            frame = pd.read_csv(path, low_memory=False)
        except (OSError, ValueError) as error:
            logger.warning(
                "Dataset fallback tidak dapat dibaca (%s): %s",
                path,
                type(error).__name__,
            )

            return pd.DataFrame(columns=FEATURE_NAMES)

        for column in ("latitude", "longitude"):
            if column in frame.columns:
                frame[column] = pd.to_numeric(frame[column], errors="coerce")

        return frame

    def _dataset_statistics(self) -> dict:
        """Statistik dataset untuk fallback (median & persentil rendah)."""

        statistics = {
            "weather_median": {},
            "hotspot_low": {},
            "rows": int(len(self.df_raw)),
            "rows_with_coordinate": int(len(self.df)),
        }

        # Cuaca: diambil dari seluruh dataset karena baris cuaca tidak
        # menyimpan latitude/longitude (hasil NASA POWER satu titik).
        for column in ("T2M", "RH2M", "PRECTOTCORR", "WS10M", "ALLSKY_SFC_SW_DWN"):
            if column not in self.df_raw.columns:
                continue

            series = pd.to_numeric(self.df_raw[column], errors="coerce").dropna()

            if series.empty:
                continue

            statistics["weather_median"][column] = float(series.median())

        # Hotspot: persentil-10 dari riwayat hotspot (masih di dalam
        # rentang data training, dipakai saat FIRMS tidak menemukan hotspot).
        for column in ("brightness", "frp"):
            if column not in self.df.columns:
                continue

            series = pd.to_numeric(self.df[column], errors="coerce").dropna()

            if series.empty:
                continue

            statistics["hotspot_low"][column] = float(series.quantile(0.10))

        statistics["hotspot_low"]["confidence"] = 0.0

        return statistics

    # ==================================================================
    # FALLBACK DATASET
    # ==================================================================

    def _nearest_dataset_row(self, latitude: float, longitude: float, require_hotspot: bool = False):
        """Baris dataset terdekat (dipakai untuk fallback)."""

        if self.df.empty:
            return None

        frame = self.df

        if require_hotspot and "brightness" in frame.columns:
            frame = frame[pd.to_numeric(frame["brightness"], errors="coerce").notna()]

        if frame.empty:
            return None

        distance = (
            (frame["latitude"] - latitude) ** 2
            + (frame["longitude"] - longitude) ** 2
        )

        index = distance.idxmin()

        return frame.loc[index]

    @staticmethod
    def _safe_float(value, default: float | None = None):
        """Konversi nilai ke float; NaN / kosong menjadi default."""

        try:
            number = float(value)
        except (TypeError, ValueError):
            return default

        if pd.isna(number):
            return default

        return number

    # ==================================================================
    # SUMBER DATA
    # ==================================================================

    def _resolve_weather(self, latitude: float, longitude: float) -> tuple:
        """Cuaca dari NASA POWER; jika gagal pakai statistik dataset."""

        result = nasa_power.get_client().fetch(latitude, longitude)

        if result.get("success") and result.get("values"):
            values = {
                name: float(result["values"][name])
                for name in ("T2M", "RH2M", "PRECTOTCORR", "WS10M", "ALLSKY_SFC_SW_DWN")
                if name in result["values"]
            }

            if len(values) == 5:
                return values, "NASA_POWER", {
                    "date": result.get("date"),
                    "cached": bool(result.get("cached")),
                    "message": result.get("message"),
                }

        fallback = dict(self.dataset_statistics.get("weather_median", {}))

        if len(fallback) == 5:
            logger.info(
                "Cuaca fallback dataset dipakai lat=%.4f lng=%.4f (%s)",
                latitude,
                longitude,
                result.get("message"),
            )

            return fallback, "CSV_FALLBACK", {
                "date": None,
                "cached": bool(result.get("cached")),
                "message": result.get("message"),
                "note": "Statistik dataset, bukan cuaca spesifik wilayah",
            }

        return None, "UNAVAILABLE", {
            "date": None,
            "cached": False,
            "message": result.get("message"),
        }

    def _resolve_hotspot(self, latitude: float, longitude: float) -> tuple:
        """Hotspot dari NASA FIRMS; fallback dataset bila perlu."""

        radius_km = FIRMS_RADIUS_KM
        days = FIRMS_DAYS

        result = nasa_firms.get_client().fetch(latitude, longitude, radius_km, days)

        detail = {
            "found": bool(result.get("found")),
            "count": int(result.get("count") or 0),
            "total_rows": int(result.get("total_rows") or 0),
            "nearest_distance_km": result.get("nearest_distance_km"),
            "radius_km": radius_km,
            "days": days,
            "cached": bool(result.get("cached")),
            "fetched_at": result.get("fetched_at"),
            "fallback": None,
            "confidence_raw": None,
            "acq_date": None,
            "message": result.get("message"),
        }

        hotspot = result.get("hotspot") or {}

        if result.get("found") and hotspot:
            values = {
                "brightness": self._safe_float(hotspot.get("brightness"), 0.0),
                "confidence": self._safe_float(hotspot.get("confidence"), 0.0),
                "frp": self._safe_float(hotspot.get("frp"), 0.0),
            }

            detail["confidence_raw"] = hotspot.get("confidence_raw")
            detail["acq_date"] = hotspot.get("acq_date")

            return values, "NASA_FIRMS", detail

        # FIRMS berhasil tetapi tidak ada hotspot dalam radius
        if result.get("success"):
            low = dict(self.dataset_statistics.get("hotspot_low", {}))

            if low:
                detail["fallback"] = "DATASET_P10_FALLBACK"

                return low, "NASA_FIRMS_NO_HOTSPOT", detail

            return None, "UNAVAILABLE", detail

        # FIRMS gagal -> hotspot terdekat pada dataset
        row = self._nearest_dataset_row(latitude, longitude, require_hotspot=True)

        if row is not None:
            values = {
                "brightness": self._safe_float(row.get("brightness"), 0.0),
                "confidence": 0.0,
                "frp": self._safe_float(row.get("frp"), 0.0),
            }

            detail["fallback"] = "CSV_FALLBACK"

            return values, "CSV_FALLBACK", detail

        low = dict(self.dataset_statistics.get("hotspot_low", {}))

        if low:
            detail["fallback"] = "DATASET_P10_FALLBACK"

            return low, "UNAVAILABLE", detail

        return None, "UNAVAILABLE", detail



    # ==================================================================
    # PREDIKSI
    # ==================================================================

    def predict(self, latitude, longitude) -> dict:
        """Prediksi risiko untuk satu koordinat."""

        latitude = self._safe_float(latitude)
        longitude = self._safe_float(longitude)

        if latitude is None or longitude is None:
            return self._failure("Koordinat wilayah tidak valid")

        weather_values, weather_source, weather_detail = self._resolve_weather(
            latitude, longitude
        )

        hotspot_values, hotspot_source, hotspot_detail = self._resolve_hotspot(
            latitude, longitude
        )

        if not weather_values or not hotspot_values:
            return self._failure(
                "Data cuaca / hotspot tidak tersedia untuk wilayah ini",
                data_source={"weather": weather_source, "hotspot": hotspot_source},
            )

        features = dict(weather_values)

        features.update(hotspot_values)

        features["latitude"] = latitude
        features["longitude"] = longitude

        frame = self._build_feature_frame(features)

        prediction = self.model.predict(frame)

        risk = str(self.encoder.inverse_transform(prediction)[0])

        probability = self.model.predict_proba(frame)[0]

        confidence = float(max(probability))

        logger.info(
            "Prediksi lat=%.4f lng=%.4f level=%s confidence=%.2f weather=%s hotspot=%s",
            latitude,
            longitude,
            risk,
            confidence,
            weather_source,
            hotspot_source,
        )

        return {
            "success": True,
            "risk_level": risk,
            "confidence": round(confidence, 2),
            "temperature": round(float(features["T2M"]), 2),
            "humidity": round(float(features["RH2M"]), 2),
            "rainfall": round(float(features["PRECTOTCORR"]), 2),
            "wind_speed": round(float(features["WS10M"]), 2),
            "solar_radiation": round(float(features["ALLSKY_SFC_SW_DWN"]), 2),
            "latitude": round(latitude, 6),
            "longitude": round(longitude, 6),
            "hotspot": {
                "found": bool(hotspot_detail["found"]),
                "brightness": round(float(features["brightness"]), 2),
                "confidence": round(float(features["confidence"]), 2),
                "confidence_raw": hotspot_detail["confidence_raw"],
                "frp": round(float(features["frp"]), 2),
                "count": hotspot_detail["count"],
                "distance_km": hotspot_detail["nearest_distance_km"],
                "fallback": hotspot_detail["fallback"],
                "acq_date": hotspot_detail["acq_date"],
            },
            "data_source": {
                "weather": weather_source,
                "hotspot": hotspot_source,
            },
            "metadata": {
                "weather_date": weather_detail["date"],
                "weather_message": weather_detail["message"],
                "weather_note": weather_detail.get("note"),
                "hotspot_message": hotspot_detail["message"],
                "hotspot_total_rows": hotspot_detail["total_rows"],
                "radius_km": hotspot_detail["radius_km"],
                "days": hotspot_detail["days"],
                "cached": {
                    "weather": bool(weather_detail["cached"]),
                    "hotspot": bool(hotspot_detail["cached"]),
                },
                "fetched_at": datetime.now(timezone.utc).isoformat(timespec="seconds"),
                "model": self.model_path.replace("\\", "/").split("/")[-1],
                "model_classes": [str(name) for name in self.encoder.classes_],
                "features": self.features,
            },
        }

    def _build_feature_frame(self, features: dict) -> pd.DataFrame:
        """DataFrame dengan urutan feature persis seperti model."""

        missing = [name for name in self.features if name not in features]

        if missing:
            raise ValueError(f"Feature tidak lengkap untuk model: {missing}")

        frame = pd.DataFrame(
            [[features[name] for name in self.features]],
            columns=self.features,
        )

        if list(frame.columns) != list(self.features):
            raise ValueError("Urutan feature tidak sesuai dengan model")

        if frame.isna().any().any():
            raise ValueError("Feature masih mengandung NaN")

        return frame

    def _failure(self, message: str, **extra) -> dict:
        """Response gagal yang tetap aman untuk diproses Laravel."""

        logger.warning("Prediksi gagal: %s", message)

        payload = {
            "success": False,
            "risk_level": "UNKNOWN",
            "confidence": 0,
            "message": message,
        }

        payload.update(extra)

        return payload

    def info(self) -> dict:
        """Ringkasan konfigurasi predictor (untuk debugging/verifikasi)."""

        return {
            "model": self.model_path.replace("\\", "/").split("/")[-1],
            "features": self.features,
            "model_classes": [str(name) for name in self.encoder.classes_],
            "dataset": self.dataset_path.replace("\\", "/").split("/")[-1],
            "dataset_rows": int(len(self.df)),
            "dataset_statistics": self.dataset_statistics,
            "config": {
                "firms_radius_km": FIRMS_RADIUS_KM,
                "firms_days": FIRMS_DAYS,
            },
        }
