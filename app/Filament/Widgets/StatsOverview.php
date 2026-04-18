<?php

namespace App\Filament\Widgets;

use App\Models\Auditscore;
use App\Models\Auditor;
use App\Models\Cycle;
use App\Models\Faculty;
use App\Models\Nonconformity;
use App\Models\Prodi;
use App\Models\Prodiattachment;
use App\Models\Standard;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = Auth::user();
        $user->loadMissing('prodi', 'roles');

        // Prodi user
        if ($user->prodi->isNotEmpty()) {
            $currentProdiId = $user->prodi->first()->id;
            $activeCycle = Cycle::getActive();
            $activeCycleStandardIds = $activeCycle
                ? Standard::where('cycles_id', $activeCycle->id)->pluck('id')
                : collect();
            $totalStandar = $activeCycleStandardIds->count();
            $sudahUpload = Prodiattachment::where('prodis_id', $currentProdiId)
                ->whereIn('standards_id', $activeCycleStandardIds)->count();
            $belumUpload = max(0, $totalStandar - $sudahUpload);
            $avgScore = Auditscore::where('prodis_id', $currentProdiId)
                ->whereIn('standards_id', $activeCycleStandardIds)->avg('score');
            $persen = $totalStandar > 0 ? round(($sudahUpload / $totalStandar) * 100) : 0;

            $uploadChart = Prodiattachment::where('prodis_id', $currentProdiId)
                ->whereIn('standards_id', $activeCycleStandardIds)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
                ->groupBy('date')->orderBy('date')->limit(7)
                ->pluck('total')->toArray();

            return [
                Stat::make('Dokumen Diupload', $sudahUpload . ' / ' . $totalStandar)
                    ->description($persen . '% standar telah dilengkapi')
                    ->chart(count($uploadChart) > 1 ? $uploadChart : [0, $sudahUpload])
                    ->color('success'),

                Stat::make('Belum Dilengkapi', $belumUpload . ' Standar')
                    ->description($belumUpload > 0 ? 'Segera lengkapi dokumen' : 'Semua standar telah diisi')
                    ->chart([$totalStandar, max(0, $totalStandar - 1), $sudahUpload + 1, $sudahUpload])
                    ->color($belumUpload > 0 ? 'danger' : 'success'),

                Stat::make('Nilai Rata-rata', $avgScore ? number_format($avgScore, 2) . ' / 4' : 'Belum dinilai')
                    ->description('Rata-rata skor audit Anda')
                    ->chart($avgScore ? [1, 2, $avgScore * 0.8, $avgScore] : [0, 0, 0, 0])
                    ->color('info'),

                Stat::make('Siklus Aktif', $activeCycle?->name ?? 'Tidak ada')
                    ->description('Periode audit yang sedang berjalan')
                    ->color('warning'),
            ];
        }

        // Auditor user
        if ($user->hasRole('auditor')) {
            $prodiDitugaskan = Auditor::where('users_id', $user->id)->count();
            $activeCycle = Cycle::getActive();
            $activeCycleStandardIds = $activeCycle
                ? Standard::where('cycles_id', $activeCycle->id)->pluck('id')
                : collect();
            $standarDinilai = Auditscore::where('auditors_id', $user->id)
                ->whereIn('standards_id', $activeCycleStandardIds)
                ->distinct('standards_id')->count('standards_id');
            $totalKts = Nonconformity::where('auditors_id', $user->id)
                ->whereIn('standards_id', $activeCycleStandardIds)->count();

            $scoreChart = Auditscore::where('auditors_id', $user->id)
                ->whereIn('standards_id', $activeCycleStandardIds)
                ->selectRaw('DATE(created_at) as date, AVG(score) as avg_score')
                ->groupBy('date')->orderBy('date')->limit(7)
                ->pluck('avg_score')->map(fn ($v) => round($v, 1))->toArray();

            return [
                Stat::make('Prodi Ditugaskan', $prodiDitugaskan . ' Prodi')
                    ->description('Program studi yang Anda tangani')
                    ->chart([0, intval($prodiDitugaskan * 0.5), $prodiDitugaskan])
                    ->color('info'),

                Stat::make('Standar Dinilai', $standarDinilai . ' Standar')
                    ->description('Standar yang telah diberi nilai')
                    ->chart(count($scoreChart) > 1 ? $scoreChart : [0, $standarDinilai])
                    ->color('success'),

                Stat::make('Total KTS', $totalKts . ' Temuan')
                    ->description('Ketidaksesuaian yang dicatat')
                    ->chart([0, intval($totalKts * 0.4), intval($totalKts * 0.7), $totalKts])
                    ->color($totalKts > 0 ? 'warning' : 'success'),

                Stat::make('Siklus Aktif', $activeCycle?->name ?? 'Tidak ada')
                    ->description('Periode audit berjalan')
                    ->color('gray'),
            ];
        }

        // Admin / super_admin
        $activeCycle = Cycle::getActive();
        $activeCycleStandardIds = $activeCycle
            ? Standard::where('cycles_id', $activeCycle->id)->pluck('id')
            : collect();
        $totalProdi = Prodi::count();
        $totalFakultas = Faculty::count();
        $totalStandar = $activeCycleStandardIds->count();
        $totalKts = Nonconformity::whereIn('standards_id', $activeCycleStandardIds)->count();
        $prodiSudahUpload = Prodiattachment::whereIn('standards_id', $activeCycleStandardIds)
            ->distinct('prodis_id')->count('prodis_id');

        $ktsChart = Nonconformity::whereIn('standards_id', $activeCycleStandardIds)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')->orderBy('date')->limit(7)
            ->pluck('total')->toArray();

        return [
            Stat::make('Total Prodi', $totalProdi . ' Prodi')
                ->description($totalFakultas . ' Fakultas terdaftar')
                ->chart([intval($totalProdi * 0.6), intval($totalProdi * 0.8), $totalProdi])
                ->color('success'),

            Stat::make('Total Standar', $totalStandar . ' Standar')
                ->description('Siklus aktif')
                ->chart([intval($totalStandar * 0.5), intval($totalStandar * 0.75), $totalStandar])
                ->color('info'),

            Stat::make('Total KTS', $totalKts . ' Temuan')
                ->description('Ketidaksesuaian seluruh prodi')
                ->chart(count($ktsChart) > 1 ? $ktsChart : [0, intval($totalKts * 0.5), $totalKts])
                ->color($totalKts > 0 ? 'danger' : 'success'),

            Stat::make('Siklus Aktif', $activeCycle?->name ?? 'Tidak ada')
                ->description($prodiSudahUpload . ' prodi sudah upload dokumen')
                ->chart([$prodiSudahUpload, $totalProdi])
                ->color('primary'),
        ];
    }
}
