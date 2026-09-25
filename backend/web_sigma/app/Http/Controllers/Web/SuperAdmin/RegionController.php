<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Helpers\EncryptHelper;
use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Services\SuperAdminAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegionController extends Controller
{
    public function index(Request $request): View
    {
        $regions = Region::with('parent')
            ->withCount(['children', 'organizations'])
            ->when(
                $request->search,
                function ($query, $value) {

                    $query->where(function ($query) use ($value) {

                        $query
                            ->where('name', 'like', "%{$value}%")
                            ->orWhere('code', 'like', "%{$value}%")
                            ->orWhereHas('parent', function ($parent) use ($value) {

                                $parent->where(
                                    'name',
                                    'like',
                                    "%{$value}%"
                                );
                            });
                    });
                }
            )
            ->when(
                $request->level,
                fn($query, $value) => $query->where('level', $value)
            )
            ->orderBy('level')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {

            return view(
                'super_admin.regions.table',
                compact('regions')
            );
        }
        return view('super_admin.regions.index', compact('regions'));
    }

    public function create(): View
    {
        return view('super_admin.regions.create', [
            'parents' => Region::orderBy('name')->get()
        ]);
    }

    public function store(
        Request $request,
        SuperAdminAuditService $audit
    ): RedirectResponse {
        $region = Region::create($this->validated($request));

        $audit->log(
            $request,
            'CREATE',
            'regions',
            "Membuat wilayah {$region->name}",
            null,
            $region
        );

        return redirect()
            ->route('super-admin.regions.index')
            ->with('success', 'Wilayah dibuat.');
    }

    public function show(string $region): View
    {
        $id = EncryptHelper::decrypt($region);

        $region = Region::with([
            'parent',
            'children',
            'organizations'
        ])->findOrFail($id);

        return view('super_admin.regions.show', compact('region'));
    }

    public function edit(string $region): View
    {
        $id = EncryptHelper::decrypt($region);

        $region = Region::findOrFail($id);

        return view('super_admin.regions.edit', [
            'region' => $region,
            'parents' => Region::whereKeyNot($region)
                ->orderBy('name')
                ->get()
        ]);
    }

    public function update(
        Request $request,
        string $region,
        SuperAdminAuditService $audit
    ): RedirectResponse {
        $id = EncryptHelper::decrypt($region);

        $region = Region::findOrFail($id);

        $old = clone $region;

        $region->update(
            $this->validated($request, $region)
        );

        $audit->log(
            $request,
            'UPDATE',
            'regions',
            "Memperbarui wilayah {$region->name}",
            $old,
            $region
        );

        return redirect()
            ->route(
                'super-admin.regions.show',
                EncryptHelper::encrypt($region->id)
            )
            ->with('success', 'Wilayah diperbarui.');
    }

    public function destroy(
        Request $request,
        string $region,
        SuperAdminAuditService $audit
    ): RedirectResponse {
        $id = EncryptHelper::decrypt($region);

        $region = Region::findOrFail($id);

        if (
            $region->children()->exists() ||
            $region->organizations()->exists()
        ) {
            return back()->withErrors([
                'region' => 'Wilayah yang memiliki subwilayah atau organisasi tidak dapat dihapus.'
            ]);
        }

        $old = clone $region;

        $region->delete();

        $audit->log(
            $request,
            'DELETE',
            'regions',
            "Menghapus wilayah {$old->name}",
            $old
        );

        return redirect()
            ->route('super-admin.regions.index')
            ->with('success', 'Wilayah dihapus.');
    }

    private function validated(
        Request $request,
        ?Region $region = null
    ): array {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255'
            ],

            'code' => [
                'nullable',
                'string',
                'max:50'
            ],

            'level' => [
                'required',
                Rule::in([
                    'province',
                    'regency',
                    'district'
                ])
            ],

            'parent_id' => [
                'nullable',
                'exists:regions,id',
                Rule::notIn([$region?->id])
            ],

            'area_size' => [
                'nullable',
                'numeric',
                'min:0'
            ]
        ]);
    }
}
