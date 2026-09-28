import pandas as pd
import joblib
import os


from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier

from sklearn.preprocessing import LabelEncoder

from sklearn.metrics import accuracy_score, classification_report




DATASET = "dataset/processed/risk_features.csv"

MODEL_PATH = "models/fire_risk_model.pkl"





print("==============================")
print("Loading dataset")
print("==============================")



df = pd.read_csv(
    DATASET
)



print("Jumlah data:")
print(len(df))



print("\nDistribusi risiko:")

print(
    df["risk_level"].value_counts()
)





# =================================
# Feature yang digunakan AI
# =================================

FEATURES = [

    "T2M",

    "RH2M",

    "PRECTOTCORR",

    "WS10M",

    "ALLSKY_SFC_SW_DWN",

    "brightness",

    "confidence",

    "frp",

    "latitude",

    "longitude"

]





# =================================
# Validasi kolom
# =================================


missing_features = [

    col for col in FEATURES

    if col not in df.columns

]



if missing_features:

    print("==============================")

    print("ERROR: Kolom tidak ditemukan")

    print(missing_features)

    print("==============================")

    exit()




# =================================
# Feature dan Target
# =================================


X = df[FEATURES]


y = df["risk_level"]




print("\nFeature yang digunakan:")

print(
    FEATURES
)





# =================================
# Pastikan numeric
# =================================


for col in FEATURES:

    X[col] = pd.to_numeric(

        X[col],

        errors="coerce"

    )



X = X.fillna(
    X.median()
)





# =================================
# Encode Target
# =================================


encoder = LabelEncoder()



y = encoder.fit_transform(

    y

)





print("\nLabel:")

print(
    encoder.classes_
)





# =================================
# Split Dataset
# =================================


X_train, X_test, y_train, y_test = train_test_split(

    X,

    y,

    test_size=0.2,

    random_state=42

)





# =================================
# Training Random Forest
# =================================


print("==============================")
print("Training Random Forest")
print("==============================")



model = RandomForestClassifier(

    n_estimators=100,

    random_state=42,

    n_jobs=-1

)



model.fit(

    X_train,

    y_train

)





# =================================
# Evaluation
# =================================


prediction = model.predict(

    X_test

)



accuracy = accuracy_score(

    y_test,

    prediction

)



print("==============================")

print("Accuracy:")

print(
    accuracy
)




print("\nClassification Report:")



print(

    classification_report(

        y_test,

        prediction,

        target_names=encoder.classes_

    )

)





# =================================
# Save Model
# =================================


os.makedirs(

    "models",

    exist_ok=True

)




joblib.dump(

    {

        "model": model,

        "encoder": encoder,

        "features": FEATURES

    },

    MODEL_PATH

)




print("==============================")

print("MODEL BERHASIL DISIMPAN")

print("==============================")

print(

    MODEL_PATH

)