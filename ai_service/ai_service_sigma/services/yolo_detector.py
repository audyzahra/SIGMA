import base64
from io import BytesIO
from pathlib import Path

from PIL import Image
from ultralytics import YOLO

BASE_DIR = Path(__file__).resolve().parents[1]
MODEL_PATH = BASE_DIR / "models" / "firedetect-11s.pt"

if not MODEL_PATH.is_file():
    raise FileNotFoundError(f"Model tidak ditemukan: {MODEL_PATH}")

model = YOLO(str(MODEL_PATH))


def detect_fire_smoke(image_bytes: bytes) -> dict:
    image = Image.open(BytesIO(image_bytes)).convert("RGB")

    results = model.predict(
        source=image,
        imgsz=640,
        conf=0.25,
        device="cpu",
        save=False,
        verbose=False
    )

    detections = []

    for result in results:
        if result.boxes is None:
            continue

        for box in result.boxes:
            class_id = int(box.cls.item())
            confidence = float(box.conf.item())
            coordinates = box.xyxy[0].tolist()

            detections.append({
                "class_id": class_id,
                "class_name": model.names[class_id],
                "confidence": round(confidence, 4),
                "bbox": [round(float(value), 2) for value in coordinates]
            })

    annotated_image_base64 = None

    if results:
        annotated_image = results[0].plot()
        annotated_image_rgb = Image.fromarray(annotated_image[:, :, ::-1])
        output = BytesIO()
        annotated_image_rgb.save(output, format="JPEG", quality=90)
        annotated_image_base64 = base64.b64encode(
            output.getvalue()
        ).decode("utf-8")

    return {
        "model": MODEL_PATH.name,
        "detections": detections,
        "fire_detected": any(
            item["class_name"].lower() == "fire"
            for item in detections
        ),
        "smoke_detected": any(
            item["class_name"].lower() == "smoke"
            for item in detections
        ),
        "annotated_image_base64": annotated_image_base64
    }