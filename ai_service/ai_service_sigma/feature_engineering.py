import pandas as pd
import os


INPUT = "dataset/processed/fire_risk_dataset.csv"

OUTPUT = "dataset/processed/risk_features.csv"


print("Loading dataset...")

df = pd.read_csv(INPUT)


print("==============================")
print("Jumlah data awal:", len(df))
print("==============================")


print("Cleaning data...")


# ==========================
# Konversi kolom numerik
# ==========================

numeric_columns = [

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


for col in numeric_columns:

    if col in df.columns:

        # ubah string menjadi numeric
        df[col] = pd.to_numeric(
            df[col],
            errors="coerce"
        )


        # isi nilai kosong dengan median

        median_value = df[col].median()


        df[col] = df[col].fillna(
            median_value
        )



# ==========================
# Feature Risk Label
# ==========================

def risk_label(row):

    score = 0


    # suhu tinggi
    if row["T2M"] >= 30:
        score += 1


    # kelembapan rendah
    if row["RH2M"] <= 70:
        score += 1


    # hujan rendah
    if row["PRECTOTCORR"] <= 5:
        score += 1


    # hotspot confidence
    if row["confidence"] >= 50:
        score += 1


    # FRP api
    if row["frp"] >= 5:
        score += 1



    if score >= 4:

        return "HIGH"


    elif score >= 2:

        return "MEDIUM"


    else:

        return "LOW"


print("Membuat label risiko...")


df["risk_level"] = df.apply(
    risk_label,
    axis=1
)



# ==========================
# Ambil fitur AI
# ==========================


features = [

    "T2M",

    "RH2M",

    "PRECTOTCORR",

    "WS10M",

    "ALLSKY_SFC_SW_DWN",

    "brightness",

    "confidence",

    "frp",

    "latitude",

    "longitude",

    "risk_level"

]


# hanya ambil kolom yang tersedia

available_features = [

    col for col in features
    if col in df.columns

]


df = df[available_features]



# ==========================
# Simpan dataset AI
# ==========================


os.makedirs(
    "dataset/processed",
    exist_ok=True
)



df.to_csv(
    OUTPUT,
    index=False
)



print("==============================")
print("Feature Engineering selesai")
print("==============================")

print(
    df["risk_level"].value_counts()
)


print("==============================")
print("File:")
print(OUTPUT)
print("==============================")