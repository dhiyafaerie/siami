<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Nonconformity extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = [];

    const STATUS_TERBUKA          = 'terbuka';
    const STATUS_DALAM_PERBAIKAN  = 'dalam_perbaikan';
    const STATUS_DITUTUP          = 'ditutup';

    protected $casts = [
        'deadline_perbaikan'      => 'date',
        'perbaikan_diajukan_at'   => 'datetime',
        'verified_at'             => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function standard()
    {
        return $this->belongsTo(Standard::class, 'standards_id');
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodis_id');
    }

    public function auditor()
    {
        return $this->belongsTo(User::class, 'auditors_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isTerbuka(): bool
    {
        return $this->status === self::STATUS_TERBUKA;
    }

    public function isDalamPerbaikan(): bool
    {
        return $this->status === self::STATUS_DALAM_PERBAIKAN;
    }

    public function isDitutup(): bool
    {
        return $this->status === self::STATUS_DITUTUP;
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_TERBUKA         => 'Terbuka',
            self::STATUS_DALAM_PERBAIKAN => 'Dalam Perbaikan',
            self::STATUS_DITUTUP         => 'Ditutup',
        ];
    }

    public static function statusColor(string $status): string
    {
        return match ($status) {
            self::STATUS_TERBUKA         => 'danger',
            self::STATUS_DALAM_PERBAIKAN => 'warning',
            self::STATUS_DITUTUP         => 'success',
            default                      => 'gray',
        };
    }

    public static function kategoriOptions(): array
    {
        return [
            'Mayor'     => 'Mayor',
            'Minor'     => 'Minor',
            'Observasi' => 'Observasi',
        ];
    }

    public static function kategoriColor(?string $kategori): string
    {
        return match (strtolower($kategori ?? '')) {
            'mayor'              => 'danger',
            'minor'              => 'warning',
            'observasi', 'ob'    => 'info',
            default              => 'gray',
        };
    }
}
