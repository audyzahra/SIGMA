import rasterio
import pandas as pd
import os


# ===============================
# PATH DATASET
# ===============================

INPUT_FOLDER = "dataset/raw/land"

OUTPUT_FOLDER = "dataset/processed"

OUTPUT_FILE = "dataset/processed/land_cover.csv"


# ===============================
# LAND PROCESSING
# ===============================

def process_land():

    records = []


    # cek folder input

    if not os.path.exists(INPUT_FOLDER):
        raise FileNotFoundError(
            f"Folder tidak ditemukan: {INPUT_FOLDER}"
        )


    # baca semua file tif

    for file in os.listdir(INPUT_FOLDER):

        if file.endswith(".tif"):

            path = os.path.join(
                INPUT_FOLDER,
                file
            )


            print(f"Membaca {file}")


            with rasterio.open(path) as src:


                # baca raster band 1
                band = src.read(1)


                transform = src.transform


                height, width = band.shape



                # sampling pixel setiap 50 pixel
                # agar dataset tidak terlalu besar

                for row in range(0, height, 50):

                    for col in range(0, width, 50):


                        land_class = band[row, col]


                        # skip no data

                        if land_class == 0:
                            continue



                        longitude, latitude = rasterio.transform.xy(
                            transform,
                            row,
                            col
                        )


                        records.append({

                            "latitude": latitude,

                            "longitude": longitude,

                            "land_class": int(land_class),

                            "source": file

                        })



    # ubah ke dataframe

    df = pd.DataFrame(records)



    # buat folder output

    os.makedirs(
        OUTPUT_FOLDER,
        exist_ok=True
    )



    # simpan csv

    df.to_csv(
        OUTPUT_FILE,
        index=False
    )



    print("==============================")
    print("Land dataset berhasil")
    print(f"Jumlah data : {len(df)}")
    print(f"File : {OUTPUT_FILE}")
    print("==============================")



# ===============================
# RUN
# ===============================

if __name__ == "__main__":

    process_land()