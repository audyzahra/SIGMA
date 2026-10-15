<?php

namespace App\Http\Controllers\Web\Government;

use App\Http\Controllers\Controller;
use App\Models\EarlyWarning;
use App\Models\Incident;
use App\Models\Region;
use App\Services\EarlyWarningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class EarlyWarningController extends Controller
{
    public function feed(): JsonResponse
    {
        $warnings = EarlyWarning::query()
            ->with(['region:id,name,parent_id,level', 'region.parent:id,name,parent_id,level', 'region.parent.parent:id,name,level', 'histories.actor:id,name'])
            ->latest('issued_at')
            ->paginate(20)
            ->getCollection()
            ->map(fn (EarlyWarning $warning): array => [
                'id' => $warning->id,
                'incident_id' => $warning->incident_id,
                'level' => $warning->level,
                'status' => $warning->status === 'active' && $warning->expires_at?->isPast() ? 'expired' : $warning->status,
                'area' => $warning->area,
                'region' => $warning->region?->name,
                'region_id' => $warning->region_id,
                'region_level' => $warning->region?->level,
                'regency_id' => $warning->region?->level === 'district' ? $warning->region?->parent_id : ($warning->region?->level === 'regency' ? $warning->region_id : null),
                'province_id' => $warning->region?->level === 'province' ? $warning->region_id : $warning->region?->parent?->parent_id,
                'regency' => $warning->region?->level === 'district' ? $warning->region?->parent?->name : ($warning->region?->level === 'regency' ? $warning->region?->name : null),
                'province' => $warning->region?->level === 'province' ? $warning->region?->name : $warning->region?->parent?->parent?->name,
                'source' => $warning->source,
                'reason' => $warning->reason,
                'instructions' => $warning->instructions,
                'expires_at' => $warning->expires_at?->toIso8601String(),
                'expires_at_input' => $warning->expires_at?->format('Y-m-d\\TH:i'),
                'issued_at' => $warning->issued_at?->toIso8601String(),
                'histories' => $warning->histories->map(fn ($history): array => [
                    'action' => $history->action,
                    'actor' => $history->actor?->name,
                    'created_at' => $history->created_at?->toIso8601String(),
                ])->values(),
            ])->values();

        return response()->json([
            'warnings' => $warnings,
            'active_count' => EarlyWarning::query()->where('status', 'active')->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))->count(),
            'high_count' => EarlyWarning::query()->whereIn('level', ['warning', 'critical'])->where('status', 'active')->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))->count(),
            'expiring_count' => EarlyWarning::query()->where('status', 'active')->whereBetween('expires_at', [now(), now()->addHours(6)])->count(),
            'closed_count' => EarlyWarning::query()->where('status', 'closed')->count(),
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    public function index(): View
    {
        return view('government.early-warnings.index', [
            'warnings' => EarlyWarning::query()->with(['region:id,name,parent_id,level', 'region.parent:id,name,parent_id,level', 'region.parent.parent:id,name,level', 'incident:id,location_description', 'histories.actor:id,name'])->latest('issued_at')->paginate(20),
            'activeCount' => EarlyWarning::query()->where('status', 'active')->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))->count(),
            'closedCount' => EarlyWarning::query()->where('status', 'closed')->count(),
            'highCount' => EarlyWarning::query()->whereIn('level', ['warning', 'critical'])->where('status', 'active')->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))->count(),
            'expiringCount' => EarlyWarning::query()->where('status', 'active')->whereBetween('expires_at', [now(), now()->addHours(6)])->count(),
            'incidents' => Incident::query()
                ->whereNotIn('fire_status', ['extinguished', 'resolved', 'closed'])
                ->where(function ($query): void {
                    $query->where('source_type', '!=', 'report')
                        ->orWhereHas('citizenReports', fn ($reports) => $reports->where('verification_status', 'verified'));
                })
                ->with(['citizenReports' => fn ($reports) => $reports->where('verification_status', 'verified')->select(['id', 'incident_id', 'report_type'])])
                ->latest('detected_at')
                ->limit(100)
                ->get(['id', 'source_type', 'location_description', 'detected_at']),
            'provinces' => Region::query()->where('level', 'province')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function searchRegions(Request $request): JsonResponse
    {
        $data = $request->validate([
            'parent_id' => ['required', 'integer', 'exists:regions,id'],
            'level' => ['required', 'in:regency,district'],
        ]);
        $parent = Region::query()->findOrFail($data['parent_id']);
        $expectedParentLevel = $data['level'] === 'regency' ? 'province' : 'regency';
        abort_unless($parent->level === $expectedParentLevel, 422, 'Tingkatan wilayah tidak sesuai.');

        return response()->json(['regions' => Region::query()
            ->where('parent_id', $parent->id)
            ->where('level', $data['level'])
            ->orderBy('name')
            ->get(['id', 'name', 'level'])]);
    }

    public function store(Request $request, EarlyWarningService $service): RedirectResponse
    {
        $data = $request->validate([
            'warning_id' => ['nullable', 'integer', 'exists:early_warnings,id'],
            'incident_id' => ['nullable', 'required_if:source,citizen_report,confirmed_incident', 'integer', 'exists:incidents,id'],
            'region_id' => ['required', 'integer', 'exists:regions,id'],
            'idempotency_key' => ['required', 'uuid'],
            'level' => ['required', 'in:advisory,watch,warning,critical'],
            'source' => ['required', 'in:citizen_report,confirmed_incident,government'],
            'area' => ['required', 'string', 'max:255'],
            'reason' => ['required', 'string', 'max:2000'],
            'instructions' => ['required', 'string', 'max:2000'],
            'expires_at' => ['nullable', 'date', 'after:now', 'before:30 days'],
        ]);
        try {
            $service->issue($request->user(), $data);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            Log::error('Early warning publication failed.', ['actor_id' => $request->user()->id, 'error_class' => $exception::class]);

            return back()->withInput()->with('error', 'Peringatan gagal disimpan. Silakan coba lagi.');
        }

        return redirect()->route('government.early-warnings')->with('success', 'Peringatan dini berhasil diterbitkan atau diperbarui.');
    }

    public function close(Request $request, EarlyWarning $warning, EarlyWarningService $service): RedirectResponse
    {
        try {
            $service->close($warning, $request->user());
        } catch (Throwable $exception) {
            Log::error('Early warning close failed.', ['warning_id' => $warning->id, 'actor_id' => $request->user()->id, 'error_class' => $exception::class]);

            return back()->with('error', 'Peringatan gagal ditutup. Silakan coba lagi.');
        }

        return redirect()->route('government.early-warnings')->with('success', 'Peringatan dini ditutup.');
    }
}
