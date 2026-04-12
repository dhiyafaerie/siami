<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cycle extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'is_locked' => 'boolean',
    ];

    public function isLocked(): bool
    {
        return (bool) $this->is_locked;
    }

    public function standards(): HasMany
    {
        return $this->hasMany(Standard::class, 'cycles_id');
    }
}
