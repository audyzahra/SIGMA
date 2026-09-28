<?php

namespace App\Http\Controllers\Web\Government;

use App\Http\Controllers\Controller;
use App\Models\AiResponseRecommendation;
use App\Models\FieldTeam;
use App\Models\Region;
use App\Models\ResponseAction;

class RecommendationController extends Controller
{
    public function index()
    {
        $recommendations = AiResponseRecommendation::with([
            'incident.priority',
        ])
            ->latest('generated_at')
            ->get()
            ->unique(function ($recommendation) {
                return $recommendation->incident?->location_description
                    . '|'
                    . $recommendation->recommendation_type;
            })
            ->sortByDesc(function ($recommendation) {
                return $recommendation->incident?->priority?->priority_score ?? 0;
            })
            ->values();


        /*
    |--------------------------------------------------------------------------
    | Ambil tim pemadam aktif
    |--------------------------------------------------------------------------
    */

        $teams = FieldTeam::where('status', 'active')
            ->get();


        $regions = Region::all();

        $actions = ResponseAction::where(
            'is_active',
            true
        )->get();



        return view('government.recommendation', compact(
            'recommendations',
            'teams',
            'regions',
            'actions'
        ));
    }
}
