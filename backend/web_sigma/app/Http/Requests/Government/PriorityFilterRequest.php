<?php

namespace App\Http\Requests\Government;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi filter halaman Prioritas Penanganan.
 *
 * Dipakai oleh GET /government/priority (filter tabel ranking) dan
 * POST /government/priority/recalculate (filter yang dipertahankan setelah
 * perhitungan ulang).
 *
 * Hal yang divalidasi:
 *  - search      : kata kunci nama wilayah
 *  - level       : kategori prioritas (mengikuti config, bukan daftar manual)
 *  - province_id : wilayah provinsi yang dipilih
 *  - regency_id  : wilayah kabupaten/kota yang dipilih
 *  - page        : halaman tabel ranking
 */
class PriorityFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $perPage = (int) config('sigma_priority.per_page_max', 100);

        return [
            'search' => ['nullable', 'string', 'max:100'],
            'level' => ['nullable', 'string', Rule::in(array_keys((array) config('sigma_priority.levels', [])))],
            'province_id' => ['nullable', 'integer', 'exists:regions,id'],
            'regency_id' => ['nullable', 'integer', 'exists:regions,id'],
            'page' => ['nullable', 'integer', 'min:1', 'max:' . max($perPage * 1000, 1000)],
        ];
    }

    /**
     * Nama atribut pada pesan kesalahan.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'search' => 'kata kunci wilayah',
            'level' => 'tingkat prioritas',
            'province_id' => 'provinsi',
            'regency_id' => 'kabupaten/kota',
            'page' => 'halaman',
        ];
    }

    /**
     * Filter yang sudah ternormalisasi (selalu punya kunci yang sama).
     *
     * @return array{search: string|null, level: string|null, province_id: int|null, regency_id: int|null, page: int|null}
     */
    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'search' => isset($validated['search']) && trim((string) $validated['search']) !== ''
                ? trim((string) $validated['search'])
                : null,

            'level' => $validated['level'] ?? null,

            'province_id' => isset($validated['province_id']) ? (int) $validated['province_id'] : null,

            'regency_id' => isset($validated['regency_id']) ? (int) $validated['regency_id'] : null,

            'page' => isset($validated['page']) ? (int) $validated['page'] : null,
        ];
    }

    /**
     * Filter yang dipakai sebagai query string saat mengalihkan halaman.
     *
     * @return array<string, mixed>
     */
    public function redirectParameters(): array
    {
        return array_filter(
            $this->filters(),
            fn ($value) => $value !== null && $value !== ''
        );
    }
}
