
from pathlib import Path
from ultralytics import YOLO

base_dir = Path(__file__).resolve().parent
model_path = base_dir / "models" / "firedetect-11s.pt"
image_path = base_dir / "test2.jpeg"

if not model_path.is_file():
    raise FileNotFoundError(f"Model tidak ditemukan: {model_path}")

if not image_path.is_file():
    raise FileNotFoundError(f"Foto tidak ditemukan: {image_path}")

model = YOLO(str(model_path))

print("Model berhasil dimuat")
print("Nama kelas:", model.names)
print("Foto yang diuji:", image_path.resolve())

results = model.predict(
    source=str(image_path),
    imgsz=640,
    conf=0.10,
    device="cpu",
    save=True,
    project=str(base_dir / "runs" / "detect"),
    name="test_fire_smoke",
    exist_ok=True,
    verbose=False
)

for result in results:
    if result.boxes is None or len(result.boxes) == 0:
        print("Tidak ada objek terdeteksi")
    else:
        for box in result.boxes:
            class_id = int(box.cls.item())
            confidence = float(box.conf.item())
            print(
                f"Deteksi: {model.names[class_id]} | "
                f"Confidence: {confidence:.2%}"
            )

print("Hasil gambar tersimpan di:", base_dir / "runs" / "detect" / "test_fire_smoke")
