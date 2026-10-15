from pathlib import Path
import shutil

source = Path("downloads/fire_smoke/smoke/data")
target = Path("dataset/yolo_fire_smoke")
splits = ["train", "val", "test"]

for split in splits:
    if not (source / split / "images").is_dir():
        raise FileNotFoundError(f"Folder gambar tidak ditemukan: {split}")
    if not (source / split / "labels").is_dir():
        raise FileNotFoundError(f"Folder label tidak ditemukan: {split}")

if target.exists():
    shutil.rmtree(target)

for split in splits:
    src_images = source / split / "images"
    src_labels = source / split / "labels"
    dst_images = target / "images" / split
    dst_labels = target / "labels" / split

    dst_images.mkdir(parents=True, exist_ok=True)
    dst_labels.mkdir(parents=True, exist_ok=True)

    for image in src_images.iterdir():
        if image.is_file():
            shutil.copy2(image, dst_images / image.name)

    for label in src_labels.glob("*.txt"):
        cleaned = []

        for line in label.read_text(encoding="utf-8").splitlines():
            parts = line.split()

            if len(parts) != 5:
                continue

            try:
                cls = int(parts[0])
                x, y, w, h = map(float, parts[1:])
            except ValueError:
                continue

            if cls not in (0, 1) or w <= 0 or h <= 0:
                continue

            if not all(0 <= v <= 1 for v in (x, y, w, h)):
                x1 = max(0.0, x - w / 2)
                y1 = max(0.0, y - h / 2)
                x2 = min(1.0, x + w / 2)
                y2 = min(1.0, y + h / 2)

                if x2 <= x1 or y2 <= y1:
                    continue

                x = (x1 + x2) / 2
                y = (y1 + y2) / 2
                w = x2 - x1
                h = y2 - y1

            x1 = x - w / 2
            y1 = y - h / 2
            x2 = x + w / 2
            y2 = y + h / 2

            if x1 < -1e-6 or y1 < -1e-6 or x2 > 1 + 1e-6 or y2 > 1 + 1e-6:
                continue

            cleaned.append(
                f"{cls} {x:.8f} {y:.8f} {w:.8f} {h:.8f}"
            )

        (dst_labels / label.name).write_text(
            "\n".join(cleaned),
            encoding="utf-8"
        )

yaml = """path: D:/laragon/www/SIGMA/ai_service/ai_service_sigma/dataset/yolo_fire_smoke
train: images/train
val: images/val
test: images/test
names:
  0: smoke
  1: fire
"""

(target / "data.yaml").write_text(yaml, encoding="utf-8")

print("Dataset disiapkan:", target.resolve())

for split in splits:
    images = [
        f for f in (target / "images" / split).iterdir()
        if f.is_file()
    ]
    labels = list((target / "labels" / split).glob("*.txt"))
    print(f"{split}: images={len(images)}, labels={len(labels)}")