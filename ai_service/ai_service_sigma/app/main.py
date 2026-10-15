
from fastapi import FastAPI

from config import setup_logging
from routers.fire_risk import router as fire_risk_router
from routers.yolo_detection import router as yolo_detection_router

setup_logging()

app = FastAPI(
    title="SIGMA AI Service",
    description="AI Engine untuk Mitigasi Karhutla",
    version="1.0.0"
)

app.include_router(fire_risk_router)
app.include_router(yolo_detection_router)


@app.get("/")
def home():
    return {
        "system": "SIGMA AI Service",
        "status": "running"
    }
