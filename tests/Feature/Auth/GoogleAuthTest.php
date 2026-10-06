<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('redirects to google oauth consent screen with correct parameters', function () {
    $response = $this->get(route('auth.google'));

    $response->assertRedirect();
    $targetUrl = $response->headers->get('Location');
    expect($targetUrl)->toContain('https://accounts.google.com/o/oauth2/v2/auth');
    expect($targetUrl)->toContain('client_id=');
    expect($targetUrl)->toContain('response_type=code');
    expect($targetUrl)->toContain('scope=openid+email+profile');
    expect(session('google_oauth_state'))->not->toBeNull();
});

it('fails callback if state is missing or invalid', function () {
    $response = $this->withSession(['google_oauth_state' => 'correct-state'])
        ->get(route('auth.google.callback', [
            'state' => 'wrong-state',
            'code' => 'test-code',
        ]));

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors('email');
});

it('logs in directly when google_id is already linked', function () {
    $user = User::factory()->create([
        'role' => 'mahasiswa',
        'google_id' => 'google-sub-12345',
        'google_email' => 'mhs@gmail.com',
    ]);

    Http::fake([
        'https://oauth2.googleapis.com/token' => Http::response([
            'access_token' => 'mock-access-token',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ], 200),
        'https://www.googleapis.com/oauth2/v3/userinfo' => Http::response([
            'sub' => 'google-sub-12345',
            'email' => 'mhs@gmail.com',
            'name' => 'Mahasiswa Test',
            'picture' => 'https://example.com/avatar.jpg',
        ], 200),
    ]);

    $response = $this->withSession(['google_oauth_state' => 'test-state'])
        ->get(route('auth.google.callback', [
            'state' => 'test-state',
            'code' => 'valid-code',
        ]));

    $response->assertRedirect(route('mahasiswa.dashboard', absolute: false));
    expect(Auth::check())->toBeTrue();
    expect(Auth::id())->toBe($user->id);
});

it('redirects to login with google data in session if not yet linked', function () {
    Http::fake([
        'https://oauth2.googleapis.com/token' => Http::response([
            'access_token' => 'mock-access-token',
        ], 200),
        'https://www.googleapis.com/oauth2/v3/userinfo' => Http::response([
            'sub' => 'new-google-sub-999',
            'email' => 'newuser@gmail.com',
            'name' => 'New User',
            'picture' => 'https://example.com/photo.jpg',
        ], 200),
    ]);

    $response = $this->withSession(['google_oauth_state' => 'test-state'])
        ->get(route('auth.google.callback', [
            'state' => 'test-state',
            'code' => 'valid-code',
        ]));

    $response->assertRedirect(route('login'));
    expect(session('google_link_data'))->toEqual([
        'id' => 'new-google-sub-999',
        'email' => 'newuser@gmail.com',
        'name' => 'New User',
        'avatar' => 'https://example.com/photo.jpg',
    ]);
    expect(Auth::check())->toBeFalse();
});

it('links google account when user logs in with siakad credentials', function () {
    $user = User::factory()->create([
        'email' => 'student@siakad.test',
        'password' => bcrypt('password123'),
        'role' => 'mahasiswa',
        'google_id' => null,
    ]);

    $response = $this->withSession([
        'google_link_data' => [
            'id' => 'linked-google-id-888',
            'email' => 'student.google@gmail.com',
            'name' => 'Student Google',
            'avatar' => 'https://example.com/avatar.jpg',
        ],
    ])->post(route('login'), [
        'email' => 'student@siakad.test',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('mahasiswa.dashboard', absolute: false));
    $user->refresh();
    expect($user->google_id)->toBe('linked-google-id-888');
    expect($user->google_email)->toBe('student.google@gmail.com');
    expect(session('google_link_data'))->toBeNull();
});

it('cancels google linking session', function () {
    $response = $this->withSession([
        'google_link_data' => ['id' => 'temp-id'],
    ])->post(route('auth.google.cancel'));

    $response->assertRedirect(route('login'));
    expect(session('google_link_data'))->toBeNull();
});

it('allows authenticated user to unlink google account', function () {
    $user = User::factory()->create([
        'google_id' => 'google-linked-id',
        'google_email' => 'linked@gmail.com',
    ]);

    $response = $this->actingAs($user)->delete(route('auth.google.unlink'));

    $response->assertRedirect();
    $user->refresh();
    expect($user->google_id)->toBeNull();
    expect($user->google_email)->toBeNull();
});
