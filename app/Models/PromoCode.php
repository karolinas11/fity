<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public static function findByCode(?string $code): ?self
    {
        $code = strtoupper(trim((string) $code));

        return $code === '' ? null : self::where('code', $code)->first();
    }

    /**
     * Razlog zbog kog kod trenutno ne moze da se iskoristi, ili null ako moze.
     */
    public function unavailableReason(): ?string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        if ($this->starts_at && now()->lt($this->starts_at)) {
            return 'not_started';
        }

        if ($this->ends_at && now()->gt($this->ends_at)) {
            return 'expired';
        }

        if ($this->max_redemptions !== null && $this->redemptions_count >= $this->max_redemptions) {
            return 'exhausted';
        }

        return null;
    }

    public function isRedeemable(): bool
    {
        return $this->unavailableReason() === null;
    }
}
