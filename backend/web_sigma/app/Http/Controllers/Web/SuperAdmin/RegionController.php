<?php

namespace App\Http\Controllers\Web\SuperAdmin;

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
        $regions = Region::with('parent')->withCount(['children', 'organizations'])->when($request->search, fn ($query, $value) => $query->where(fn ($query) => $query->where('name', 'like', "%{$value}%")->orWhere('code', 'like', "%{$value}%")))->when($request->level, fn ($query, $value) => $query->where('level', $value))->orderBy('level')->orderBy('name')->paginate(15)->withQueryString();

        return view('super_admin.regions.index', compact('regions'));
    }

    public function create(): View
    {
        return view('super_admin.regions.create', ['parents' => Region::orderBy('name')->get()]);
    }

    public function store(Request $request, SuperAdminAuditService $audit): RedirectResponse
    {
        $region = Region::create($this->validated($request));
        $audit->log($request, 'CREATE', 'regions', "Membuat wilayah {$region->name}", null, $region);

        return redirect()->route('super-admin.regions.index')->with('success', 'Wilayah dibuat.');
    }

    public function show(Region $region): View
    {
        return view('super_admin.regions.show', ['region' => $region->load(['parent', 'children', 'organizations'])]);
    }

    public function edit(Region $region): View
    {
        return view('super_admin.regions.edit', ['region' => $region, 'parents' => Region::whereKeyNot($region)->orderBy('name')->get()]);
    }

    public function update(Request $request, Region $region, SuperAdminAuditService $audit): RedirectResponse
    {
        $old = clone $region;
        $region->update($this->validated($request, $region));
        $audit->log($request, 'UPDATE', 'regions', "Memperbarui wilayah {$region->name}", $old, $region);

        return redirect()->route('super-admin.regions.show', $region)->with('success', 'Wilayah diperbarui.');
    }

    public function destroy(Request $request, Region $region, SuperAdminAuditService $audit): RedirectResponse
    {
        if ($region->children()->exists() || $region->organizations()->exists()) {
            return back()->withErrors(['region' => 'Wilayah yang memiliki subwilayah atau organisasi tidak dapat dihapus.']);
        } $old = clone $region;
        $region->delete();
        $audit->log($request, 'DELETE', 'regions', "Menghapus wilayah {$old->name}", $old);

        return redirect()->route('super-admin.regions.index')->with('success', 'Wilayah dihapus.');
    }

    private function validated(Request $request, ?Region $region = null): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:255'], 'code' => ['nullable', 'string', 'max:50'], 'level' => ['required', Rule::in(['province', 'regency', 'district'])], 'parent_id' => ['nullable', 'exists:regions,id', Rule::notIn([$region?->id])], 'area_size' => ['nullable', 'numeric', 'min:0']]);
    }
}
