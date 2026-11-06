<?php

use App\Models\User;

it('shows a single animated notification on admin pages', function (string $route, string $type) {
    $admin = User::factory()->create(['role' => 'superadmin']);
    $message = 'Data pengujian berhasil diproses.';

    $response = $this->actingAs($admin)
        ->withSession([$type => $message])
        ->get(route($route));

    $response->assertSuccessful()
        ->assertSee('data-flash-notification', false)
        ->assertSee('x-transition:leave', false)
        ->assertSee('5000', false)
        ->assertSee('Tutup notifikasi');

    expect(substr_count($response->getContent(), $message))->toBe(1);
})->with([
    'users success' => ['admin.users.index', 'success'],
    'students success' => ['admin.mahasiswa.index', 'success'],
    'lecturers success' => ['admin.dosen.index', 'success'],
    'academic years success' => ['admin.tahun-akademik.index', 'success'],
    'payment types success' => ['admin.payment-types.index', 'success'],
    'payment types error' => ['admin.payment-types.index', 'error'],
]);

it('does not render a notification without a flash message', function () {
    $admin = User::factory()->create(['role' => 'superadmin']);

    $this->actingAs($admin)->get(route('admin.users.index'))
        ->assertSuccessful()
        ->assertDontSee('data-flash-notification', false);
});

it('prioritizes errors when both flash statuses exist and escapes the message', function () {
    $admin = User::factory()->create(['role' => 'superadmin']);

    $response = $this->actingAs($admin)
        ->withSession(['success' => 'Success should be hidden', 'error' => '<script>alert(1)</script>'])
        ->get(route('admin.users.index'));

    $response->assertSuccessful()
        ->assertDontSee('Success should be hidden')
        ->assertSee('<script>alert(1)</script>')
        ->assertDontSee('<script>alert(1)</script>', false);

    expect(substr_count($response->getContent(), 'data-flash-notification'))->toBe(1);
});
