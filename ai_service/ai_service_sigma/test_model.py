import joblib
import pandas as pd


MODEL_PATH = "models/fire_risk_model.pkl"


print("Loading model...")


model_data = joblib.load(
    MODEL_PATH
)


model = model_data["model"]

encoder = model_data["encoder"]


print("Model loaded")



sample = pd.DataFrame([

    {

        "T2M":35,

        "RH2M":40,

        "PRECTOTCORR":1,

        "WS10M":5,

        "ALLSKY_SFC_SW_DWN":25,

        "brightness":320,

        "confidence":90,

        "frp":50,

        "latitude":-6.2,

        "longitude":107.6

    }

])



# prediksi angka

prediction = model.predict(
    sample
)



# ubah angka menjadi label

risk = encoder.inverse_transform(
    prediction
)



# confidence

probability = model.predict_proba(
    sample
)



print("================")
print("Prediksi Risiko")
print("================")


print(
    "Risk :",
    risk[0]
)


print(
    "Confidence :",
    round(max(probability[0]) * 100, 2),
    "%"
)