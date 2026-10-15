<?php

namespace App\Services;

use App\Models\EarlyWarning;
use App\Models\EarlyWarningHistory;
use App\Models\Incident;
use App\Models\Region;
use App\Models\User;
use App\Notifications\EarlyWarningChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EarlyWarningService
{
    public function issue(User $issuer, array $data): EarlyWarning
    {
        return DB::transaction(function () use ($issuer, $data): EarlyWarning {
            $previous = EarlyWarningHistory::query()->where('idempotency_key', $data['idempotency_key'])->first();
            if ($previous) {
                return EarlyWarning::query()->findOrFail($previous->early_warning_id);
            }

            $incident = isset($data['incident_id']) ? Incident::query()->lockForUpdate()->findOrFail($data['incident_id']) : null;
            $region = Region::query()->findOrFail($data['region_id']);
            if (! in_array($region->level, ['province', 'regency', 'district'], true)) {
                throw ValidationException::withMessages(['region_id' => 'Cakupan peringatan harus menggunakan provinsi, kabupaten/kota, atau kecamatan.']);
            }
            if (($data['source'] ?? null) === 'citizen_report' && ! $incident?->citizenReports()->where('verification_status', 'verified')->exists()) {
                throw ValidationException::withMessages(['incident_id' => 'Peringatan dari laporan hanya dapat diterbitkan setelah laporan terverifikasi.']);
            }
            if ($incident && in_array(strtolower((string) $incident->fire_status), ['extinguished', 'resolved', 'closed'], true)) {
                throw ValidationException::withMessages(['incident_id' => 'Insiden yang selesai tidak dapat diberi peringatan aktif.']);
            }

            $existing = isset($data['warning_id'])
                ? EarlyWarning::query()->lockForUpdate()->findOrFail($data['warning_id'])
                : ($incident ? EarlyWarning::query()->where('incident_id', $incident->id)->where('status', 'active')->lockForUpdate()->first() : null);
            if ($existing && ($existing->status !== 'active' || ($incident && $existing->incident_id !== $incident->id))) {
                throw ValidationException::withMessages(['warning_id' => 'Peringatan ini tidak lagi aktif atau tidak sesuai dengan insiden yang dipilih.']);
            }
            if ($existing) {
                $previousRegionId = $existing->region_id;
                $existing->update([
                    'level' => $data['level'], 'area' => $data['area'],
                    'reason' => $data['reason'], 'instructions' => $data['instructions'],
                    'source' => $data['source'], 'region_id' => $data['region_id'] ?? null,
                    'issued_by' => $issuer->id, 'issued_at' => now(),
                    'expires_at' => $data['expires_at'] ?? now()->addDay(),
                ]);

                $this->recordHistory($existing, $issuer, 'updated', $data['idempotency_key']);
                DB::afterCommit(fn () => $this->notifyCitizens($existing, 'updated', [$previousRegionId, $existing->region_id]));

                return $existing->fresh(['region', 'incident']);
            }

            $attributes = $data;
            unset($attributes['idempotency_key']);
            unset($attributes['warning_id']);
            $attributes['issued_by'] = $issuer->id;
            $attributes['status'] = 'active';
            $attributes['issued_at'] = now();
            $attributes['expires_at'] = $data['expires_at'] ?? now()->addDay();
            $warning = EarlyWarning::query()->create($attributes);
            $this->recordHistory($warning, $issuer, 'issued', $data['idempotency_key']);
            DB::afterCommit(fn () => $this->notifyCitizens($warning, 'issued'));

            return $warning;
        });
    }

    public function close(EarlyWarning $warning, User $actor): EarlyWarning
    {
        return DB::transaction(function () use ($warning, $actor): EarlyWarning {
            $warning = EarlyWarning::query()->lockForUpdate()->findOrFail($warning->id);
            if ($warning->status === 'active') {
                $warning->update(['status' => 'closed', 'closed_at' => now()]);
                $this->recordHistory($warning, $actor, 'closed');
                DB::afterCommit(fn () => $this->notifyCitizens($warning, 'closed'));
            }

            return $warning->fresh(['region', 'incident']);
        });
    }

    private function recordHistory(EarlyWarning $warning, User $actor, string $action, ?string $idempotencyKey = null): void
    {
        EarlyWarningHistory::query()->create([
            'early_warning_id' => $warning->id,
            'actor_id' => $actor->id,
            'action' => $action,
            'snapshot' => $warning->only(['level', 'status', 'source', 'area', 'reason', 'instructions', 'issued_at', 'expires_at', 'closed_at', 'region_id', 'incident_id']),
            'created_at' => now(),
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    private function notifyCitizens(EarlyWarning $warning, string $action, array $regionIds = []): void
    {
        $warning->loadMissing('region');
        $scopes = Region::query()->whereIn('id', array_values(array_unique(array_filter($regionIds ?: [$warning->region_id]))))->get(['id', 'level']);
        $recipientRegionIds = [];

        foreach ($scopes as $scope) {
            if ($scope->level === 'district') {
                $recipientRegionIds[] = $scope->id;
                continue;
            }

            $expectedChildLevel = $scope->level === 'province' ? 'regency' : 'district';
            $frontier = [$scope->id];
            for ($depth = 0; $depth < ($scope->level === 'province' ? 2 : 1); $depth++) {
                $children = Region::query()->whereIn('parent_id', $frontier)->where('level', $expectedChildLevel)->pluck('id')->all();
                $recipientRegionIds = array_merge($recipientRegionIds, $children);
                $frontier = $children;
                if ($scope->level === 'province') {
                    $expectedChildLevel = 'district';
                }
            }
        }

        $recipientRegionIds = array_values(array_unique($recipientRegionIds));
        if ($recipientRegionIds === []) return;

        User::query()->role('citizen')->whereIn('region_id', array_unique($recipientRegionIds))
            ->chunkById(200, function ($citizens) use ($warning, $action): void {
                foreach ($citizens as $citizen) {
                    $citizen->notify(new EarlyWarningChanged($warning, $action));
                }
            });
    }
}
