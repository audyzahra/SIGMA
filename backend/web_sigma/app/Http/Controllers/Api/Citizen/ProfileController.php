<?php

namespace App\Http\Controllers\Api\Citizen;

use App\Http\Controllers\Controller;
use App\Http\Requests\Citizen\Profile\UpdateProfileRequest;
use App\Http\Resources\Citizen\ProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json(['profile' => (new ProfileResource($request->user()))->resolve()]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->safe()->only('name'));

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'profile' => (new ProfileResource($user->fresh()))->resolve(),
        ]);
    }
}
