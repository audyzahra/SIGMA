from pathlib import Path

root = Path("downloads/fire_smoke/smoke/data")
files = [
    "WEB10769.txt",
    "WEB10770.txt",
    "WEB10775.txt",
    "WEB10821.txt",
    "WEB11090.txt",
    "WEB11598.txt",
    "WEB11600.txt",
    "WEB11606.txt",
]

for split in ["train", "val", "test"]:
    labels_dir = root / split / "labels"

    for name in files:
        path = labels_dir / name

        if path.exists():
            print(f"\n{split}/{name}")
            for line_no, line in enumerate(
                path.read_text(encoding="utf-8").splitlines(), 1
            ):
                print(f"{line_no}: {line}")