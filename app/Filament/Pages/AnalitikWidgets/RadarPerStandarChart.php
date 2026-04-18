<?php

namespace App\Filament\Pages\AnalitikWidgets;

use App\Models\Auditscore;
use App\Models\Cycle;
use App\Models\Prodi;
use App\Models\Standard;
use Filament\Widgets\RadarChartWidget;
use Illuminate\Support\Facades\DB;

class RadarPerStandarChart extends RadarChartWidget
{
    use ScopesAnalitik;

    protected static ?string $heading = 'Radar Nilai Audit per Standar';
    protected static ?string $description = 'Rata-rata nilai audit per standar (skala 0–4). Maksimum 6 prodi teratas ditampilkan agar tetap terbaca.';
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $cycle = Cycle::getActive();
        if (! $cycle) {
            return ['datasets' => [], 'labels' => []];
        }

        $standards = Standard::where('cycles_id', $cycle->id)
            ->orderByRaw('CAST(nomor AS UNSIGNED), nomor')
            ->get(['id', 'nomor']);

        if ($standards->isEmpty()) {
            return ['datasets' => [], 'labels' => []];
        }

        $standardIds = $standards->pluck('id');

        $scopedProdi = $this->scopedProdiIds();
        $prodiQuery  = Prodi::query();
        if ($scopedProdi !== null) {
            $prodiQuery->whereIn('id', $scopedProdi);
        }
        $allProdis = $prodiQuery->get();
        if ($allProdis->isEmpty()) {
            return ['datasets' => [], 'labels' => []];
        }

        // Ambil 6 prodi teratas berdasar total skor agar radar tidak ramai.
        $totals = Auditscore::whereIn('standards_id', $standardIds)
            ->whereIn('prodis_id', $allProdis->pluck('id'))
            ->select('prodis_id', DB::raw('SUM(score) as total'))
            ->groupBy('prodis_id')
            ->pluck('total', 'prodis_id');

        $topProdis = $allProdis
            ->sortByDesc(fn (Prodi $p) => (int) ($totals[$p->id] ?? 0))
            ->take(6)
            ->values();

        // Hitung rata-rata per (prodi, standard) dalam satu query.
        $avgMap = Auditscore::whereIn('standards_id', $standardIds)
            ->whereIn('prodis_id', $topProdis->pluck('id'))
            ->select('prodis_id', 'standards_id', DB::raw('AVG(score) as avg_score'))
            ->groupBy('prodis_id', 'standards_id')
            ->get()
            ->groupBy('prodis_id');

        $palette = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#a855f7'];

        $datasets = $topProdis->map(function (Prodi $prodi, int $i) use ($standards, $avgMap, $palette) {
            $color  = $palette[$i % count($palette)];
            $scores = optional($avgMap->get($prodi->id))->keyBy('standards_id') ?? collect();

            $data = $standards->map(function (Standard $s) use ($scores) {
                $row = $scores->get($s->id);
                return $row ? round((float) $row->avg_score, 2) : 0;
            })->toArray();

            return [
                'label'                => $prodi->programstudi,
                'data'                 => $data,
                'backgroundColor'      => $color . '33',
                'borderColor'          => $color,
                'borderWidth'          => 2,
                'pointBackgroundColor' => $color,
                'pointRadius'          => 3,
            ];
        })->values()->toArray();

        return [
            'datasets' => $datasets,
            'labels'   => $standards->pluck('nomor')->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'r' => [
                    'min'         => 0,
                    'max'         => 4,
                    'ticks'       => ['stepSize' => 1],
                    'pointLabels' => ['font' => ['size' => 11]],
                ],
            ],
            'plugins' => [
                'legend' => ['position' => 'bottom'],
            ],
        ];
    }
}
