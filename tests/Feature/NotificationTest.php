<?php

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'mahasiswa']);
    $this->otherUser = User::factory()->create(['role' => 'mahasiswa']);
});

it('requires authentication to view notifications', function () {
    $response = $this->get(route('notifications.index'));
    $response->assertRedirect('/login');
});

it('displays notifications for authenticated user', function () {
    Notification::create([
        'user_id' => $this->user->id,
        'type' => Notification::TYPE_KRS_APPROVED,
        'title' => 'KRS Disetujui',
        'message' => 'KRS Anda untuk semester ini telah disetujui.',
    ]);

    $response = $this->actingAs($this->user)->get(route('notifications.index'));

    $response->assertSuccessful();
    $response->assertSee('KRS Disetujui');
    $response->assertSee('KRS Anda untuk semester ini telah disetujui.');
});

it('allows user to delete their own notification', function () {
    $notif = Notification::create([
        'user_id' => $this->user->id,
        'type' => Notification::TYPE_JADWAL_CHANGE,
        'title' => 'Jadwal Berubah',
        'message' => 'Jadwal kuliah Fiqih diubah.',
    ]);

    $response = $this->actingAs($this->user)->delete(route('notifications.destroy', $notif));

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Notifikasi berhasil dihapus');
    $this->assertDatabaseMissing('notifications', ['id' => $notif->id]);
});

it('prevents user from deleting other users notification', function () {
    $otherNotif = Notification::create([
        'user_id' => $this->otherUser->id,
        'type' => Notification::TYPE_JADWAL_CHANGE,
        'title' => 'Jadwal Rahasia',
        'message' => 'Pesan untuk user lain.',
    ]);

    $response = $this->actingAs($this->user)->delete(route('notifications.destroy', $otherNotif));

    $response->assertForbidden();
    $this->assertDatabaseHas('notifications', ['id' => $otherNotif->id]);
});

it('allows user to clear all their notifications', function () {
    Notification::create([
        'user_id' => $this->user->id,
        'type' => Notification::TYPE_KRS_APPROVED,
        'title' => 'Notif 1',
        'message' => 'Pesan 1',
    ]);

    Notification::create([
        'user_id' => $this->user->id,
        'type' => Notification::TYPE_NILAI_UPDATED,
        'title' => 'Notif 2',
        'message' => 'Pesan 2',
    ]);

    $otherNotif = Notification::create([
        'user_id' => $this->otherUser->id,
        'type' => Notification::TYPE_KRS_APPROVED,
        'title' => 'Notif Orang Lain',
        'message' => 'Jangan dihapus',
    ]);

    $response = $this->actingAs($this->user)->delete(route('notifications.clear-all'));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('notifications', ['user_id' => $this->user->id]);
    $this->assertDatabaseHas('notifications', ['id' => $otherNotif->id]);
});

it('supports deleting notification via json request', function () {
    $notif = Notification::create([
        'user_id' => $this->user->id,
        'type' => Notification::TYPE_JADWAL_CHANGE,
        'title' => 'Jadwal Berubah',
        'message' => 'Jadwal kuliah Fiqih diubah.',
    ]);

    $response = $this->actingAs($this->user)->deleteJson(route('notifications.destroy', $notif));

    $response->assertSuccessful();
    $response->assertJson(['success' => true]);
    $this->assertDatabaseMissing('notifications', ['id' => $notif->id]);
});
