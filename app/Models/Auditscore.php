<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auditscore extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Relationship to Standard
    public function standard()
    {
        return $this->belongsTo(Standard::class, 'standards_id');
    }

    // Relationship to User (Auditor)
    public function auditor()
    {
        return $this->belongsTo(User::class, 'auditors_id');
    }

    // Relationship to Prodi
    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodis_id');
    }

    // Accessor for score text
    public function getScoreTextAttribute()
    {
        return match($this->score) {
            1 => 'Kurang Cukup',
            2 => 'Kurang',
            3 => 'Cukup',
            4 => 'Sangat Cukup',
            default => 'Belum Dinilai'
        };
    }
}
