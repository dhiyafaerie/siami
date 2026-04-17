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
        $prodis = $prodiQuery->get();

        $today = now()->toDateString();

        $rows = $prodis->map(function (Prodi $prodi) use ($standardIds, $today) {
            $base = Nonconformity::where('prodis_id', $prodi->id)
                ->whereIn('standards_id', $standardIds);

            $overdue = (clone $base)
                ->where('status', '!=', Nonconformity::STATUS_DITUTUP)
                ->whereNotNull('deadline_perbaikan')
                ->whereDate('deadline_perbaikan', '<', $today)
                ->count();

            $aktif = (clone $base)
                ->where('status', '!=', Nonconformity::STATUS_DITUTUP)
                ->where(function ($q) use ($today) {
                    $q->whereNull('deadline_perbaikan')
                      ->orWhereDate('deadline_perbaikan', '>=', $today);
                })
                ->count();

            return [
                'label'   => $prodi->programstudi,
                'overdue' => $overdue,
                'aktif'   => $aktif,
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
