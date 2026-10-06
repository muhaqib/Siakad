<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    /**
     * Redirect user to Google OAuth consent screen.
     */
    public function redirect(): RedirectResponse
    {
        $clientId = config('services.google.client_id');
        $redirectUri = config('services.google.redirect');

        if (! $clientId || ! config('services.google.client_secret')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google OAuth belum dikonfigurasi pada server.',
            ]);
        }

        $state = Str::random(40);
        session(['google_oauth_state' => $state]);

        $params = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'offline',
            'prompt' => 'select_account',
        ]);

        return redirect()->away("https://accounts.google.com/o/oauth2/v2/auth?{$params}");
    }

    /**
     * Handle callback from Google OAuth.
     */
    public function callback(Request $request): RedirectResponse
    {
        if ($request->has('error')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Login Google dibatalkan: '.$request->get('error_description', $request->get('error')),
            ]);
        }

        $sessionState = $request->session()->pull('google_oauth_state');
        if (! $sessionState || $sessionState !== $request->state) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sesi Google OAuth tidak valid atau telah kedaluwarsa. Silakan coba lagi.',
            ]);
        }

        $code = $request->code;
        if (! $code) {
            return redirect()->route('login')->withErrors([
                'email' => 'Kode otorisasi Google tidak ditemukan.',
            ]);
        }

        // Exchange authorization code for access token
        $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => config('services.google.redirect'),
        ]);

        if (! $tokenResponse->successful()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Gagal mendapatkan token dari Google: '.($tokenResponse->json('error_description') ?? $tokenResponse->json('error') ?? 'Request failed'),
            ]);
        }

        $accessToken = $tokenResponse->json('access_token');

        // Fetch user profile from Google
        $userResponse = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v3/userinfo');

        if (! $userResponse->successful()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Gagal mengambil data profil pengguna dari Google.',
            ]);
        }

        $googleUser = $userResponse->json();
        $googleId = $googleUser['sub'] ?? null;
        $googleEmail = $googleUser['email'] ?? null;
        $googleName = $googleUser['name'] ?? null;
        $googleAvatar = $googleUser['picture'] ?? null;

        if (! $googleId) {
            return redirect()->route('login')->withErrors([
                'email' => 'Data identitas Google tidak valid.',
            ]);
        }

        // Check if user already linked
        $user = User::where('google_id', $googleId)->first();

        if ($user) {
            // Already linked -> Login directly!
            Auth::login($user, remember: true);
            $request->session()->regenerate();

            $redirectRoute = match ($user->role) {
                'mahasiswa' => 'mahasiswa.dashboard',
                'dosen' => 'dosen.dashboard',
                'superadmin', 'admin_fakultas' => 'admin.dashboard',
                default => 'dashboard',
            };

            return redirect()->intended(route($redirectRoute, absolute: false))
                ->with('success', 'Selamat datang kembali, '.$user->name.'!');
        }

        // Not yet linked -> Store Google info in session and redirect to login to link credentials
        $request->session()->put('google_link_data', [
            'id' => $googleId,
            'email' => $googleEmail,
            'name' => $googleName,
            'avatar' => $googleAvatar,
        ]);

        return redirect()->route('login')->with('status', 'Akun Google ('.$googleEmail.') berhasil diverifikasi. Masukkan email & password akun SIAKAD Anda untuk menghubungkan akun.');
    }

    /**
     * Cancel pending Google linking session.
     */
    public function cancel(Request $request): RedirectResponse
    {
        $request->session()->forget('google_link_data');

        return redirect()->route('login')->with('status', 'Penautan akun Google dibatalkan.');
    }

    /**
     * Unlink Google account for authenticated user.
     */
    public function unlink(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->update([
            'google_id' => null,
            'google_email' => null,
            'google_avatar' => null,
        ]);

        return redirect()->back()->with('success', 'Akun Google berhasil diputuskan.');
    }
}
