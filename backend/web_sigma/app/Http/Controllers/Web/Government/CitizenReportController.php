<?php

namespace App\Http\Controllers\Web\Government;

use App\Http\Controllers\Controller;
use App\Models\CitizenReport;
use App\Models\ReportStatusHistory;
use App\Models\FieldTeam;
use App\Models\Region;
use App\Models\Incident;
use App\Models\FieldAssignment;
use App\Models\ResponseAction;
use App\Helpers\EncryptHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CitizenReportController extends Controller
{
    /**
     * Menampilkan daftar laporan masyarakat.
     */
    public function index(Request $request)
    {
        $query = CitizenReport::query()
            ->with([
                'user',
                'statusHistories.updater',
            ])
            ->latest();

        /*
        |--------------------------------------------------------------------------
        | Filter status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where(
                'verification_status',
                $request->status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter jenis laporan
        |--------------------------------------------------------------------------
        */

        if ($request->filled('report_type')) {
            $query->where(
                'report_type',
                $request->report_type
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pencarian
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where(
                    'description',
                    'like',
                    "%{$search}%"
                )
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where(
                            'name',
                            'like',
                            "%{$search}%"
                        );
                    });
            });
        }

        $reports = $query
            ->paginate(10)
            ->withQueryString();

        return view(
            'government.reports.index',
            compact('reports')
        );
    }


    /**
     * Menampilkan detail laporan.
     */
    public function show($hash)
    {
        try {

            $id = EncryptHelper::decrypt($hash);

        } catch (\Exception $e) {

            abort(404);

        }


        $report = CitizenReport::with([
            'user',
            'incident',
            'statusHistories.updater',
        ])->findOrFail($id);


        $teams = FieldTeam::where(
            'status',
            'active'
        )->get();


        $regions = Region::all();


        $actions = ResponseAction::where(
            'is_active',
            true
        )->get();



        return view(
            'government.reports.show',
            compact(
                'report',
                'teams',
                'regions',
                'actions'
            )
        );
    }

    public function verify(CitizenReport $report)
    {

        if ($report->verification_status !== 'pending') {

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Laporan sudah diproses.'
                );
        }


        DB::transaction(function () use ($report) {


            /*
        |--------------------------------------------------------------------------
        | Buat Incident dari laporan masyarakat
        |--------------------------------------------------------------------------
        */

            $incident = Incident::create([

                'source_type' => 'report',

                'location_description' =>
                'Laporan masyarakat #' . $report->id,

                'latitude' => $report->latitude,

                'longitude' => $report->longitude,

                'fire_status' => 'detected',

                'severity_level' => 'medium',

                'detected_at' => now(),

            ]);



            /*
        |--------------------------------------------------------------------------
        | Update status laporan + hubungkan incident
        |--------------------------------------------------------------------------
        */

            $report->update([

                'verification_status' => 'verified',

                'incident_id' => $incident->id,

            ]);



            /*
        |--------------------------------------------------------------------------
        | Simpan histori
        |--------------------------------------------------------------------------
        */

            ReportStatusHistory::create([

                'citizen_report_id' => $report->id,

                'status' => 'verified',

                'description' =>
                'Laporan telah diverifikasi oleh Pemerintah SIGMA.',

                'updated_by' => Auth::id(),

            ]);
        });



        return redirect()
            ->route(
                'government.reports.show',
                [
                    'hash' => EncryptHelper::encrypt($report->id)
                ]
            )
            ->with(
                'success',
                'Laporan berhasil diterima.'
            );
    }

    public function reject(
        Request $request,
        CitizenReport $report
    ) {

        $request->validate([

            'description' => [
                'required',
                'string',
                'max:1000'
            ],

        ]);


        DB::transaction(function () use ($request, $report) {


            /*
        |--------------------------------------------------------------------------
        | Update status laporan
        |--------------------------------------------------------------------------
        */

            $report->update([

                'verification_status' => 'rejected',

            ]);



            /*
        |--------------------------------------------------------------------------
        | Simpan history penolakan
        |--------------------------------------------------------------------------
        */

            ReportStatusHistory::create([

                'citizen_report_id' => $report->id,

                'status' => 'rejected',

                'description' => $request->description,

                'updated_by' => Auth::id(),

            ]);
        });


        return redirect()
            ->route('government.reports.show', [
            'hash' => EncryptHelper::encrypt($report->id)])
            ->with(
                'success',
                'Laporan berhasil ditolak.'
            );
    }

    public function assign(Request $request)
    {
        $request->validate([
            'report_id' => [
                'required',
                'exists:citizen_reports,id'
            ],

            'team_id' => [
                'required',
                'exists:field_teams,id'
            ],

            'region' => [
                'required'
            ],

            'action_type' => [
                'required'
            ],
        ]);


        $report = CitizenReport::findOrFail(
            $request->report_id
        );


        if (!$report->incident_id) {

            $incident = Incident::create([

                'source_type' => 'report',

                'location_description' =>
                'Laporan masyarakat #' . $report->id,

                'latitude' => $report->latitude,

                'longitude' => $report->longitude,

                'fire_status' => 'detected',

                'severity_level' => 'medium',

                'detected_at' => now(),

            ]);


            $report->update([
                'incident_id' => $incident->id
            ]);
        }


        DB::transaction(function () use ($request, $report) {


            FieldAssignment::create([

                'incident_id' => $report->incident_id,

                'team_id' => $request->team_id,

                'assigned_by' => Auth::id(),

                'priority_level' => 'medium',

                'status' => 'assigned',

                'assigned_at' => now(),

            ]);



            ReportStatusHistory::create([

                'citizen_report_id' => $report->id,

                'status' => 'process',

                'description' =>
                'Petugas telah ditugaskan oleh Pemerintah SIGMA.',

                'updated_by' => Auth::id(),

            ]);
        });



        return redirect()
            ->route(
                'government.reports.show',
                [
                    'hash' => EncryptHelper::encrypt($report->id)
                ]
            )
            ->with(
                'success',
                'Petugas berhasil dikirim.'
            );
    }


    public function history($hash)
{
    try {

        $id = EncryptHelper::decrypt($hash);

    } catch (\Exception $e) {

        abort(404);

    }


    $report = CitizenReport::findOrFail($id);


    $histories = $report->statusHistories()
        ->with('updater')
        ->latest('created_at')
        ->get()
        ->map(function ($history) {

            return [
                'id' => $history->id,
                'status' => $history->status,
                'description' => $history->description,
                'updated_by' => $history->updater?->name,
                'created_at' => $history->created_at?->format('d M Y, H:i'),
            ];

        });


    return response()->json([
        'histories' => $histories,
    ]);
}
}
