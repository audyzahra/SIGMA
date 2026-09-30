from fastapi import FastAPI

from config import setup_logging

from routers.fire_risk import router as fire_risk_router


setup_logging()


# ==================================================
# FastAPI
# ==================================================

app = FastAPI(

    title="SIGMA AI Fire Risk API",

    description="AI Prediction Karhutla SIGMA",

    version="1.0"

)


# ==================================================
# Fire Risk Endpoints
# ==================================================
#
# /predict-risk   -> prediksi memakai fitur lengkap
# /predict-region -> prediksi berdasarkan koordinat wilayah
#
# Implementasi dipakai bersama dengan app/main.py
# (lihat routers/fire_risk.py).

app.include_router(

    fire_risk_router

)


# ==================================================
# Root
# ==================================================

@app.get("/")
def home():

    return {

        "status": "running",

        "service": "SIGMA AI Fire Risk"

    }
