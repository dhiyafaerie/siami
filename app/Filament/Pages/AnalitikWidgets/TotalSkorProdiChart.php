<?php

namespace App\Filament\Pages\AnalitikWidgets;

use App\Models\Auditscore;
use App\Models\Cycle;
use App\Models\Prodi;
use Filament\Widgets\BarChartWidget;

class TotalSkorProdiChart extends BarChartWidget
{
    use ScopesAnalitik;

    protected static ?string $heading = 'Total Skor per Prodi (Siklus Aktif)';
    protected static ?string $description = 'Akumulasi nilai audit per program studi';
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $cycle = Cycle::where('is_active', true)->first();
        if (! $cycle) {
            return ['datasets' => [], 'labels' => []];
        }

        $scopedProdi = $this->scopedProdiIds();

        $query = Auditscore::query()
            ->join('standards', 'auditscores.standards_id', '=', 'standards.id')
            ->join('prodis', 'auditscores.prodis_id', '=', 'prodis.id')
            ->where('standards.cycles_id', $cycle->id)
            ->selectRaw('prodis.id as prodi_id, prodis.programstudi as nama, SUM(auditscores.score) as total')
            ->groupBy('prodis.id', 'prodis.programstudi');

        if ($scopedProdi !== null) {
            $query->whereIn('prodis.id', $scopedProdi);
        }

        $rows = $query->orderByDesc('total')->get();

        $palette = [
            '#6366f1', // indigo
            '#10b981', // emerald
            '#f59e0b', // amber
            '#ef4444', // red
            '#06b6d4', // cyan
            '#a855f7', // purple
            '#ec4899', // pink
            '#14b8a6', // teal
            '#f97316', // orange
            '#3b82f6', // blue
            '#84cc16', // lime
            '#eab308', // yellow
            '#8b5cf6', // violet
            '#22c55e', // green
            '#d946ef', // fuchsia
        ];

        $colors       = [];
        $borderColors = [];
        foreach ($rows as $i => $_) {
            $color          = $palette[$i % count($palette)];
            $colors[]       = $color . 'cc'; // ~80% opacity for fill
            $borderColors[] = $color;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Skor',
                    'data'  => $rows->pluck('total')->map(fn ($v) => (int) $v)->toArray(),
                    'backgroundColor' => $colors,
                    'borderColor'     => $borderColors,
                    'borderWidth'     => 2,
                    'borderRadius'    => 6,
                ],
            ],
            'labels' => $rows->pluck('nama')->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => ['beginAtZero' => true, 'ticks' => ['stepSize' => 10]],
            ],
        ];
    }
}
