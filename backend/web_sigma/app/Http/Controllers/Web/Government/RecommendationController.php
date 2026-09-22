<?php

namespace App\Http\Controllers\Web\Government;

use App\Http\Controllers\Controller;
use App\Models\AiResponseRecommendation;

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

        return view('government.recommendation', compact(
            'recommendations'
        ));
    }
}