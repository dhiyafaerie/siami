<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Berkas extends Model
{
    use HasFactory;

    protected $table = 'berkas';
    protected $guarded = [];

    public const TARGET_AUDITOR = 'auditor';
    public const TARGET_PRODI   = 'prodi';

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function cycle()
    {
        return $this->belongsTo(Cycle::class, 'cycles_id');
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_id');
    }

    public function targetProdi()
    {
        return $this->belongsTo(Prodi::class, 'target_id');
    }

    public function getFileSizeFormattedAttribute(): string
    {
        if (! $this->file_size) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size  = (float) $this->file_size;
        $i     = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 2) . ' ' . $units[$i];
    }

    public function scopeForAuditor($query, int $userId)
    {
        return $query->where('target_role', self::TARGET_AUDITOR)
            ->where(fn ($q) => $q->whereNull('target_id')->orWhere('target_id', $userId));
    }

    public function scopeForProdi($query, array $prodiIds)
    {
        return $query->where('target_role', self::TARGET_PRODI)
            ->where(fn ($q) => $q->whereNull('target_id')->orWhereIn('target_id', $prodiIds));
    }
}
