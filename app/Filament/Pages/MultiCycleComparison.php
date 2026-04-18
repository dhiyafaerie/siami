<?php

namespace App\Filament\Pages;

use App\Models\Auditscore;
use App\Models\Cycle;
use App\Models\Prodi;
use Filament\Pages\Page;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Illuminate\Support\Facades\DB;

class MultiCycleComparison extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Perbandingan Siklus';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $title = 'Perbandingan Siklus';
    protected static string $view = 'filament.pages.multi-cycle-comparison';

    public function getViewData(): array
    {
        $cycles = Cycle::orderBy('year')->orderBy('id')->get();

        $averages = Auditscore::query()
            ->join('standards', 'auditscores.standards_id', '=', 'standards.id')
            ->select('auditscores.prodis_id', 'standards.cycles_id', DB::raw('AVG(auditscores.score) as avg_score'))
            ->groupBy('auditscores.prodis_id', 'standards.cycles_id')
            ->get()
            ->groupBy('prodis_id');

        $rows = Prodi::orderBy('programstudi')->get()->map(function (Prodi $prodi) use ($averages, $cycles) {
            $byCycle = $averages->get($prodi->id, collect())->keyBy('cycles_id');

            $cycleAverages = $cycles->mapWithKeys(fn (Cycle $cycle) => [
                $cycle->id => isset($byCycle[$cycle->id])
                    ? round((float) $byCycle[$cycle->id]->avg_score, 2)
                    : null,
            ]);

            return [
                'prodi'  => $prodi,
                'scores' => $cycleAverages,
            ];
        });

        return [
            'cycles' => $cycles,
            'rows'   => $rows,
        ];
    }
}
