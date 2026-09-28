import pandas as pd
import os


RAW_PATH = "dataset/raw"
OUTPUT_PATH = "dataset/processed"


def collect_csv():

    os.makedirs(
        OUTPUT_PATH,
        exist_ok=True
    )


    files = {

        "hotspot":
        f"{RAW_PATH}/hotspot/hotspot_indonesia.csv",

        "fire_history":
        f"{RAW_PATH}/fire_history/fire_history_indonesia.csv",

        "weather":
        f"{RAW_PATH}/weather/weather_indonesia.csv"

    }


    for name, path in files.items():

        if os.path.exists(path):

            print(f"Membaca {name}")

            df = pd.read_csv(path)


            output = (
                f"{OUTPUT_PATH}/{name}.csv"
            )


            df.to_csv(
                output,
                index=False
            )


            print(
                f"Tersimpan: {output}"
            )


        else:

            print(
                f"Tidak ditemukan {path}"
            )



if __name__ == "__main__":

    collect_csv()