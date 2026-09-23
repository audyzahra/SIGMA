<?php

namespace App\Http\Controllers\Api\Officer;

use App\Http\Controllers\Controller;
use App\Services\Officer\OfficerTaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function __construct(private readonly OfficerTaskService $tasks) {}

    public function __invoke(Request $request): JsonResponse
    {
        return response()->json(['tasks' => $this->tasks->forUser($request->user())->map(fn ($task): array => $this->tasks->present($task))->values()]);
    }
}
