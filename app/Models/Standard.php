<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Standard extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function cycle()
    {
        return $this->belongsTo(Cycle::class, 'cycles_id');    
    }
}
