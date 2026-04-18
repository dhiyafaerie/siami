<?php

namespace App\Filament\Pages\AnalitikWidgets;

use App\Models\Cycle;
use App\Models\Nonconformity;
use App\Models\Prodi;
use App\Models\Standard;
use Filament\Widgets\BarChartWidget;

class KtsOverdueChart extends BarChartWidget
{
    use ScopesAnalitik;

    protected static ?string $heading = 'KTS Overdue per Prodi (Siklus Aktif)';
    protected static ?string $description = 'Jumlah KTS yang melewati deadline perbaikan dan belum ditutup';
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

        $today       = now()->toDateString();
        $prodiIds    = $prodis->pluck('id');

        $openKts = Nonconformity::whereIn('prodis_id', $prodiIds)
            ->whereIn('standards_id', $standardIds)
            ->where('status', '!=', Nonconformity::STATUS_DITUTUP)
            ->selectRaw(
                'prodis_id,
                 SUM(CASE WHEN deadline_perbaikan IS NOT NULL AND DATE(deadline_perbaikan) < ? THEN 1 ELSE 0 END) as overdue,
                 SUM(CASE WHEN deadline_perbaikan IS NULL OR DATE(deadline_perbaikan) >= ? THEN 1 ELSE 0 END) as aktif',
                [$today, $today]
            )
            ->groupBy('prodis_id')
            ->get()
            ->keyBy('prodis_id');

        $rows = $prodis->map(function (Prodi $prodi) use ($openKts) {
            $row = $openKts->get($prodi->id);
            return [
                'label'   => $prodi->programstudi,
                'overdue' => (int) ($row->overdue ?? 0),
                'aktif'   => (int) ($row->aktif ?? 0),
            ];
        })->sortByDesc('overdue')->values();

        return [
            'datasets' => [
                [
                    'label'           => 'Overdue',
                    'data'            => $rows->pluck('overdue')->toArray(),
                    'backgroundColor' => '#ef4444',
                    'borderColor'     => '#b91c1c',
                    'borderWidth'     => 1,
                    'borderRadius'    => 4,
                ],
                [
                    'label'           => 'Belum jatuh tempo',
                    'data'            => $rows->pluck('aktif')->toArray(),
                    'backgroundColor' => '#f59e0b',
                    'borderColor'     => '#b45309',
                    'borderWidth'     => 1,
                    'borderRadius'    => 4,
                ],
            ],
            'labels' => $rows->pluck('label')->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'top'],
            ],
            'scales' => [
                'x' => ['stacked' => true],
                'y' => [
                    'stacked'     => true,
                    'beginAtZero' => true,
                    'ticks'       => ['stepSize' => 1, 'precision' => 0],
                ],
            ],
        ];
    }
}
