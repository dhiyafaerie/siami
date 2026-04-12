<?php

namespace App\Filament\Pages;

use App\Models\Auditscore;
use App\Models\Cycle;
use App\Models\Prodi;
use Filament\Pages\Page;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class MultiCycleComparison extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Perbandingan Siklus';
    protected static ?string $navigationGroup = 'AMI';
    protected static ?string $title = 'Perbandingan Siklus';
    protected static string $view = 'filament.pages.multi-cycle-comparison';

    public function getViewData(): array
    {
        $cycles = Cycle::orderBy('year')->orderBy('id')->get();

        $scores = Auditscore::with(['prodi', 'standard.cycle'])->get();

        $grouped = $scores->groupBy('prodis_id');

        $rows = Prodi::all()->map(function (Prodi $prodi) use ($grouped, $cycles) {
            $prodiScores = $grouped->get($prodi->id, collect());

            $cycleAverages = $cycles->mapWithKeys(function (Cycle $cycle) use ($prodiScores) {
                $avg = $prodiScores
                    ->filter(fn ($s) => $s->standard?->cycles_id === $cycle->id)
                    ->avg('score');
                return [$cycle->id => $avg ? round($avg, 2) : null];
            });

            return [
                'prodi' => $prodi,
                'scores' => $cycleAverages,
            ];
        });

        return [
            'cycles' => $cycles,
            'rows' => $rows,
        ];
    }
}
