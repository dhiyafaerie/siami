<?php

namespace App\Filament\Pages;

use App\Models\Cycle;
use App\Models\Nonconformity;
use App\Models\Prodi;
use App\Models\Standard;
use App\Notifications\KtsDitutupNotification;
use App\Notifications\KtsPerbaikanDiajukanNotification;
use App\Notifications\KtsPerbaikanDitolakNotification;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AuditorKts extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationGroup = 'AMI';
    protected static ?string $navigationLabel = 'KTS Auditor';
    protected static ?string $title = 'Daftar KTS';
    protected static string $view = 'filament.pages.auditor-kts';

    public static function canAccess(): bool
    {
        return Auth::user()->hasRole('auditor');
    }

    public function table(Table $table): Table
    {
        $user = Auth::user();

        return $table
            ->query(
                Nonconformity::query()
                    ->with(['standard.cycle', 'prodi', 'auditor', 'verifiedBy'])
                    ->whereNotNull('standards_id')
                    ->where('auditors_id', $user->id)
            )
            ->columns([
                Tables\Columns\TextColumn::make('kts')
                    ->label('Kode KTS')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('kategori')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable()
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

                Tables\Columns\TextColumn::make('prodi.programstudi')
                    ->label('Program Studi')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(60)
                    ->wrap(),

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

                Tables\Columns\TextColumn::make('verified_at')
                    ->label('Ditutup Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cycles_id')
                    ->label('Siklus')
                    ->options(Cycle::orderByDesc('year')->pluck('name', 'id'))
                    ->default(Cycle::getActive()?->id)
                    ->placeholder('Pilih Siklus')
                    ->query(function (Builder $query, array $data) {
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

                Tables\Filters\SelectFilter::make('prodis_id')
                    ->label('Program Studi')
                    ->relationship('prodi', 'programstudi')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('kategori')
                    ->label('Kategori')
                    ->options(Nonconformity::kategoriOptions()),
            ])
            ->actions([
                // Edit detail temuan
                Tables\Actions\Action::make('edit_detail')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->iconButton()
                    ->visible(fn (Nonconformity $record) => !$record->isDitutup() && $record->standard?->cycle?->is_active && !$record->standard?->cycle?->is_locked)
                    ->fillForm(fn (Nonconformity $record) => [
                        'description'        => $record->description,
                        'prodis_id'          => $record->prodis_id,
                        'deadline_perbaikan' => $record->deadline_perbaikan,
                    ])
                    ->form([
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi Ketidaksesuaian')
                            ->rows(3)
                            ->required(),

                        Forms\Components\Select::make('prodis_id')
                            ->label('Program Studi')
                            ->options(Prodi::pluck('programstudi', 'id'))
                            ->searchable()
                            ->required(),

                        Forms\Components\DatePicker::make('deadline_perbaikan')
                            ->label('Deadline Perbaikan')
                            ->nullable(),
                    ])
                    ->action(function (Nonconformity $record, array $data) {
                        $record->update($data);

                        Notification::make()
                            ->title('KTS berhasil diperbarui')
                            ->success()
                            ->send();
                    }),

                // Verifikasi & tutup
                Tables\Actions\Action::make('verifikasi_tutup')
                    ->label('Verifikasi & Tutup')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->iconButton()
                    ->visible(fn (Nonconformity $record) => $record->isDalamPerbaikan() && $record->standard?->cycle?->is_active && !$record->standard?->cycle?->is_locked)
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Tindakan Perbaikan')
                    ->modalDescription('Apakah tindakan perbaikan yang diajukan sudah memadai?')
                    ->action(function (Nonconformity $record) use ($user) {
                        $record->update([
                            'status'      => Nonconformity::STATUS_DITUTUP,
                            'verified_at' => now(),
                            'verified_by' => $user->id,
                        ]);

                        if ($record->prodi?->user) {
                            $record->prodi->user->notify(new KtsDitutupNotification($record));
                        }

                        Notification::make()
                            ->title('KTS berhasil ditutup')
                            ->success()
                            ->send();
                    }),

                // Tolak perbaikan
                Tables\Actions\Action::make('tolak_perbaikan')
                    ->label('Tolak Perbaikan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->iconButton()
                    ->visible(fn (Nonconformity $record) => $record->isDalamPerbaikan() && $record->standard?->cycle?->is_active && !$record->standard?->cycle?->is_locked)
                    ->form([
                        Forms\Components\Textarea::make('alasan_penolakan')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3)
                            ->helperText('Jelaskan mengapa perbaikan belum memadai agar prodi dapat memperbaiki kembali.'),
                    ])
                    ->modalHeading('Tolak Tindakan Perbaikan')
                    ->modalDescription('Perbaikan akan ditolak dan KTS dikembalikan ke status Terbuka agar prodi dapat mengajukan ulang.')
                    ->action(function (Nonconformity $record, array $data) {
                        $record->update([
                            'status'            => Nonconformity::STATUS_TERBUKA,
                            'alasan_penolakan'  => $data['alasan_penolakan'],
                        ]);

                        if ($record->prodi?->user) {
                            $record->prodi->user->notify(new KtsPerbaikanDitolakNotification($record));
                        }

                        Notification::make()
                            ->title('Perbaikan ditolak, KTS dikembalikan ke Terbuka')
                            ->warning()
                            ->send();
                    }),

                // Buka kembali
                Tables\Actions\Action::make('buka_kembali')
                    ->label('Buka Kembali')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->iconButton()
                    ->visible(fn (Nonconformity $record) => $record->isDitutup() && $record->standard?->cycle?->is_active && !$record->standard?->cycle?->is_locked)
                    ->requiresConfirmation()
                    ->modalHeading('Buka Kembali KTS?')
                    ->modalDescription('KTS akan dikembalikan ke status Terbuka.')
                    ->action(function (Nonconformity $record) {
                        $record->update([
                            'status'      => Nonconformity::STATUS_TERBUKA,
                            'verified_at' => null,
                            'verified_by' => null,
                        ]);

                        Notification::make()
                            ->title('KTS dibuka kembali')
                            ->warning()
                            ->send();
                    }),

                // Hapus KTS
                Tables\Actions\Action::make('hapus')
                    ->label('Hapus')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->iconButton()
                    ->visible(fn (Nonconformity $record) => $record->standard?->cycle?->is_active && !$record->standard?->cycle?->is_locked)
                    ->requiresConfirmation()
                    ->modalHeading('Hapus KTS?')
                    ->modalDescription('KTS akan dihapus secara permanen.')
                    ->action(function (Nonconformity $record) {
                        $record->delete();

                        Notification::make()
                            ->title('KTS berhasil dihapus')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }
}
