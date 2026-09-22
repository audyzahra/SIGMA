<?php

namespace App\Http\Controllers\Web\Government;

use App\Http\Controllers\Controller;
use App\Models\ImpactAssessment;
use App\Models\Incident;

class ImpactController extends Controller
{
    public function index()
    {
        $incidents = Incident::with('impactAssessment')
            ->latest('detected_at')
            ->get();

        $impactAssessments = ImpactAssessment::with('incident')
            ->latest('calculated_at')
            ->get();

        return view('government.impact', compact(
            'incidents',
            'impactAssessments'
        ));
    }
}