<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auditor extends Model
{
    use HasFactory;

    protected $guarded = [];
    public function Faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculties_id');    
    }

    public function Prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodis_id');    
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');    
    }
}
