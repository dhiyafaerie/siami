<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prodiattachment extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    public function standard()
    {
        return $this->belongsTo(Standard::class, 'standards_id');    
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodis_id');    
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');    
    }


}
