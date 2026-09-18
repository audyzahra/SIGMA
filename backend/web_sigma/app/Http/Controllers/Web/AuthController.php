<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin(string $access): mixed
    {
        $roles = ['super-admin' => 'super_admin', 'pemerintah' => 'government'];
        abort_unless(array_key_exists($access, $roles), 404);

        return view('auth.login', ['access' => $access, 'role' => $roles[$access]]);
    }

    public function showSelection(): mixed
    {
        return view('auth.login-selection');
    }

    public function login(LoginRequest $request, string $access): RedirectResponse
    {
        $roles = ['super-admin' => 'super_admin', 'pemerintah' => 'government'];
        abort_unless(array_key_exists($access, $roles), 404);

        if (! Auth::attempt($request->only(['email', 'password']), true)) {
            return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = $request->user();

        if (! $user->hasVerifiedEmail()) {
            Auth::logout();

            return back()->withErrors(['email' => 'Email belum diverifikasi.'])->onlyInput('email');
        }

        $requestedRole = $roles[$access];
        if (! $user->hasRole($requestedRole)) {
            Auth::logout();

            return back()->withErrors(['email' => 'Akun tidak memiliki akses sebagai role tersebut.'])->onlyInput('email');
        }

        if ($user->hasRole('super_admin')) {
            return redirect('/super-admin/dashboard');
        }

        if ($user->hasRole('government')) {
            return redirect('/government/dashboard');
        }

        Auth::logout();

        return back()->withErrors(['email' => 'Akun ini tidak memiliki akses web.'])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
