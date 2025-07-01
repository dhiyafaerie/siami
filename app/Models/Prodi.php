<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prodi extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function Faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculties_id');    
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');    
    }

    // Relationship to AuditScores
    public function auditscore()
    {
        return $this->hasMany(Auditscore::class, 'prodis_id');
    }
}
