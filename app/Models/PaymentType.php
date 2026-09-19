<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentType extends Model
{
    use HasFactory;

    protected $table = 'payment_types';

    protected $fillable = [
        'code',
        'name',
        'category',
        'semester',
        'description',
        'default_amount',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'semester' => 'integer',
            'default_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function payments(): HasMany
    {
        return $this->hasMany(StudentPayment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSemesterCategory($query)
    {
        return $query->where('category', 'semester');
    }

    /**
     * Get numerical sequence rank for ordered payment enforcement.
     * 0: Registration / Heregistrasi
     * 1-8: Semester 1-8
     * >8: Other / later semesters
     */
    public function getSequenceOrder(): int
    {
        $code = strtolower((string) $this->code);
        $name = strtolower((string) $this->name);

        if ($this->category === 'registration' || str_contains($code, 'registra') || str_contains($name, 'registrasi') || str_contains($code, 'daftar') || str_contains($name, 'pendaftaran')) {
            return 0;
        }

        if ($this->category === 'semester' && $this->semester !== null) {
            return (int) $this->semester;
        }

        if (preg_match('/semester[_\s-]*(\d+)/i', $code, $matches)) {
            return (int) $matches[1];
        }

        return 100 + (int) $this->id;
    }
}
