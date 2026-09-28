import requests
import pandas as pd


# ======================
# Lokasi Indonesia
# ======================

latitude = -2.5489
longitude = 118.0149


# ======================
# Periode Data
# ======================

start_date = "20240101"
end_date = "20260924"



# ======================
# NASA POWER API
# ======================

url = (
    "https://power.larc.nasa.gov/api/temporal/daily/point?"
    "parameters=T2M,RH2M,PRECTOTCORR,WS10M,ALLSKY_SFC_SW_DWN"
    "&community=AG"
    f"&longitude={longitude}"
    f"&latitude={latitude}"
    f"&start={start_date}"
    f"&end={end_date}"
    "&format=JSON"
)


print("Mengambil data NASA POWER...")


response = requests.get(url)


if response.status_code != 200:
    print("API gagal")
    print(response.text)
    exit()



data = response.json()



weather = data["properties"]["parameter"]



df = pd.DataFrame(weather)



df.index.name = "date"

df.reset_index(inplace=True)



output = "dataset/raw/weather/weather_indonesia.csv"



df.to_csv(
    output,
    index=False
)



print("==============================")
print("Weather dataset berhasil")
print("File:", output)
print("Jumlah data:", len(df))