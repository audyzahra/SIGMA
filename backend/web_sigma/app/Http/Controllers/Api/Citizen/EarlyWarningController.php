<?php

namespace App\Http\Controllers\Api\Citizen;

use App\Http\Controllers\Controller;
use App\Models\EarlyWarning;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EarlyWarningController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $regionIds = $this->applicableWarningRegionIds($request->user()->region_id);
        $warnings = EarlyWarning::query()->whereIn('region_id', $regionIds)->where('status', 'active')->where(function ($query): void {
            $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
        })->with(['region:id,name,parent_id,level', 'region.parent:id,name,parent_id,level', 'region.parent.parent:id,name,level'])->latest('issued_at')->paginate(20);

        $warnings->through(fn (EarlyWarning $warning): array => $this->present($warning));

        return response()->json($warnings);
    }

    public function show(Request $request, EarlyWarning $warning): JsonResponse
    {
        $isInCurrentScope = in_array((int) $warning->region_id, $this->applicableWarningRegionIds($request->user()->region_id), true);
        $hasWarningNotification = $request->user()->notifications()->where('data->warning_id', $warning->id)->exists();
        abort_unless($isInCurrentScope || $hasWarningNotification, 404);

        return response()->json(['warning' => $this->present($warning->load(['region:id,name,parent_id,level', 'region.parent:id,name,parent_id,level', 'region.parent.parent:id,name,level']))]);
    }

    /** @return list<int> */
    private function applicableWarningRegionIds(?int $regionId): array
    {
        if (! $regionId) {
            return [];
        }

        $ids = [$regionId];
        $region = Region::query()->find($regionId);
        while ($region?->parent_id) {
            $ids[] = $region->parent_id;
            $region = Region::query()->find($region->parent_id);
        }

        return array_values(array_unique(array_map('intval', $ids)));
    }

    /** @return array<string, mixed> */
    private function present(EarlyWarning $warning): array
    {
        return [
            'id' => $warning->id,
            'level' => $warning->level,
            'area' => $warning->area,
            'region' => $this->regionLabel($warning->region),
            'reason' => $warning->reason,
            'instructions' => $warning->instructions,
            'issued_at' => $warning->issued_at?->toIso8601String(),
            'expires_at' => $warning->expires_at?->toIso8601String(),
            'status' => $warning->status === 'active' && $warning->expires_at?->isPast() ? 'expired' : $warning->status,
        ];
    }

    private function regionLabel(?Region $region): ?string
    {
        if (! $region) return null;
        return collect([$region->name, $region->parent?->name, $region->parent?->parent?->name])->filter()->implode(', ');
    }
}
