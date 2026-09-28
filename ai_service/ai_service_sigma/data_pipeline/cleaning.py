import pandas as pd
import os


INPUT_FOLDER = "dataset/processed"


def clean_dataset():


    for file in os.listdir(INPUT_FOLDER):

        if file.endswith(".csv"):

            path = os.path.join(
                INPUT_FOLDER,
                file
            )


            print(
                f"Cleaning {file}"
            )


            df = pd.read_csv(path)


            before = len(df)


            df.dropna(
                inplace=True
            )


            df.drop_duplicates(
                inplace=True
            )


            after = len(df)


            df.to_csv(
                path,
                index=False
            )


            print(
                f"{before} -> {after}"
            )



if __name__ == "__main__":

    clean_dataset()