"""
SIGMA AI - Fire Risk Router

Endpoint prediksi risiko karhutla yang dipakai bersama oleh:

- app/main.py   -> uvicorn app.main:app
- main.py       -> uvicorn main:app

Model & dataset dimuat secara LAZY (saat request pertama) supaya service
tetap hidup walaupun file model/dataset bermasalah.
"""

from fastapi import APIRouter, HTTPException
from pydantic import BaseModel

import joblib
import pandas as pd

from services.nasa_firms import get_client
from config import FIRMS_DAYS

from services.region_predictor import RegionPredictor


MODEL_PATH = "models/fire_risk_model.pkl"


router = APIRouter()


_model_data = None

_region_predictor = None


def get_model_data():
    """Load model sekali saja, lalu dipakai ulang."""

    global _model_data

    if _model_data is None:

        _model_data = joblib.load(
            MODEL_PATH
        )

    return _model_data


def get_region_predictor():
    """Load predictor wilayah sekali saja, lalu dipakai ulang."""

    global _region_predictor

    if _region_predictor is None:

        _region_predictor = RegionPredictor()

    return _region_predictor


# ==================================================
# Request Body
# ==================================================

class FireRiskInput(BaseModel):

    T2M: float

    RH2M: float

    PRECTOTCORR: float

    WS10M: float

    ALLSKY_SFC_SW_DWN: float

    brightness: float

    confidence: float

    frp: float

    latitude: float

    longitude: float


class RegionInput(BaseModel):

    latitude: float

    longitude: float


# ==================================================
# Manual Prediction
# ==================================================

@router.post("/predict-risk")
def predict_risk(
    data: FireRiskInput
):

    try:

        model_data = get_model_data()

    except Exception as error:

        raise HTTPException(
            status_code=503,
            detail="Model AI belum siap: " + str(error)
        )


    model = model_data["model"]

    encoder = model_data["encoder"]


    input_data = pd.DataFrame([

        data.dict()

    ])


    prediction = model.predict(
        input_data
    )


    risk = encoder.inverse_transform(

        prediction

    )[0]


    probability = model.predict_proba(

        input_data

    )[0]


    confidence = float(
        max(probability)
    )


    return {


        "risk_level": risk,


        "confidence": round(
            confidence,
            2
        ),


        "latitude": data.latitude,


        "longitude": data.longitude


    }


# ==================================================
# Hybrid Region Prediction
# ==================================================

@router.post("/predict-region")
def predict_region(
    data: RegionInput
):

    try:

        region_predictor = get_region_predictor()

    except Exception as error:

        raise HTTPException(
            status_code=503,
            detail="Dataset/model wilayah belum siap: " + str(error)
        )


    return region_predictor.predict(

        data.latitude,

        data.longitude

    )

    
# ==================================================
# NASA FIRMS Hotspots untuk Peta SIGMA
# ==================================================

@router.get("/hotspots")
def get_hotspots():
    """Mengambil deteksi NASA FIRMS terbaru untuk wilayah Indonesia."""

    from config import (
        NASA_FIRMS_MAP_KEY,
        NASA_FIRMS_BASE_URL,
        NASA_FIRMS_SOURCE,
        AI_REQUEST_TIMEOUT,
    )
    import requests
    import csv
    import io

    if not NASA_FIRMS_MAP_KEY:
        raise HTTPException(
            status_code=503,
            detail="NASA FIRMS API key belum dikonfigurasi di AI Service.",
        )

    area = "95,-11,141,6"
    days = FIRMS_DAYS

    url = (
        f"{NASA_FIRMS_BASE_URL.rstrip('/')}/"
        f"{NASA_FIRMS_MAP_KEY}/{NASA_FIRMS_SOURCE}/{area}/{days}"
    )

    try:
        response = requests.get(url, timeout=AI_REQUEST_TIMEOUT)
        response.raise_for_status()
        reader = csv.DictReader(io.StringIO(response.text))

        hotspots = []

        for row in reader:
            try:
                latitude = float(row["latitude"])
                longitude = float(row["longitude"])
            except (KeyError, TypeError, ValueError):
                continue

            confidence, confidence_raw = (
                __import__("services.nasa_firms", fromlist=["parse_confidence"])
                .parse_confidence(row.get("confidence"))
            )

            brightness = row.get("bright_ti4") or row.get("brightness")

            try:
                brightness = float(brightness) if brightness else None
            except (ValueError, TypeError):
                brightness = None

            try:
                frp = float(row["frp"]) if row.get("frp") else None
            except (ValueError, TypeError):
                frp = None

            hotspots.append({
                "latitude": latitude,
                "longitude": longitude,
                "satellite": row.get("satellite") or NASA_FIRMS_SOURCE,
                "brightness": brightness,
                "confidence": confidence,
                "confidence_raw": confidence_raw,
                "frp": frp,
                "acq_date": row.get("acq_date"),
                "acq_time": row.get("acq_time"),
            })

        return {
            "success": True,
            "source": "NASA_FIRMS",
            "count": len(hotspots),
            "hotspots": hotspots,
        }

    except requests.RequestException as error:
        raise HTTPException(
            status_code=502,
            detail=f"Gagal mengambil data NASA FIRMS: {type(error).__name__}",
        )

