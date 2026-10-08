"""
SIGMA AI - Recommendation Router

Endpoint rekomendasi tindakan pemerintah berdasarkan:
- hasil analisis risiko
- hasil analisis dampak
- prioritas penanganan

Gemini hanya memberikan rekomendasi tindakan.
Gemini tidak menghitung ulang risk, impact, atau priority.
"""

import json
import logging
import os
import time

import httpx
from fastapi import APIRouter, HTTPException
from pydantic import BaseModel, Field


logger = logging.getLogger(__name__)

router = APIRouter()


# ============================================================
# GEMINI COOLDOWN
# ============================================================

COOLDOWN_SECONDS = 300
cooldown_until = 0.0


# ============================================================
# GEMINI CONFIG
# ============================================================

GEMINI_API_KEY = os.getenv(
    "GEMINI_API_KEY",
    "",
).strip()

GEMINI_MODEL = os.getenv(
    "GEMINI_MODEL",
    "gemini-2.5-flash",
).strip()

GEMINI_URL = (
    "https://generativelanguage.googleapis.com/v1beta/"
    f"models/{GEMINI_MODEL}:generateContent"
)


# ============================================================
# PYDANTIC MODELS
# ============================================================

class RegionData(BaseModel):
    id: int | None = None
    name: str | None = None
    code: str | None = None
    level: str | None = None
    parent_name: str | None = None


class RiskData(BaseModel):
    score: float | None = None
    level: str | None = None
    weather: dict | None = None


class ImpactData(BaseModel):
    score: float | None = None
    population: dict | None = None
    land_cover: dict | None = None


class PriorityData(BaseModel):
    score: float | None = None
    level: str | None = None
    ranking_position: int | None = None


class RecommendationInput(BaseModel):
    region: RegionData
    risk: RiskData
    impact: ImpactData
    priority: PriorityData
    sources: list[dict] = Field(
        default_factory=list,
    )


# ============================================================
# PROMPT
# ============================================================

def build_prompt(
    data: RecommendationInput,
) -> str:

    payload = data.model_dump()

    return f"""
Anda adalah AI pendukung pengambilan keputusan pemerintah
untuk penanganan risiko kebakaran hutan dan lahan (karhutla)
dalam sistem SIGMA.

Gunakan DATA YANG DIBERIKAN sebagai dasar rekomendasi.

PENTING:

1. Jangan menghitung ulang risk score.
2. Jangan menghitung ulang impact score.
3. Jangan menghitung ulang priority score.
4. Jangan mengubah priority level.
5. Jangan membuat data faktual yang tidak diberikan.
6. Jangan melakukan dispatch petugas secara otomatis.
7. Rekomendasi harus berupa tindakan pemerintah yang dapat
   dilakukan oleh operator/pengambil keputusan.
8. Prioritaskan keselamatan masyarakat dan pengendalian risiko.
9. Jika data tidak tersedia, jangan mengarang nilainya.
10. Gunakan bahasa Indonesia yang jelas dan formal.
11. Jika impact score belum tersedia, jangan menganggap
    nilainya nol. Nyatakan bahwa data dampak belum tersedia
    dan berikan rekomendasi berdasarkan data yang tersedia.
12. Jika terdapat sumber laporan masyarakat dalam data,
    gunakan laporan tersebut sebagai konteks pendukung,
    tetapi jangan menganggap laporan belum terverifikasi
    sebagai fakta pasti.
13. Jangan membuat nama lokasi, angka, kondisi cuaca,
    jumlah penduduk, hotspot, atau fakta lain yang tidak
    diberikan dalam DATA SIGMA.

DATA SIGMA:

{json.dumps(
    payload,
    ensure_ascii=False,
    indent=2,
)}

Kembalikan HANYA JSON dengan struktur:

{{
    "recommendation": "ringkasan rekomendasi utama",
    "priority_action": "tindakan yang paling mendesak",
    "actions": [
        {{
            "type": "monitoring|field_response|public_safety|coordination|resource",
            "title": "judul tindakan",
            "detail": "penjelasan tindakan",
            "priority": "critical|high|medium|low"
        }}
    ],
    "reasoning": "alasan singkat mengapa tindakan tersebut diprioritaskan"
}}

Berikan maksimal 4 tindakan.
"""


# ============================================================
# GEMINI REQUEST
# ============================================================

def request_gemini(
    prompt: str,
) -> dict:

    global cooldown_until

    # --------------------------------------------------------
    # CHECK API KEY
    # --------------------------------------------------------

    if not GEMINI_API_KEY:
        raise HTTPException(
            status_code=503,
            detail="GEMINI_API_KEY belum dikonfigurasi.",
        )

    # --------------------------------------------------------
    # CHECK COOLDOWN
    # --------------------------------------------------------

    now = time.time()

    if now < cooldown_until:

        remaining = int(
            cooldown_until - now
        )

        logger.warning(
            "Gemini cooldown aktif. Sisa waktu=%s detik.",
            remaining,
        )

        raise HTTPException(
            status_code=429,
            detail={
                "code": "cooldown_active",
                "provider": "gemini",
                "model": GEMINI_MODEL,
                "message": (
                    "Gemini sedang dalam cooldown. "
                    f"Coba lagi dalam {remaining} detik."
                ),
                "remaining_seconds": remaining,
            },
        )

    # --------------------------------------------------------
    # HEADERS
    # --------------------------------------------------------

    headers = {
        "Content-Type": "application/json",
        "x-goog-api-key": GEMINI_API_KEY,
    }

    # --------------------------------------------------------
    # REQUEST BODY
    # --------------------------------------------------------

    body = {
        "contents": [
            {
                "parts": [
                    {
                        "text": prompt,
                    }
                ]
            }
        ],
        "generationConfig": {
            "responseMimeType": "application/json",
            "thinkingConfig": {
                "thinkingLevel": "low",
            },
            "maxOutputTokens": 500,
        },
    }

    # --------------------------------------------------------
    # RETRY CONFIG
    # --------------------------------------------------------

    max_attempts = 2

    retryable_statuses = {
        500,
        502,
        503,
        504,
    }

    backoff_seconds = [
        2,
    ]

    # --------------------------------------------------------
    # TIMEOUT
    # --------------------------------------------------------

    timeout = httpx.Timeout(
        connect=10.0,
        read=30.0,
        write=10.0,
        pool=10.0,
    )

    # --------------------------------------------------------
    # HTTP CLIENT
    # --------------------------------------------------------

    with httpx.Client(
        timeout=timeout,
    ) as client:

        for attempt in range(
            1,
            max_attempts + 1,
        ):

            try:

                logger.info(
                    "Memanggil Gemini recommendation. "
                    "model=%s attempt=%s/%s",
                    GEMINI_MODEL,
                    attempt,
                    max_attempts,
                )

                response = client.post(
                    GEMINI_URL,
                    headers=headers,
                    json=body,
                )

            # ------------------------------------------------
            # READ TIMEOUT
            # ------------------------------------------------

            except httpx.ReadTimeout:

                logger.warning(
                    "Gemini read timeout. "
                    "attempt=%s/%s",
                    attempt,
                    max_attempts,
                )

                if attempt < max_attempts:

                    delay = backoff_seconds[
                        attempt - 1
                    ]

                    logger.warning(
                        "Retry Gemini dalam %s detik.",
                        delay,
                    )

                    time.sleep(delay)

                    continue

                cooldown_until = (
                    time.time()
                    + COOLDOWN_SECONDS
                )

                logger.warning(
                    "Gemini cooldown dimulai selama %s detik.",
                    COOLDOWN_SECONDS,
                )

                raise HTTPException(
                    status_code=503,
                    detail=(
                        "Gemini tidak memberikan response "
                        "dalam batas waktu setelah "
                        f"{max_attempts} percobaan."
                    ),
                )

            # ------------------------------------------------
            # CONNECT TIMEOUT
            # ------------------------------------------------

            except httpx.ConnectTimeout:

                logger.warning(
                    "Gemini connection timeout. "
                    "attempt=%s/%s",
                    attempt,
                    max_attempts,
                )

                if attempt < max_attempts:

                    delay = backoff_seconds[
                        attempt - 1
                    ]

                    logger.warning(
                        "Retry Gemini dalam %s detik.",
                        delay,
                    )

                    time.sleep(delay)

                    continue

                cooldown_until = (
                    time.time()
                    + COOLDOWN_SECONDS
                )

                logger.warning(
                    "Gemini cooldown dimulai selama %s detik.",
                    COOLDOWN_SECONDS,
                )

                raise HTTPException(
                    status_code=503,
                    detail=(
                        "Koneksi ke Gemini timeout "
                        f"setelah {max_attempts} percobaan."
                    ),
                )

            # ------------------------------------------------
            # REQUEST ERROR
            # ------------------------------------------------

            except httpx.RequestError as error:

                logger.warning(
                    "Gagal menghubungi Gemini. "
                    "attempt=%s/%s error=%s",
                    attempt,
                    max_attempts,
                    str(error),
                )

                if attempt < max_attempts:

                    delay = backoff_seconds[
                        attempt - 1
                    ]

                    logger.warning(
                        "Retry Gemini dalam %s detik.",
                        delay,
                    )

                    time.sleep(delay)

                    continue

                cooldown_until = (
                    time.time()
                    + COOLDOWN_SECONDS
                )

                logger.warning(
                    "Gemini cooldown dimulai selama %s detik.",
                    COOLDOWN_SECONDS,
                )

                raise HTTPException(
                    status_code=503,
                    detail=(
                        "Tidak dapat terhubung ke Gemini "
                        "setelah beberapa percobaan."
                    ),
                ) from error

            # ------------------------------------------------
            # 429 RATE LIMIT
            # ------------------------------------------------

            if response.status_code == 429:

                try:

                    error_data = response.json()

                    error_message = (
                        error_data
                        .get("error", {})
                        .get(
                            "message",
                            response.text,
                        )
                    )

                    error_status = (
                        error_data
                        .get("error", {})
                        .get(
                            "status",
                            "RESOURCE_EXHAUSTED",
                        )
                    )

                except Exception:

                    error_message = response.text
                    error_status = "RESOURCE_EXHAUSTED"

                logger.warning(
                    "Gemini quota/rate limit exceeded. "
                    "model=%s message=%s",
                    GEMINI_MODEL,
                    error_message,
                )

                raise HTTPException(
                    status_code=429,
                    detail={
                        "code": "quota_exceeded",
                        "provider": "gemini",
                        "model": GEMINI_MODEL,
                        "status": error_status,
                        "message": error_message,
                    },
                )

            # ------------------------------------------------
            # 200 SUCCESS
            # ------------------------------------------------

            if response.status_code == 200:

                try:

                    response_data = response.json()

                    candidates = (
                        response_data.get(
                            "candidates",
                            [],
                        )
                    )

                    if not candidates:

                        raise HTTPException(
                            status_code=502,
                            detail=(
                                "Gemini tidak "
                                "mengembalikan kandidat jawaban."
                            ),
                        )

                    parts = (
                        candidates[0]
                        .get("content", {})
                        .get("parts", [])
                    )

                    if not parts:

                        raise HTTPException(
                            status_code=502,
                            detail=(
                                "Gemini tidak "
                                "mengembalikan isi jawaban."
                            ),
                        )

                    # ----------------------------------------
                    # AMBIL TEXT DARI GEMINI
                    # ----------------------------------------

                    text = (
                        parts[0]
                        .get("text", "")
                        .strip()
                    )

                    if not text:

                        raise HTTPException(
                            status_code=502,
                            detail=(
                                "Gemini mengembalikan "
                                "jawaban kosong."
                            ),
                        )

                    # ----------------------------------------
                    # LOG RAW RESPONSE
                    # ----------------------------------------

                    logger.info(
                        "RAW GEMINI RESPONSE: %s",
                        text[:5000],
                    )

                    # ----------------------------------------
                    # CLEAN RESPONSE
                    # ----------------------------------------

                    cleaned_text = text.strip()

                    # Hapus markdown code fence:
                    #
                    # ```json
                    # {
                    #   ...
                    # }
                    # ```
                    #
                    if cleaned_text.startswith("```"):

                        lines = (
                            cleaned_text
                            .splitlines()
                        )

                        if (
                            lines
                            and lines[0]
                            .strip()
                            .startswith("```")
                        ):
                            lines = lines[1:]

                        if (
                            lines
                            and lines[-1]
                            .strip()
                            == "```"
                        ):
                            lines = lines[:-1]

                        cleaned_text = (
                            "\n".join(lines)
                            .strip()
                        )

                    # ----------------------------------------
                    # CARI OBJECT JSON
                    # ----------------------------------------

                    if not cleaned_text.startswith("{"):

                        json_start = (
                            cleaned_text.find("{")
                        )

                        if json_start != -1:

                            cleaned_text = (
                                cleaned_text[
                                    json_start:
                                ]
                            )

                    if not cleaned_text.endswith("}"):

                        json_end = (
                            cleaned_text.rfind("}")
                        )

                        if json_end != -1:

                            cleaned_text = (
                                cleaned_text[
                                    :json_end + 1
                                ]
                            )

                    # ----------------------------------------
                    # PARSE JSON
                    # ----------------------------------------

                    try:

                        result = json.loads(
                            cleaned_text
                        )

                    except json.JSONDecodeError as error:

                        logger.warning(
                            "Response Gemini bukan "
                            "JSON valid."
                        )

                        logger.warning(
                            "RAW RESPONSE GEMINI: %s",
                            text[:5000],
                        )

                        logger.warning(
                            "CLEANED RESPONSE GEMINI: %s",
                            cleaned_text[:5000],
                        )

                        raise HTTPException(
                            status_code=502,
                            detail={
                                "code": "invalid_gemini_json",
                                "message": (
                                    "Gemini mengembalikan "
                                    "response yang bukan "
                                    "JSON valid."
                                ),
                                "raw_response": text[:1000],
                            },
                        ) from error

                    # ----------------------------------------
                    # PASTIKAN OBJECT
                    # ----------------------------------------

                    if not isinstance(
                        result,
                        dict,
                    ):

                        raise HTTPException(
                            status_code=502,
                            detail=(
                                "Format response Gemini "
                                "bukan JSON object."
                            ),
                        )

                    # ----------------------------------------
                    # RETURN RESULT
                    # ----------------------------------------

                    return result

                except json.JSONDecodeError as error:

                    logger.warning(
                        "Response Gemini bukan JSON valid."
                    )

                    raise HTTPException(
                        status_code=502,
                        detail=(
                            "Response Gemini bukan "
                            "JSON yang valid."
                        ),
                    ) from error

            # ------------------------------------------------
            # TEMPORARY ERROR
            # ------------------------------------------------

            if response.status_code in retryable_statuses:

                logger.warning(
                    "Gemini temporary error. "
                    "status=%s attempt=%s/%s body=%s",
                    response.status_code,
                    attempt,
                    max_attempts,
                    response.text[:1000],
                )

                if attempt < max_attempts:

                    retry_after_header = (
                        response.headers.get(
                            "retry-after"
                        )
                    )

                    if retry_after_header:

                        try:

                            delay = float(
                                retry_after_header
                            )

                        except ValueError:

                            delay = (
                                backoff_seconds[
                                    attempt - 1
                                ]
                            )

                    else:

                        delay = (
                            backoff_seconds[
                                attempt - 1
                            ]
                        )

                    logger.warning(
                        "Menunggu %.1f detik sebelum "
                        "retry Gemini.",
                        delay,
                    )

                    time.sleep(delay)

                    continue

                # --------------------------------------------
                # COOLDOWN SETELAH RETRY TERAKHIR GAGAL
                # --------------------------------------------

                cooldown_until = (
                    time.time()
                    + COOLDOWN_SECONDS
                )

                logger.warning(
                    "Gemini cooldown dimulai selama %s detik.",
                    COOLDOWN_SECONDS,
                )

                try:

                    error_data = response.json()

                    error_message = (
                        error_data
                        .get("error", {})
                        .get(
                            "message",
                            "Unknown Gemini error",
                        )
                    )

                except Exception:

                    error_message = response.text

                raise HTTPException(
                    status_code=503,
                    detail=(
                        "Gemini sementara tidak tersedia "
                        f"({response.status_code}): "
                        f"{error_message}"
                    ),
                )

            # ------------------------------------------------
            # NON RETRYABLE ERROR
            # ------------------------------------------------

            logger.error(
                "Gemini non-retryable error "
                "%s: %s",
                response.status_code,
                response.text,
            )

            try:

                error_data = response.json()

                error_message = (
                    error_data
                    .get("error", {})
                    .get(
                        "message",
                        "Unknown Gemini error",
                    )
                )

            except Exception:

                error_message = response.text

            raise HTTPException(
                status_code=502,
                detail=(
                    f"Gemini error "
                    f"{response.status_code}: "
                    f"{error_message}"
                ),
            )

    # --------------------------------------------------------
    # FALLBACK
    # --------------------------------------------------------

    raise HTTPException(
        status_code=503,
        detail=(
            "Gemini sedang tidak tersedia "
            "setelah beberapa percobaan."
        ),
    )


# ============================================================
# ENDPOINT
# ============================================================

@router.post("/recommend-action")
def recommend_action(
    data: RecommendationInput,
):

    logger.info(
        "Recommendation request diterima. "
        "region=%s priority=%s score=%s",
        data.region.name,
        data.priority.level,
        data.priority.score,
    )

    prompt = build_prompt(data)

    result = request_gemini(prompt)

    return {
        "available": True,
        "status": "success",
        "provider": "gemini",
        "model": GEMINI_MODEL,
        "recommendation": result.get(
            "recommendation"
        ),
        "priority_action": result.get(
            "priority_action"
        ),
        "actions": result.get(
            "actions",
            [],
        ),
        "reasoning": result.get(
            "reasoning"
        ),
    }