<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use \App\Helpers\EncryptHelper;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Region;
use App\Services\SuperAdminAuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrganizationController extends Controller
{
    public function index(Request $r)
    {
        $organizations = Organization::with('region')

            ->when($r->search, function ($q, $value) {

                $q->where(function ($query) use ($value) {

                    $query
                        ->where('name', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%");
                });
            })

            ->when($r->type, function ($q, $value) {

                $q->where('type', $value);
            })

            ->when($r->status, function ($q, $value) {

                $q->where('status', $value);
            })

            ->latest()
            ->paginate(15)
            ->withQueryString();


        if ($r->ajax()) {

            return view(
                'super_admin.organizations.table',
                compact('organizations')
            );
        }


        return view(
            'super_admin.organizations.index',
            compact('organizations')
        );
    }

    public function create()
    {
        return view('super_admin.organizations.create', ['regions' => Region::orderBy('name')->get()]);
    }

    public function store(Request $r, SuperAdminAuditService $a)
    {
        $o = Organization::create($this->valid($r));
        $a->log($r, 'CREATE', 'organizations', "Membuat organisasi $o->name", null, $o);

        return redirect()->route('super-admin.organizations.index')->with('success', 'Organisasi dibuat.');
    }

    public function show(string $organization)
    {
        $id = EncryptHelper::decrypt($organization);

        $organization = Organization::findOrFail($id);

        return view('super_admin.organizations.show', compact('organization'));
    }

    public function edit(string $organization)
    {
        $id = EncryptHelper::decrypt($organization);

        $organization = Organization::findOrFail($id);

        return view('super_admin.organizations.edit', [
            'organization' => $organization,
            'regions' => Region::orderBy('name')->get()
        ]);
    }

    public function update(Request $r, string $organization, SuperAdminAuditService $a)
    {
        $id = EncryptHelper::decrypt($organization);

        $organization = Organization::findOrFail($id);

        $old = clone $organization;

        $organization->update($this->valid($r));

        $a->log(
            $r,
            'UPDATE',
            'organizations',
            "Memperbarui organisasi $organization->name",
            $old,
            $organization
        );

        return redirect()
            ->route(
                'super-admin.organizations.show',
                EncryptHelper::encrypt($organization->id)
            )
            ->with('success', 'Organisasi diperbarui.');
    }

    public function destroy(Request $r, string $organization, SuperAdminAuditService $a)
    {
        $id = EncryptHelper::decrypt($organization);

        $organization = Organization::findOrFail($id);

        $old = clone $organization;

        $organization->delete();

        $a->log(
            $r,
            'DELETE',
            'organizations',
            "Menghapus organisasi $old->name",
            $old
        );

        return redirect()
            ->route('super-admin.organizations.index')
            ->with('success', 'Organisasi dihapus.');
    }

    private function valid(Request $r): array
    {
        return $r->validate(['name' => 'required|string|max:255', 'type' => ['required', Rule::in(['government', 'team', 'company'])], 'address' => 'nullable|string', 'phone' => 'nullable|string|max:20', 'email' => 'nullable|email', 'region_id' => 'nullable|exists:regions,id', 'status' => ['required', Rule::in(['active', 'inactive'])]]);
    }
}
