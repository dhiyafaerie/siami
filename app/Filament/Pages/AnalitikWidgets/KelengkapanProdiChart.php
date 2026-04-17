<?php

namespace App\Filament\Pages\AnalitikWidgets;

use App\Models\Cycle;
use App\Models\Prodi;
use App\Models\Prodiattachment;
use App\Models\Standard;
use Filament\Widgets\BarChartWidget;

class KelengkapanProdiChart extends BarChartWidget
{
    use ScopesAnalitik;

    protected static ?string $heading = 'Kelengkapan Evidence per Prodi (Siklus Aktif)';
    protected static ?string $description = 'Persentase standar yang sudah diupload dokumennya';
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $cycle = Cycle::where('is_active', true)->first();
        if (! $cycle) {
            return ['datasets' => [], 'labels' => []];
        }

        $totalStandar = Standard::where('cycles_id', $cycle->id)->count();
        if ($totalStandar === 0) {
            return ['datasets' => [], 'labels' => []];
        }

        $scopedProdi = $this->scopedProdiIds();
        $prodiQuery  = Prodi::query()->orderBy('programstudi');
        if ($scopedProdi !== null) {
            $prodiQuery->whereIn('id', $scopedProdi);
        }
        $prodis = $prodiQuery->get();

        $standardIds = Standard::where('cycles_id', $cycle->id)->pluck('id');

        $rows = $prodis->map(function (Prodi $prodi) use ($standardIds, $totalStandar) {
            $uploaded = Prodiattachment::where('prodis_id', $prodi->id)
                ->whereIn('standards_id', $standardIds)
                ->distinct('standards_id')
                ->count('standards_id');

            return [
                'label'   => $prodi->programstudi,
                'percent' => round(($uploaded / $totalStandar) * 100, 1),
            ];
        })->sortByDesc('percent')->values();

        return [
            'datasets' => [
                [
                    'label' => 'Kelengkapan (%)',
                    'data'  => $rows->pluck('percent')->toArray(),
                    'backgroundColor' => '#10b981',
                ],
            ],
            'labels' => $rows->pluck('label')->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => ['min' => 0, 'max' => 100, 'ticks' => ['stepSize' => 20]],
            ],
        ];
    }
}
