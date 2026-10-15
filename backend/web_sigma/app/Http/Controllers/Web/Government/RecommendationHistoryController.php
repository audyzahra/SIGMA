<?php

namespace App\Http\Controllers\Web\Government;

use App\Http\Controllers\Controller;
use App\Models\FieldAssignment;

class RecommendationHistoryController extends Controller
{
    public function index()
    {
        $assignments = FieldAssignment::with([
            'incident',
            'region',
            'team',
            'assigner',
            'acceptor',
            'navigationLogs',
            'statusHistories.updater',
        ])
            ->latest('assigned_at')
            ->paginate(10);

        return view(
            'government.recommendation-history',
            compact('assignments')
        );
    }

    public function show(FieldAssignment $assignment)
    {
        $assignment->load([
            'incident',
            'region',
            'team',
            'assigner',
            'acceptor',
            'navigationLogs',
            'statusHistories.updater',
        ]);

        return view(
            'government.recommendation-history-detail',
            compact('assignment')
        );
    }
}
