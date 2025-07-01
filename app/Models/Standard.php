<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Standard extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function cycle()
    {
        return $this->belongsTo(Cycle::class, 'cycles_id');    
    }

    public function prodiattachment(): HasMany
    {
        return $this->hasMany(Prodiattachment::class, 'standards_id');
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodis_id');    
    }

    public function auditscore()
    {
        return $this->hasMany(Auditscore::class, 'standards_id');
    }
}
