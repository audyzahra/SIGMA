from fastapi import FastAPI

from config import setup_logging

from routers.fire_risk import router as fire_risk_router


setup_logging()


app = FastAPI(
    title="SIGMA AI Service",
    description="AI Engine untuk Mitigasi Karhutla",
    version="1.0.0"
)


# Prediksi risiko karhutla (/predict-risk dan /predict-region)
app.include_router(
    fire_risk_router
)


@app.get("/")
def home():

    return {
        "system":"SIGMA AI Service",
        "status":"running"
    }