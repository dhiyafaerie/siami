<?php

namespace App\Filament\Widgets;

use App\Models\Cycle;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class WelcomeWidget extends Widget
{
    protected static string $view = 'filament.widgets.welcome-widget';
    protected static ?int $sort = 0;
    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $user = Auth::user();
        $user->loadMissing('prodi', 'roles');
        $activeCycle = Cycle::where('is_active', true)->first();

        $role = match (true) {
            $user->hasRole('super_admin') => 'Super Admin',
            $user->hasRole('admin')       => 'Admin',
            $user->hasRole('auditor')     => 'Auditor',
            $user->prodi->isNotEmpty()    => 'Program Studi',
            default                       => 'Pengguna',
        };

        $subtitle = match (true) {
            $user->hasRole('super_admin'), $user->hasRole('admin') =>
                'Kelola dan pantau seluruh proses Audit Mutu Internal dari sini.',
            $user->hasRole('auditor') =>
                'Berikan penilaian audit yang objektif dan catat temuan dengan cermat.',
            $user->prodi->isNotEmpty() =>
                'Lengkapi dokumen standar dan pantau status penilaian audit Anda.',
            default =>
                'Selamat menggunakan Sistem Informasi Audit Mutu Internal.',
        };

        return [
            'name'        => $user->name,
            'role'        => $role,
            'subtitle'    => $subtitle,
            'date'        => now()->translatedFormat('l, d F Y'),
            'cycleName'   => $activeCycle?->name,
            'cycleLabel'  => $activeCycle ? 'Siklus Aktif' : 'Belum Ada Siklus Aktif',
        ];
    }
}
