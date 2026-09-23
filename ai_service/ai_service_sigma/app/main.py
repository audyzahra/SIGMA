from fastapi import FastAPI


app = FastAPI(
    title="SIGMA AI Service",
    description="AI Engine untuk Mitigasi Karhutla",
    version="1.0.0"
)


@app.get("/")
def home():

    return {
        "system":"SIGMA AI Service",
        "status":"running"
    }