<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        // Cek jika sedang dalam proses menautkan akun Google
        if ($googleData = $request->session()->get('google_link_data')) {
            $existing = User::where('google_id', $googleData['id'])
                ->where('id', '!=', $user->id)
                ->first();

            if ($existing) {
                Auth::guard('web')->logout();

                return redirect()->route('login')->withErrors([
                    'email' => 'Akun Google tersebut sudah ditautkan ke pengguna lain.',
                ]);
            }

            $user->update([
                'google_id' => $googleData['id'],
                'google_email' => $googleData['email'] ?? null,
                'google_avatar' => $googleData['avatar'] ?? null,
            ]);

            $request->session()->forget('google_link_data');
            $request->session()->regenerate();

            $redirectRoute = match ($user->role) {
                'mahasiswa' => 'mahasiswa.dashboard',
                'dosen' => 'dosen.dashboard',
                'superadmin', 'admin_fakultas' => 'admin.dashboard',
                default => 'dashboard',
            };

            return redirect()->intended(route($redirectRoute, absolute: false))
                ->with('success', 'Akun Google ('.$googleData['email'].') berhasil ditautkan! Anda sekarang dapat login menggunakan Google.');
        }

        $request->session()->regenerate();

        // Redirect berdasarkan role
        $redirectRoute = match ($user->role) {
            'mahasiswa' => 'mahasiswa.dashboard',
            'dosen' => 'dosen.dashboard',
            'superadmin', 'admin_fakultas' => 'admin.dashboard',
            default => 'dashboard',
        };

        return redirect()->intended(route($redirectRoute, absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
