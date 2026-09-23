<?php

namespace App\Http\Controllers\Web\Government;

use App\Http\Controllers\Controller;
use App\Models\CitizenReport;
use App\Models\ReportStatusHistory;
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
    public function show(CitizenReport $report)
    {
        $report->load([
            'user',
            'incident',
            'statusHistories.updater',
        ]);

        return view(
            'government.reports.show',
            compact('report')
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
        | Update status laporan utama
        |--------------------------------------------------------------------------
        */

            $report->update([
                'verification_status' => 'verified',
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
            ->route('government.reports.show', $report)
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
            ->route('government.reports.show', $report)
            ->with(
                'success',
                'Laporan berhasil ditolak.'
            );
    }
}
