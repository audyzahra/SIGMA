import pandas as pd
import os


INPUT = "dataset/processed"

OUTPUT = "dataset/processed/fire_risk_dataset.csv"



def preprocessing():


    print("Membaca dataset")


    weather = pd.read_csv(
        f"{INPUT}/weather.csv"
    )


    hotspot = pd.read_csv(
        f"{INPUT}/hotspot.csv"
    )


    fire = pd.read_csv(
        f"{INPUT}/fire_history.csv"
    )



    print("Menggabungkan data")


    dataset = pd.concat(
        [
            weather,
            hotspot,
            fire
        ],
        ignore_index=True
    )


    dataset.drop_duplicates(
        inplace=True
    )


    dataset.to_csv(
        OUTPUT,
        index=False
    )


    print("================")
    print("Preprocessing selesai")
    print(
        f"Jumlah data: {len(dataset)}"
    )

    print(
        f"File: {OUTPUT}"
    )



if __name__ == "__main__":

    preprocessing()