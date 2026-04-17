<?php

namespace App\Filament\Pages;

use App\Models\Cycle;
use App\Models\Nonconformity;
use App\Models\Standard;
use App\Notifications\KtsPerbaikanDiajukanNotification;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ProdiKts extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationGroup = 'AMI';
    protected static ?string $navigationLabel = 'KTS Prodi';
    protected static ?string $title = 'Daftar KTS';
    protected static string $view = 'filament.pages.prodi-kts';

    public static function canAccess(): bool
    {
        return Auth::user()->prodi->isNotEmpty();
    }

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $prodiId = $user->prodi->first()?->id;

        return $table
            ->query(
                Nonconformity::query()
                    ->with(['standard.cycle', 'auditor'])
                    ->where('prodis_id', $prodiId)
                    ->whereNotNull('standards_id')
            )
            ->columns([
                Tables\Columns\TextColumn::make('kts')
                    ->label('Kode KTS')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('kategori')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (?string $state) => Nonconformity::kategoriColor($state)),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => Nonconformity::statusColor($state))
                    ->formatStateUsing(fn (string $state) => Nonconformity::statusOptions()[$state] ?? $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('standard.nomor')
                    ->label('Standar')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(80)
                    ->wrap(),

                Tables\Columns\TextColumn::make('auditor.name')
                    ->label('Auditor'),

                Tables\Columns\TextColumn::make('deadline_perbaikan')
                    ->label('Deadline')
                    ->date('d M Y')
                    ->color(fn ($record) => $record->deadline_perbaikan?->isPast() && !$record->isDitutup()
                        ? 'danger' : null)
                    ->sortable(),

                Tables\Columns\TextColumn::make('tindakan_perbaikan')
                    ->label('Tindakan Perbaikan')
                    ->limit(60)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('alasan_penolakan')
                    ->label('Alasan Penolakan')
                    ->limit(60)
                    ->wrap()
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cycles_id')
                    ->label('Siklus')
                    ->options(Cycle::orderByDesc('year')->pluck('name', 'id'))
                    ->default(Cycle::where('is_active', true)->value('id'))
                    ->placeholder('Pilih Siklus')
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $standardIds = Standard::where('cycles_id', $data['value'])->pluck('id');
                            $query->whereIn('standards_id', $standardIds);
                        } else {
                            $query->whereRaw('0 = 1');
                        }
                    }),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(Nonconformity::statusOptions()),
            ])
            ->actions([
                Tables\Actions\Action::make('ajukan_perbaikan')
                    ->label('Ajukan Perbaikan')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('warning')
                    ->iconButton()
                    ->visible(fn (Nonconformity $record) => $record->isTerbuka() && $record->standard?->cycle?->is_active && !$record->standard?->cycle?->is_locked)
                    ->fillForm(fn (Nonconformity $record) => [
                        'tindakan_perbaikan' => $record->tindakan_perbaikan,
                    ])
                    ->form([
                        Forms\Components\Textarea::make('tindakan_perbaikan')
                            ->label('Uraian Tindakan Perbaikan')
                            ->required()
                            ->rows(5)
                            ->helperText('Jelaskan tindakan yang telah atau akan dilakukan untuk memperbaiki ketidaksesuaian ini.'),
                    ])
                    ->action(function (Nonconformity $record, array $data) {
                        $record->update([
                            'tindakan_perbaikan'    => $data['tindakan_perbaikan'],
                            'status'                => Nonconformity::STATUS_DALAM_PERBAIKAN,
                            'perbaikan_diajukan_at' => now(),
                            'alasan_penolakan'      => null,
                        ]);

                        if ($record->auditor) {
                            $record->auditor->notify(new KtsPerbaikanDiajukanNotification($record));
                        }

                        Notification::make()
                            ->title('Tindakan perbaikan berhasil diajukan')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }
}
