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
