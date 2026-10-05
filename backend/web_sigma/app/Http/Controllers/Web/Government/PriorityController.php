<?php

namespace App\Http\Controllers\Web\Government;

use App\Http\Controllers\Controller;
use App\Http\Requests\Government\PriorityFilterRequest;
use App\Models\PriorityResult;
use App\Services\AI\AIRecommendationService;
use App\Services\PriorityCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * Prioritas Penanganan (Government).
 *
 * Halaman decision support yang menampilkan ranking wilayah menurut tingkat
 * risiko karhutla dan dampak yang ditimbulkan.
 *
 * Controller ini hanya:
 *   1. menerima request   (validasi filter lewat PriorityFilterRequest)
 *   2. memanggil service  (PriorityCalculationService / AIRecommendationService)
 *   3. mengirim data ke view
 *
 * Seluruh perhitungan skor, kategori prioritas, ranking, filter, dan
 * pagination dikerjakan di service + database. Tidak ada angka analisis di
 * controller, view, maupun JavaScript.
 */
class PriorityController extends Controller
{
    public function __construct(
        protected PriorityCalculationService $priority,
        protected AIRecommendationService $ai
    ) {
    }

    /**
     * Halaman utama: ringkasan, filter, dan tabel ranking.
     */
    public function index(PriorityFilterRequest $request): View
    {
        $filters = $request->filters();

        return view('government.priority', [
            'summary' => $this->priority->summary(),
            'filters' => $filters,
            'options' => $this->priority->filterOptions($filters),
            'priorities' => $this->priority->ranking($filters),

            /* transparansi sumber data & metodologi */
            'dataSources' => $this->priority->dataSources(),
            'methodologyNotes' => $this->priority->methodologyNotes(),
            'methodologyVersion' => $this->priority->methodologyVersion(),
            'weights' => $this->priority->weights(),
        ]);
    }

    /**
     * Detail prioritas satu wilayah.
     */
    public function show(PriorityResult $priority): View
    {
        $detail = $this->priority->detail($priority);

        $aiInput = $this->ai->payload($detail);

        return view('government.priority-detail', [
            'detail' => $detail,
            'aiInput' => $aiInput,
            'aiOutput' => $this->ai->recommend($aiInput),
        ]);
    }

    /**
     * Jalankan perhitungan ulang prioritas ("Hitung Ulang Prioritas").
     */
    public function recalculate(PriorityFilterRequest $request): RedirectResponse
    {
        $result = $this->priority->recalculate();

        return redirect()
            ->route('government.priority', $request->redirectParameters())
            ->with('success', $result['message']);
    }

    /**
     * Data satu wilayah dalam bentuk JSON.
     *
     * Dipakai bila AI Service atau proses GIS lain membutuhkan data prioritas
     * satu wilayah tanpa membuka halaman detail.
     */
    public function payload(PriorityResult $priority): JsonResponse
    {
        $detail = $this->priority->detail($priority);

        $aiInput = $this->ai->payload($detail);

        return response()->json([
            'success' => true,
            'data' => Arr::except($detail, ['result']),
            'ai' => [
                'input' => $aiInput,
                'output' => $this->ai->recommend($aiInput),
            ],
        ]);
    }
}
