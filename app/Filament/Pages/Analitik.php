<?php

namespace App\Filament\Pages;

use App\Filament\Pages\AnalitikWidgets\KelengkapanProdiChart;
use App\Filament\Pages\AnalitikWidgets\KepatuhanProdiChart;
use App\Filament\Pages\AnalitikWidgets\KtsOverdueChart;
use App\Filament\Pages\AnalitikWidgets\RadarPerStandarChart;
use App\Filament\Pages\AnalitikWidgets\TotalSkorProdiChart;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Analitik extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Analitik';
    protected static ?string $title = 'Analitik Audit';
    protected static ?int $navigationSort = 10;
    protected static string $view = 'filament.pages.analitik';

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user) return false;

        // Prodi users tidak punya akses analitik agregat
        if ($user->prodi && $user->prodi->isNotEmpty()) return false;

        return $user->hasRole('super_admin')
            || $user->hasRole('auditor')
            || $user->faculty !== null;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TotalSkorProdiChart::class,
            KelengkapanProdiChart::class,
            KepatuhanProdiChart::class,
            KtsOverdueChart::class,
            RadarPerStandarChart::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return [
            'default' => 1,
            'md'      => 2,
            'xl'      => 2,
        ];
    }
}
