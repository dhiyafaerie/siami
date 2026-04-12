<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Standard extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'deskriptor' => 'string',
    ];

    public function getDeskriptorAttribute(?string $value): ?string
    {
        if ($value === null) return null;
        return str_replace('&nbsp;', ' ', $value);
    }

    public function cycle()
    {
        return $this->belongsTo(Cycle::class, 'cycles_id');    
    }

    public function prodiattachment(): HasMany
    {
        return $this->hasMany(Prodiattachment::class, 'standards_id');
    }

    public function auditscore()
    {
        return $this->hasMany(Auditscore::class, 'standards_id');
    }
}
