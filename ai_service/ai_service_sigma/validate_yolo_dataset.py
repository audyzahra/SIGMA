from pathlib import Path
from collections import Counter

root = Path("dataset/yolo_fire_smoke")
splits = ["train", "val", "test"]
extensions = {".jpg", ".jpeg", ".png", ".bmp", ".webp"}

total_images = 0
total_labels = 0
total_empty = 0
class_counts = Counter()
issues = []

if not (root / "data.yaml").is_file():
    issues.append("data.yaml tidak ditemukan")

for split in splits:
    images_dir = root / "images" / split
    labels_dir = root / "labels" / split

    if not images_dir.is_dir():
        issues.append(f"Folder gambar tidak ditemukan: {split}")
        continue

    if not labels_dir.is_dir():
        issues.append(f"Folder label tidak ditemukan: {split}")
        continue

    images = {
        file.stem: file
        for file in images_dir.iterdir()
        if file.is_file() and file.suffix.lower() in extensions
    }

    labels = {
        file.stem: file
        for file in labels_dir.glob("*.txt")
    }

    total_images += len(images)
    total_labels += len(labels)

    missing_labels = set(images) - set(labels)
    missing_images = set(labels) - set(images)

    for name in sorted(missing_labels):
        issues.append(f"{split}: label tidak ditemukan untuk {name}")

    for name in sorted(missing_images):
        issues.append(f"{split}: gambar tidak ditemukan untuk {name}")

    split_objects = Counter()
    split_empty = 0

    for label_file in labels_dir.glob("*.txt"):
        lines = label_file.read_text(encoding="utf-8").splitlines()

        if not lines:
            split_empty += 1
            total_empty += 1
            continue

        for line_no, line in enumerate(lines, 1):
            parts = line.split()

            if len(parts) != 5:
                issues.append(
                    f"{split}/{label_file.name}:{line_no} format salah"
                )
                continue

            try:
                cls = int(parts[0])
                x, y, w, h = map(float, parts[1:])
            except ValueError:
                issues.append(
                    f"{split}/{label_file.name}:{line_no} nilai tidak valid"
                )
                continue

            if cls not in (0, 1):
                issues.append(
                    f"{split}/{label_file.name}:{line_no} ID kelas tidak valid"
                )
                continue

            if not all(
                0 <= value <= 1
                for value in (x, y, w, h)
            ):
                issues.append(
                    f"{split}/{label_file.name}:{line_no} nilai di luar 0-1"
                )
                continue

            if (
                w <= 0
                or h <= 0
                or x - w / 2 < -1e-6
                or y - h / 2 < -1e-6
                or x + w / 2 > 1 + 1e-6
                or y + h / 2 > 1 + 1e-6
            ):
                issues.append(
                    f"{split}/{label_file.name}:{line_no} bounding box tidak valid"
                )
                continue

            split_objects[cls] += 1
            class_counts[cls] += 1

    print(
        f"{split}: images={len(images)}, labels={len(labels)}, "
        f"smoke={split_objects[0]}, fire={split_objects[1]}, "
        f"empty_labels={split_empty}"
    )

print("\nTOTAL GAMBAR:", total_images)
print("TOTAL FILE LABEL:", total_labels)
print("TOTAL ANOTASI SMOKE (0):", class_counts[0])
print("TOTAL ANOTASI FIRE (1):", class_counts[1])
print("TOTAL LABEL KOSONG:", total_empty)
print("TOTAL MASALAH:", len(issues))

for issue in issues[:50]:
    print("ERROR:", issue)

if len(issues) > 50:
    print(f"... {len(issues) - 50} masalah lainnya")

if not issues:
    print("\nVALIDASI LULUS")
else:
    print("\nVALIDASI BELUM LULUS")