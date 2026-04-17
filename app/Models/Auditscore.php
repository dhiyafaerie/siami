<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Auditscore extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'score' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['score', 'notes', 'prodis_id', 'standards_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function standard()
    {
        return $this->belongsTo(Standard::class, 'standards_id');
    }

    public function auditor()
    {
        return $this->belongsTo(User::class, 'auditors_id');
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodis_id');
    }

    public function getScoreTextAttribute()
    {
        return match($this->score) {
            1 => 'Kurang',
            2 => 'Cukup',
            3 => 'Baik',
            4 => 'Sangat Baik',
            default => 'Belum Dinilai'
        };
    }
}
