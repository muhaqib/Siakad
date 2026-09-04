<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Kelas;
use App\Models\JadwalKuliah;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Kirim notifikasi ke user
     */
    public function send(User $user, string $type, string $title, string $message, array $data = []): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Kirim notifikasi ke banyak user
     */
    public function sendToMany(Collection $users, string $type, string $title, string $message, array $data = []): int
    {
        $count = 0;
        foreach ($users as $user) {
            $this->send($user, $type, $title, $message, $data);
            $count++;
        }
        return $count;
    }

    /**
     * Kirim notifikasi perubahan jadwal ke semua mahasiswa di kelas
     */
    public function notifyJadwalChange(Kelas $kelas, JadwalKuliah $jadwal, array $changes): int
    {
        // Get all students enrolled in this class (via KRS)
        $mahasiswaList = $kelas->krsDetail()
            ->with('krs.mahasiswa.user')
            ->get()
            ->pluck('krs.mahasiswa.user')
            ->filter();

        if ($mahasiswaList->isEmpty()) {
            return 0;
        }

        // Build change description
        $changeDesc = [];
        if (isset($changes['hari'])) {
            $changeDesc[] = "Hari: {$changes['hari']['old']} → {$changes['hari']['new']}";
        }
        if (isset($changes['jam'])) {
            $changeDesc[] = "Jam: {$changes['jam']['old']} → {$changes['jam']['new']}";
        }
        if (isset($changes['ruangan'])) {
            $changeDesc[] = "Ruangan: {$changes['ruangan']['old']} → {$changes['ruangan']['new']}";
        }

        $title = "Perubahan Jadwal: {$kelas->mataKuliah->nama_mk}";
        $message = "Jadwal kelas {$kelas->nama_kelas} telah diubah.\n" . implode("\n", $changeDesc);

        $data = [
            'kelas_id' => $kelas->id,
            'jadwal_id' => $jadwal->id,
            'mata_kuliah' => $kelas->mataKuliah->nama_mk,
            'changes' => $changes,
        ];

        return $this->sendToMany($mahasiswaList, Notification::TYPE_JADWAL_CHANGE, $title, $message, $data);
    }

    /**
     * Get notifikasi untuk user
     */
    public function getForUser(User $user, int $limit = 20): Collection
    {
        return Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get jumlah notifikasi belum dibaca
     */
    public function getUnreadCount(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->unread()
            ->count();
    }

    /**
     * Tandai semua notifikasi sebagai dibaca
     */
    public function markAllAsRead(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Tandai satu notifikasi sebagai dibaca
     */
    public function markAsRead(Notification $notification): void
    {
        $notification->markAsRead();
    }
}
