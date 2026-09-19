<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentPayment extends Model
{
    use HasFactory;

    protected $table = 'student_payments';

    protected $fillable = [
        'mahasiswa_id',
        'payment_type_id',
        'tahun_akademik_id',
        'invoice_number',
        'midtrans_order_id',
        'midtrans_token',
        'midtrans_payment_type',
        'amount',
        'paid_amount',
        'status',
        'payment_method',
        'payment_date',
        'confirmed_at',
        'confirmed_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'payment_date' => 'date',
            'confirmed_at' => 'datetime',
        ];
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(TahunAkademik::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(PaymentHistory::class)->orderBy('created_at', 'desc');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPartial(): bool
    {
        return $this->status === 'partial';
    }

    public function isUnpaid(): bool
    {
        return in_array($this->status, ['unpaid', 'partial']);
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->amount - (float) $this->paid_amount);
    }

    public function getPaidPercentageAttribute(): int
    {
        if ((float) $this->amount <= 0) {
            return 100;
        }

        return min(100, (int) round(((float) $this->paid_amount / (float) $this->amount) * 100));
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePartial($query)
    {
        return $query->where('status', 'partial');
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['unpaid', 'partial']);
    }

    public function scopeForFakultas($query, $fakultasId)
    {
        return $query->whereHas('mahasiswa.prodi', function ($q) use ($fakultasId) {
            $q->where('fakultas_id', $fakultasId);
        });
    }

    public function getSequenceOrder(): int
    {
        return $this->paymentType?->getSequenceOrder() ?? 999;
    }

    /**
     * Apakah ada transaksi Midtrans yang masih pending (token belum digunakan / menunggu konfirmasi).
     */
    public function hasPendingMidtrans(): bool
    {
        return $this->status === 'pending' && ! empty($this->midtrans_token);
    }

    /**
     * Apakah pembayaran ini dilakukan via Midtrans.
     */
    public function isPaidViaMidtrans(): bool
    {
        return $this->isPaid() && ! empty($this->midtrans_order_id);
    }

    /**
     * Apakah pembayaran ini dikonfirmasi secara manual oleh Admin/Kasir.
     */
    public function isPaidViaAdmin(): bool
    {
        return $this->isPaid() && ! $this->isPaidViaMidtrans();
    }
}
