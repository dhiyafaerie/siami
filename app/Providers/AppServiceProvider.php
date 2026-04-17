<?php

namespace App\Providers;

use App\Filament\Pages\AnalitikWidgets\KelengkapanProdiChart;
use App\Filament\Pages\AnalitikWidgets\KepatuhanProdiChart;
use App\Filament\Pages\AnalitikWidgets\KtsOverdueChart;
use App\Filament\Pages\AnalitikWidgets\RadarPerStandarChart;
use App\Filament\Pages\AnalitikWidgets\TotalSkorProdiChart;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register Analitik chart widgets as Livewire components.
        // They live outside app/Filament/Widgets/ so they don't pollute the dashboard.
        Livewire::component('app.filament.pages.analitik-widgets.total-skor-prodi-chart', TotalSkorProdiChart::class);
        Livewire::component('app.filament.pages.analitik-widgets.kelengkapan-prodi-chart', KelengkapanProdiChart::class);
        Livewire::component('app.filament.pages.analitik-widgets.kepatuhan-prodi-chart', KepatuhanProdiChart::class);
        Livewire::component('app.filament.pages.analitik-widgets.kts-overdue-chart', KtsOverdueChart::class);
        Livewire::component('app.filament.pages.analitik-widgets.radar-per-standar-chart', RadarPerStandarChart::class);
    }
}
