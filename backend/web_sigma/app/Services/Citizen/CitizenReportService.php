<?php

namespace App\Services\Citizen;

use App\Models\CitizenReport;
use App\Models\ReportStatusHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CitizenReportService
{
    /** @return Collection<int, CitizenReport> */
    public function forUser(User $user): Collection
    {
        return CitizenReport::query()
            ->where('user_id', $user->id)
            ->with('statusHistories')
            ->latest()
            ->get();
    }

    /** @param array{report_type: string, latitude: float|int|string, longitude: float|int|string, description?: string|null} $attributes */
    public function create(User $user, array $attributes): CitizenReport
    {
        return DB::transaction(function () use ($attributes, $user): CitizenReport {
            $report = CitizenReport::create([
                ...$attributes,
                'user_id' => $user->id,
                'verification_status' => 'pending',
            ]);

            ReportStatusHistory::create([
                'citizen_report_id' => $report->id,
                'status' => 'submitted',
                'description' => 'Laporan diterima oleh sistem SIGMA.',
                'updated_by' => $user->id,
            ]);

            return $report->load('statusHistories');
        });
    }

    /** @return array<string, int> */
    public function statistics(Collection $reports): array
    {
        $statuses = $reports->map(fn (CitizenReport $report): string => $this->status($report));

        return [
            'total' => $reports->count(),
            'submitted' => $statuses->filter(fn (string $status): bool => $status === 'submitted')->count(),
            'verified' => $statuses->filter(fn (string $status): bool => $status === 'verified')->count(),
            'process' => $statuses->filter(fn (string $status): bool => $status === 'process')->count(),
            'completed' => $statuses->filter(fn (string $status): bool => $status === 'completed')->count(),
            'rejected' => $reports->where('verification_status', 'rejected')->count(),
        ];
    }

    public function status(CitizenReport $report): string
    {
        $latestHistory = $report->statusHistories->sortByDesc('created_at')->first();

        return $latestHistory?->status ?? match ($report->verification_status) {
            'verified' => 'verified', 'rejected' => 'rejected', default => 'submitted',
        };
    }
}
