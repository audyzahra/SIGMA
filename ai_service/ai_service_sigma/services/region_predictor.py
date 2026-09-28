import pandas as pd
import joblib
import math



DATASET = "dataset/processed/fire_risk_dataset.csv"

MODEL = "models/fire_risk_model.pkl"





class RegionPredictor:


    def __init__(self):


        self.df = pd.read_csv(
            DATASET
        )



        model_data = joblib.load(
            MODEL
        )


        self.model = model_data["model"]

        self.encoder = model_data["encoder"]

        self.features = model_data["features"]





    def safe_float(self, value):

        value = pd.to_numeric(
            value,
            errors="coerce"
        )


        if pd.isna(value) or math.isinf(float(value)):

            return 0.0


        return float(value)







    def predict(
        self,
        latitude,
        longitude
    ):


        data = self.df.copy()



        # =================================
        # Cleaning koordinat
        # =================================


        data["latitude"] = pd.to_numeric(

            data["latitude"],

            errors="coerce"

        )


        data["longitude"] = pd.to_numeric(

            data["longitude"],

            errors="coerce"

        )



        data = data.dropna(

            subset=[

                "latitude",

                "longitude"

            ]

        )





        # =================================
        # Cari titik terdekat
        # =================================


        data["distance"] = (

            (data["latitude"] - latitude) ** 2

            +

            (data["longitude"] - longitude) ** 2

        )



        nearest = data.sort_values(

            "distance"

        ).head(1)





        if nearest.empty:

            return {

                "risk_level": "UNKNOWN",

                "confidence": 0,

                "message": "Data wilayah tidak ditemukan"

            }






        print("==============================")
        print("DATA TERDEKAT")
        print(nearest)
        print("==============================")







        # =================================
        # Ambil fitur AI
        # =================================


        features = nearest[

            self.features

        ].copy()





        # =================================
        # Cleaning input model
        # =================================


        for col in self.features:


            features[col] = pd.to_numeric(

                features[col],

                errors="coerce"

            )



        features = features.fillna(0)





        print("==============================")
        print("FEATURE INPUT AI")
        print(features)
        print(features.dtypes)
        print("==============================")







        # =================================
        # Prediksi Model
        # =================================


        prediction = self.model.predict(

            features

        )



        risk = self.encoder.inverse_transform(

            prediction

        )[0]



        probability = self.model.predict_proba(

            features

        )[0]







        # =================================
        # Ambil parameter cuaca
        # =================================


        temperature = self.safe_float(

            nearest["T2M"].iloc[0]

        )


        humidity = self.safe_float(

            nearest["RH2M"].iloc[0]

        )


        rainfall = self.safe_float(

            nearest["PRECTOTCORR"].iloc[0]

        )


        wind_speed = self.safe_float(

            nearest["WS10M"].iloc[0]

        )








        # =================================
        # Response
        # =================================


        return {


            "risk_level": str(risk),


            "confidence": round(

                self.safe_float(

                    max(probability)

                ),

                2

            ),



            "temperature": round(

                temperature,

                2

            ),



            "humidity": round(

                humidity,

                2

            ),



            "rainfall": round(

                rainfall,

                2

            ),



            "wind_speed": round(

                wind_speed,

                2

            ),



            "latitude": self.safe_float(

                latitude

            ),



            "longitude": self.safe_float(

                longitude

            )

        }