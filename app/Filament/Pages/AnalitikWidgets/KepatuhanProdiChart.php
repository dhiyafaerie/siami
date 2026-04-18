<?php

namespace App\Filament\Pages\AnalitikWidgets;

use App\Models\Auditscore;
use App\Models\Cycle;
use App\Models\Prodi;
use App\Models\Standard;
use Filament\Widgets\BarChartWidget;
use Illuminate\Support\Facades\DB;

class KepatuhanProdiChart extends BarChartWidget
{
    use ScopesAnalitik;

    protected static ?string $heading = 'Persentase Kepatuhan per Prodi (Siklus Aktif)';
    protected static ?string $description = 'Proporsi standar dengan nilai rata-rata ≥ 3 (Baik/Sangat Baik) terhadap total standar yang telah dinilai';
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $cycle = Cycle::where('is_active', true)->first();
        if (! $cycle) {
            return ['datasets' => [], 'labels' => []];
        }

        $standardIds = Standard::where('cycles_id', $cycle->id)->pluck('id');
        if ($standardIds->isEmpty()) {
            return ['datasets' => [], 'labels' => []];
        }

        $scopedProdi = $this->scopedProdiIds();
        $prodiQuery  = Prodi::query()->orderBy('programstudi');
        if ($scopedProdi !== null) {
            $prodiQuery->whereIn('id', $scopedProdi);
        }
        $prodis = $prodiQuery->get(['id', 'programstudi']);

        $perProdiStandard = Auditscore::whereIn('standards_id', $standardIds)
            ->whereIn('prodis_id', $prodis->pluck('id'))
            ->select('prodis_id', 'standards_id', DB::raw('AVG(score) as avg_score'))
            ->groupBy('prodis_id', 'standards_id')
            ->get()
            ->groupBy('prodis_id');

        $rows = $prodis->map(function (Prodi $prodi) use ($perProdiStandard) {
            $perStandard = $perProdiStandard->get($prodi->id, collect());
            $total = $perStandard->count();
            if ($total === 0) {
                return ['label' => $prodi->programstudi, 'percent' => 0.0];
            }
            $compliant = $perStandard->filter(fn ($r) => (float) $r->avg_score >= 3)->count();
            return [
                'label'   => $prodi->programstudi,
                'percent' => round($compliant / $total * 100, 1),
            ];
        })->sortByDesc('percent')->values();

        return [
            'datasets' => [
                [
                    'label'           => 'Kepatuhan (%)',
                    'data'            => $rows->pluck('percent')->toArray(),
                    'backgroundColor' => '#3b82f6',
                    'borderColor'     => '#1d4ed8',
                    'borderWidth'     => 1,
                    'borderRadius'    => 6,
                ],
            ],
            'labels' => $rows->pluck('label')->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => [
                    'min'   => 0,
                    'max'   => 100,
                    'ticks' => ['stepSize' => 20],
                ],
            ],
        ];
    }
}
