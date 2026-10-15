from ultralytics import YOLO

model = YOLO("yolo11n.pt")
results = model("tests/images/test.jpg")

for result in results:
    for box in result.boxes:
        class_id = int(box.cls[0])
        confidence = float(box.conf[0])
        class_name = result.names[class_id]

        print({
            "class": class_name,
            "confidence": round(confidence, 4),
        })