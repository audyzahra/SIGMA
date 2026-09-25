<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Helpers\EncryptHelper;
use App\Http\Controllers\Controller;
use App\Services\SuperAdminAuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('permissions')
            ->orderBy('name')
            ->get();

        return view('super_admin.role_permissions.index', compact('roles'));
    }

    public function create()
    {
        return view('super_admin.role_permissions.create', ['permissions' => Permission::orderBy('name')->get()]);
    }

    public function store(Request $r, SuperAdminAuditService $a)
    {
        $d = $this->valid($r);
        $role = Role::create(['name' => $d['name'], 'guard_name' => 'web']);
        $role->syncPermissions($d['permissions'] ?? []);
        $a->log($r, 'CREATE', 'roles', "Membuat role $role->name", null, $role);

        return redirect()
            ->route(
                'super-admin.role-permissions.show',
                EncryptHelper::encrypt($role->id)
            )
            ->with('success', 'Role dibuat.');
    }

    public function show(string $role)
    {
        $id = EncryptHelper::decrypt($role);

        $role = Role::findOrFail($id);

        return view('super_admin.role_permissions.show', [
            'role' => $role->load('permissions')
        ]);
    }

    public function edit(string $role)
    {
        $id = EncryptHelper::decrypt($role);

        $role = Role::findOrFail($id);

        return view('super_admin.role_permissions.edit', [
            'role' => $role->load('permissions'),
            'permissions' => Permission::orderBy('name')->get()
        ]);
    }

    public function update(Request $r, string $role, SuperAdminAuditService $a)
    {
        $id = EncryptHelper::decrypt($role);

        $role = Role::findOrFail($id);

        $old = clone $role;

        $d = $this->valid($r, $role);

        $role->update([
            'name' => $d['name']
        ]);

        $role->syncPermissions($d['permissions'] ?? []);

        $a->log(
            $r,
            'UPDATE',
            'roles',
            "Memperbarui role $role->name dan permission",
            $old,
            $role
        );

        return redirect()
            ->route(
                'super-admin.role-permissions.show',
                EncryptHelper::encrypt($role->id)
            )
            ->with('success', 'Role diperbarui.');
    }

    public function destroy(Request $r, string $role, SuperAdminAuditService $a)
    {
        $id = EncryptHelper::decrypt($role);

        $role = Role::findOrFail($id);

        abort_if(
            $role->name === 'super_admin',
            422,
            'Role super_admin tidak dapat dihapus.'
        );

        $old = clone $role;

        $role->delete();

        $a->log(
            $r,
            'DELETE',
            'roles',
            "Menghapus role $old->name",
            $old
        );

        return redirect()
            ->route('super-admin.role-permissions.index')
            ->with('success', 'Role dihapus.');
    }

    private function valid(Request $r, ?Role $role = null): array
    {
        return $r->validate(['name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($role)], 'permissions' => 'nullable|array', 'permissions.*' => 'exists:permissions,name']);
    }
}
