<?php

namespace App\Http\Controllers\Api\Citizen;

use App\Http\Controllers\Controller;
use App\Http\Requests\Citizen\Profile\UpdateProfileRequest;
use App\Http\Resources\Citizen\ProfileResource;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load('region.parent.parent');

        return response()->json(['profile' => (new ProfileResource($user))->resolve()]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->safe()->only('name', 'region_id'));
        $user->load('region.parent.parent');

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'profile' => (new ProfileResource($user->fresh()))->resolve(),
        ]);
    }

    public function regions(Request $request): JsonResponse
    {
        $data = $request->validate([
            'parent_id' => ['nullable', 'integer', 'exists:regions,id'],
            'level' => ['nullable', 'in:province,regency,district'],
        ]);

        $level = $data['level'] ?? ($data['parent_id'] ? null : 'province');
        abort_unless($level !== null, 422, 'Tingkat wilayah wajib diisi untuk pilihan bertingkat.');

        if ($level === 'province') {
            abort_if(isset($data['parent_id']), 422, 'Provinsi tidak memiliki wilayah induk.');
            $regions = Region::query()->whereNull('parent_id')->where('level', 'province');
        } else {
            abort_unless(isset($data['parent_id']), 422, 'Pilih wilayah induk terlebih dahulu.');
            $parent = Region::query()->findOrFail($data['parent_id']);
            $expectedParentLevel = $level === 'regency' ? 'province' : 'regency';
            abort_unless($parent->level === $expectedParentLevel, 422, 'Tingkatan wilayah tidak sesuai.');
            $regions = Region::query()->where('parent_id', $parent->id)->where('level', $level);
        }

        return response()->json(['regions' => $regions
            ->orderBy('name')
            ->get(['id', 'name', 'level', 'parent_id'])]);
    }
}
