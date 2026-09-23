<?php

namespace App\Http\Controllers\Web\Government;

use App\Http\Controllers\Controller;
use App\Models\FireRisk;
use App\Models\Region;

class FireRiskController extends Controller
{
    public function index()
    {
        // Ambil seluruh data risiko terbaru
        $fireRisks = FireRisk::with('region')
            ->orderByDesc('calculated_at')
            ->orderByDesc('id')
            ->get();

        // Ambil wilayah yang memang memiliki data risiko
        $regions = Region::whereIn(
            'id',
            $fireRisks->pluck('region_id')->unique()
        )
            ->orderBy('name')
            ->get();

        return view('government.fire-risk', compact(
            'regions',
            'fireRisks'
        ));
    }
}
