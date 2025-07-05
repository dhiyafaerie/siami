<?php

namespace App\Filament\Widgets;

use App\Models\Prodi;
use App\Models\Standard;
use App\Models\Faculty;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $countProdi = Prodi::count();
        $countFakultas = Faculty::count();
        $countStandar = Standard::count();
        
        return [
            Stat::make('Prodi', $countProdi . ' Prodi'),
            Stat::make('Fakultas', $countFakultas . ' Fakultas'),
            Stat::make('Standar', $countStandar . ' Standar'),
        ];
    }
}
