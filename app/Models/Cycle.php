<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Cycle extends Model
{
    use HasFactory;

    public const ACTIVE_CACHE_KEY = 'cycle.active';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'is_locked' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Cycle $cycle) {
            if ($cycle->is_active) {
                static::where('is_active', true)
                    ->when($cycle->exists, fn ($q) => $q->where('id', '!=', $cycle->id))
                    ->update(['is_active' => false]);
            }
        });

        static::saved(fn () => Cache::forget(self::ACTIVE_CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::ACTIVE_CACHE_KEY));
    }

    public static function getActive(): ?self
    {
        return Cache::remember(
            self::ACTIVE_CACHE_KEY,
            now()->addMinutes(5),
            fn () => static::where('is_active', true)->first()
        );
    }

    public function isLocked(): bool
    {
        return (bool) $this->is_locked;
    }

    public function standards(): HasMany
    {
        return $this->hasMany(Standard::class, 'cycles_id');
    }
}
