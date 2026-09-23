<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Helpers\EncryptHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SuperAdminAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('roles')->when($request->search, fn($q, $v)
        => $q->where(fn($q) => $q->where('name', 'like', "%$v%")
            ->orWhere('email', 'like', "%$v%")))
            ->when($request->role, fn($q, $v) => $q->role($v))
            ->latest()
            ->paginate($request->get('per_page', 5))
            ->withQueryString();

        return view('super_admin.manage_users.index', compact('users'));
    }

    public function create()
    {
        return view('super_admin.manage_users.create', ['roles' => Role::orderBy('name')->get()]);
    }

    public function store(Request $r, SuperAdminAuditService $a)
    {
        $d = $this->valid($r);
        $u = User::create(['name' => $d['name'], 'email' => $d['email'], 'password' => Hash::make($d['password'])]);
        $u->syncRoles([$d['role']]);
        $a->log($r, 'CREATE', 'users', "Membuat pengguna $u->email", null, $u);

        return redirect()->route('super-admin.manage-users.index')->with('success', 'Pengguna dibuat.');
    }

    public function show(string $user)
    {
        $id = EncryptHelper::decrypt($user);

        $user = User::findOrFail($id);

        return view('super_admin.manage_users.show', compact('user'));
    }

    public function edit(string $user)
    {
        $id = EncryptHelper::decrypt($user);

        $user = User::findOrFail($id);

        return view('super_admin.manage_users.edit', [
            'user' => $user,
            'roles' => Role::orderBy('name')->get()
        ]);
    }

    public function update(Request $r, string $user, SuperAdminAuditService $a)
    {
        $id = EncryptHelper::decrypt($user);

        $user = User::findOrFail($id);

        $old = clone $user;

        $d = $this->valid($r, $user);

        $user->fill([
            'name' => $d['name'],
            'email' => $d['email']
        ]);

        if ($d['password'] ?? false) {
            $user->password = Hash::make($d['password']);
        }

        $user->save();

        $user->syncRoles([$d['role']]);

        $a->log(
            $r,
            'UPDATE',
            'users',
            "Memperbarui pengguna $user->email",
            $old,
            $user
        );

        return redirect()
            ->route(
                'super-admin.manage-users.show',
                EncryptHelper::encrypt($user->id)
            )
            ->with('success', 'Pengguna diperbarui.');
    }

    public function destroy(Request $r, string $user, SuperAdminAuditService $a)
    {
        $id = EncryptHelper::decrypt($user);

        $user = User::findOrFail($id);

        abort_if(
            $user->is($r->user()),
            422,
            'Akun sendiri tidak dapat dihapus.'
        );

        $old = clone $user;

        $user->delete();

        $a->log(
            $r,
            'DELETE',
            'users',
            "Menghapus pengguna $old->email",
            $old
        );

        return redirect()
            ->route('super-admin.manage-users.index')
            ->with('success', 'Pengguna dihapus.');
    }

    private function valid(Request $r, ?User $u = null): array
    {
        return $r->validate(['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($u)], 'role' => ['required', Rule::exists('roles', 'name')->where('guard_name', 'web')], 'password' => [$u ? 'nullable' : 'required', 'confirmed', 'min:8']]);
    }
}
