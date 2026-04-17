<?php

namespace App\Filament\Widgets;

use App\Models\Cycle;
use App\Models\Standard;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class StatusKelengkapanWidget extends BaseWidget
{
    protected static ?string $heading = 'Status Kelengkapan Dokumen Siklus Aktif';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        $user = Auth::user();
        $user->loadMissing('prodi');
        return $user->prodi->isNotEmpty();
    }

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $user->loadMissing('prodi');
        $currentProdiId = $user->prodi->first()?->id;
        $activeCycleId = Cycle::where('is_active', true)->value('id');

        return $table
            ->query(
                Standard::query()
                    ->where('cycles_id', $activeCycleId ?? 0)
                    ->with([
                        'prodiattachment' => fn ($q) => $q->where('prodis_id', $currentProdiId),
                        'auditscore' => fn ($q) => $q->where('prodis_id', $currentProdiId),
                    ])
            )
            ->emptyStateIcon('heroicon-o-document-magnifying-glass')
            ->emptyStateHeading('Belum ada standar dalam siklus aktif')
            ->emptyStateDescription('Hubungi admin jika siklus audit belum dibuat.')
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('nomor')
                    ->label('No.')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('gray')
                    ->width('80px'),

                Tables\Columns\TextColumn::make('deskriptor')
                    ->label('Deskriptor Standar')
                    ->html()
                    ->wrap()
                    ->limit(100),

                Tables\Columns\IconColumn::make('has_upload')
                    ->label('Dokumen')
                    ->alignCenter()
                    ->state(fn (Standard $record) => $record->prodiattachment->isNotEmpty())
                    ->boolean()
                    ->trueIcon('heroicon-o-document-check')
                    ->falseIcon('heroicon-o-document-minus')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->alignCenter()
                    ->badge()
                    ->state(function (Standard $record) {
                        $hasDinilai = $record->auditscore->isNotEmpty();
                        $hasUpload = $record->prodiattachment->isNotEmpty();

                        if ($hasDinilai) return 'Sudah Dinilai';
                        if ($hasUpload) return 'Menunggu Penilaian';
                        return 'Belum Upload';
                    })
                    ->color(fn (string $state) => match ($state) {
                        'Sudah Dinilai'       => 'success',
                        'Menunggu Penilaian'  => 'warning',
                        'Belum Upload'        => 'danger',
                    })
                    ->icon(fn (string $state) => match ($state) {
                        'Sudah Dinilai'       => 'heroicon-m-check-circle',
                        'Menunggu Penilaian'  => 'heroicon-m-clock',
                        'Belum Upload'        => 'heroicon-m-x-circle',
                    }),

                Tables\Columns\TextColumn::make('auditscore_value')
                    ->label('Nilai Audit')
                    ->alignCenter()
                    ->badge()
                    ->state(function (Standard $record) {
                        $scores = $record->auditscore->sortBy('keyword_index');
                        if ($scores->isEmpty()) return '-';
                        $keywords = array_filter(array_map('trim', explode(',', $record->keywords ?? '')));
                        $hasMultiple = count($keywords) > 1;
                        $letters = range('A', 'Z');
                        $scoreLabel = fn ($v) => match ($v) {
                            1 => '1 - Kurang', 2 => '2 - Cukup',
                            3 => '3 - Baik', 4 => '4 - Sangat Baik', default => '-',
                        };
                        if (!$hasMultiple) return $scoreLabel($scores->first()->score);
                        return $scores->values()->map(fn ($s, $i) =>
                            ($letters[$s->keyword_index ?? $i] ?? '') . '. ' . $scoreLabel($s->score)
                        )->implode(' | ');
                    })
                    ->color(function (Standard $record) {
                        $scores = $record->auditscore;
                        if ($scores->isEmpty()) return 'gray';
                        $avg = $scores->avg('score');
                        return match (true) {
                            $avg >= 3.5 => 'success',
                            $avg >= 2.5 => 'info',
                            $avg >= 1.5 => 'warning',
                            default     => 'danger',
                        };
                    }),
            ])
            ->paginated(false);
    }
}
