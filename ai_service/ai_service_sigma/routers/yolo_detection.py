from io import BytesIO

from fastapi import APIRouter, File, HTTPException, UploadFile
from PIL import Image, UnidentifiedImageError

from services.yolo_detector import detect_fire_smoke

router = APIRouter(
    prefix="/detect",
    tags=["YOLO Fire and Smoke"]
)


@router.post("/fire-smoke")
async def detect_fire_smoke_endpoint(
    file: UploadFile = File(...)
):
    if not file.content_type or not file.content_type.startswith("image/"):
        raise HTTPException(
            status_code=400,
            detail="File harus berupa gambar."
        )

    image_bytes = await file.read()

    if not image_bytes:
        raise HTTPException(
            status_code=400,
            detail="File gambar kosong."
        )

    if len(image_bytes) > 10 * 1024 * 1024:
        raise HTTPException(
            status_code=413,
            detail="Ukuran gambar maksimal 10 MB."
        )

    try:
        with Image.open(BytesIO(image_bytes)) as image:
            image.verify()

        result = detect_fire_smoke(image_bytes)

        return {
            "success": True,
            "message": "Deteksi api dan asap selesai.",
            "data": result
        }

    except (UnidentifiedImageError, OSError):
        raise HTTPException(
            status_code=400,
            detail="File gambar tidak valid atau tidak dapat dibaca."
        )