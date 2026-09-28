import rasterio


file = "dataset/raw/land/ESA_WorldCover_10m_2021_v200_N00E120_Map.tif"


with rasterio.open(file) as src:

    print("Driver:", src.driver)
    print("CRS:", src.crs)
    print("Ukuran:", src.width, "x", src.height)
    print("Band:", src.count)
    print("Bounds:")
    print(src.bounds)