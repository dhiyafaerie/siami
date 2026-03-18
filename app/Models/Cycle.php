<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cycle extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function standards(): HasMany
    {
        return $this->hasMany(Standard::class, 'cycles_id');
    }
}
