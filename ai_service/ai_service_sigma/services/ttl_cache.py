"""
TTL cache sederhana (in-memory, per proses).

Dipakai supaya NASA POWER / NASA FIRMS tidak dipanggil setiap kali
user klik wilayah. Cache SELALU punya TTL (NASA_CACHE_MINUTES).

Catatan: cache ini per proses uvicorn. Jika service dijalankan dengan
beberapa worker, setiap worker punya cache sendiri (masih aman, hanya
kurang optimal).
"""

import threading
import time

from typing import Any, Callable, Tuple


_lock = threading.Lock()

_store: dict[str, dict] = {}


def get(key: str) -> Any | None:
    """Ambil nilai cache; None jika tidak ada / sudah kedaluwarsa."""

    with _lock:
        entry = _store.get(key)

        if not entry:
            return None

        if entry["expires_at"] < time.time():
            _store.pop(key, None)
            return None

        return entry["value"]


def set(key: str, value: Any, ttl_seconds: float) -> None:
    """Simpan nilai cache dengan TTL."""

    with _lock:
        _store[key] = {
            "value": value,
            "expires_at": time.time() + max(float(ttl_seconds), 1.0),
            "stored_at": time.time(),
        }


def get_or_set(
    key: str,
    ttl_seconds: float,
    producer: Callable[[], Any],
) -> Tuple[Any, bool]:
    """
    Ambil dari cache, atau jalankan producer() lalu simpan.

    Return: (value, cached)
    """

    cached_value = get(key)

    if cached_value is not None:
        return cached_value, True

    value = producer()

    if value is not None:
        set(key, value, ttl_seconds)

    return value, False


def ttl_seconds(minutes: float) -> float:
    return max(float(minutes), 1.0) * 60.0


def clear() -> None:
    with _lock:
        _store.clear()


def stats() -> dict:
    """Informasi ringkas isi cache (untuk debugging)."""

    with _lock:
        return {
            "entries": len(_store),
            "keys": list(_store.keys())[:20],
        }
