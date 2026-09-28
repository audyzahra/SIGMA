<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;

class RegionController extends Controller
{
    public function provinces()
    {
        $regions = Region::where('level', 'province')
            ->orderBy('name')
            ->get([
                'id',
                'parent_id',
                'name',
                'code',
                'level',
            ]);

        return response()->json([
            'success' => true,
            'data' => $regions,
        ]);
    }

    public function children($id)
    {
        $regions = Region::where('parent_id', $id)
            ->orderBy('name')
            ->get([
                'id',
                'parent_id',
                'name',
                'code',
                'level',
            ]);

        return response()->json([
            'success' => true,
            'parent_id' => $id,
            'data' => $regions,
        ]);
    }
}
