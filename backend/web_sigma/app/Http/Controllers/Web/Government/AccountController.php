<?php

namespace App\Http\Controllers\Web\Government;

use App\Http\Controllers\Controller;
use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;

class AccountController extends Controller
{
    /**
     * Halaman utama pengaturan.
     */
    public function index()
    {
        $user = Auth::user();

        return view('government.account.index', compact('user'));
    }

    /**
     * Halaman profil.
     */
    public function profile()
    {
        $user = Auth::user();

        return view('government.account.profile', compact('user'));
    }

    /**
     * Update profil.
     */
    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . $user->id,
            ],
        ]);

        $user->update($validated);

        return redirect()
            ->route('government.account.profile')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Halaman keamanan/password.
     */
    public function password()
    {
        return view('government.account.password');
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => [
                'required',
                'current_password',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        /** @var User $user */
        $user = Auth::user();

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        activity()
            ->causedBy($user)
            ->useLog('security')
            ->log('Password berhasil diubah');

        return redirect()
            ->route('government.account.password')
            ->with('success', 'Password berhasil diperbarui.');
    }

    /**
     * Halaman tampilan.
     */
    public function appearance()
    {
        $user = Auth::user();

        $preference = UserPreference::firstOrCreate(
            ['user_id' => $user->id],
            [
                'theme' => 'light',
                'font_size' => 'medium',
                'animations_enabled' => true,
            ]
        );

        return view(
            'government.account.appearance',
            compact('preference')
        );
    }

    /**
     * Update tampilan.
     */
    public function updateAppearance(Request $request)
    {
        $validated = $request->validate([
            'theme' => [
                'required',
                'in:light,dark,system',
            ],

            'font_size' => [
                'required',
                'in:small,medium,large',
            ],

            'animations_enabled' => [
                'required',
                'boolean',
            ],
        ]);

        $user = Auth::user();

        $preference = UserPreference::firstOrCreate(
            ['user_id' => $user->id],
            [
                'theme' => 'light',
                'font_size' => 'medium',
                'animations_enabled' => true,
            ]
        );

        $preference->update($validated);

        return redirect()
            ->route('government.account.appearance')
            ->with('success', 'Pengaturan tampilan berhasil diperbarui.');
    }

    /**
     * Halaman notifikasi.
     */
    public function notifications()
    {
        /** @var User $user */
        $user = Auth::user();

        $notifications = $user->notifications()
            ->latest()
            ->get();

        return view(
            'government.account.notifications',
            compact('notifications')
        );
    }

    /**
     * Halaman riwayat aktivitas.
     */
    public function activity(Request $request)
    {
        $user = Auth::user();

        $query = Activity::query()
            ->causedBy($user)
            ->latest('created_at');

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('event', 'like', "%{$search}%")
                    ->orWhere('log_name', 'like', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Module / Log Name
        |--------------------------------------------------------------------------
        */

        if ($request->filled('module')) {
            $query->where('log_name', $request->module);
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Event
        |--------------------------------------------------------------------------
        */

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Date
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $activities = $query
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Filter Options
        |--------------------------------------------------------------------------
        */

        $modules = Activity::query()
            ->causedBy($user)
            ->whereNotNull('log_name')
            ->where('log_name', '!=', '')
            ->distinct()
            ->orderBy('log_name')
            ->pluck('log_name');

        $events = Activity::query()
            ->causedBy($user)
            ->whereNotNull('event')
            ->where('event', '!=', '')
            ->distinct()
            ->orderBy('event')
            ->pluck('event');

        return view(
            'government.account.activity',
            compact(
                'activities',
                'modules',
                'events'
            )
        );
    }
}
