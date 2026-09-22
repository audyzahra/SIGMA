<?php

namespace App\Http\Controllers\Web\Government;

use App\Http\Controllers\Controller;
use App\Models\IncidentPriority;

class PriorityController extends Controller
{
    public function index()
    {
        $priorities = IncidentPriority::with([
                'incident.impactAssessment',
            ])
            ->latest('created_at')
            ->get()
            ->unique(function ($priority) {
                return $priority->incident?->location_description;
            })
            ->sortByDesc('priority_score')
            ->values();

        return view('government.priority', compact('priorities'));
    }
}