<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NonconformityResource\Pages;
use App\Models\Nonconformity;
use App\Models\User;
use App\Notifications\KtsDitutupNotification;
use App\Notifications\KtsPerbaikanDiajukanNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class NonconformityResource extends Resource
{
    protected static ?string $model = Nonconformity::class;
    protected static ?string $navigationGroup = "AMI";
    protected static ?string $navigationLabel = "KTS";
    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';
    protected static ?string $pluralModelLabel = 'Ketidaksesuaian';
    protected static ?string $title = 'Ketidaksesuaian';

    public static function form(Form $form): Form
    {
        $isAuditor = Auth::user()->hasRole('auditor');
        $isProdi   = Auth::user()->prodi->isNotEmpty();

        return $form
            ->schema([
                Forms\Components\Section::make('Informasi KTS')
                    ->schema([
                        Forms\Components\TextInput::make('kts')
                            ->label('Kode KTS')
                            ->required()
                            ->maxLength(255)
                            ->disabled($isProdi),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(Nonconformity::statusOptions())
                            ->default(Nonconformity::STATUS_TERBUKA)
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi Ketidaksesuaian')
                            ->rows(3)
                            ->disabled($isProdi)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('standards_id')
                            ->label('Standar')
                            ->relationship('standard', 'nomor')
                            ->searchable()
                            ->preload()
                            ->disabled($isProdi)
                            ->nullable(),

                        Forms\Components\Select::make('prodis_id')
                            ->label('Program Studi')
                            ->relationship('prodi', 'programstudi')
                            ->searchable()
                            ->preload()
                            ->disabled($isProdi)
                            ->nullable(),

                        Forms\Components\Select::make('auditors_id')
                            ->label('Auditor')
                            ->options(fn () => User::role('auditor')->pluck('name', 'id'))
                            ->searchable()
                            ->default(fn () => $isAuditor ? Auth::id() : null)
                            ->disabled($isAuditor || $isProdi)
                            ->dehydrated()
                            ->nullable(),

                        Forms\Components\DatePicker::make('deadline_perbaikan')
                            ->label('Deadline Perbaikan')
                            ->disabled($isProdi)
                            ->nullable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Tindakan Perbaikan')
                    ->schema([
                        Forms\Components\Textarea::make('tindakan_perbaikan')
                            ->label('Uraian Tindakan Perbaikan')
                            ->helperText('Diisi oleh Prodi sebagai respons atas temuan KTS.')
                            ->rows(4)
                            ->disabled($isAuditor)
                            ->columnSpanFull(),
                    ])
                    ->collapsed(fn ($record) => $record === null || empty($record?->tindakan_perbaikan)),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user    = Auth::user();
        $isProdi = $user->prodi->isNotEmpty();

        return $table
            ->query(
                Nonconformity::query()
                    ->with(['standard', 'prodi', 'auditor', 'verifiedBy'])
                    ->when($isProdi, function ($q) use ($user) {
                        $prodiId = $user->prodi->first()->id;
                        $q->where('prodis_id', $prodiId);
                    })
                    ->when($user->hasRole('auditor'), function ($q) use ($user) {
                        $q->where('auditors_id', $user->id);
                    })
            )
            ->columns([
                Tables\Columns\TextColumn::make('kts')
                    ->label('Kode KTS')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => Nonconformity::statusColor($state))
                    ->formatStateUsing(fn (string $state) => Nonconformity::statusOptions()[$state] ?? $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(60)
                    ->wrap(),

                Tables\Columns\TextColumn::make('standard.nomor')
                    ->label('Standar')
                    ->sortable(),

                Tables\Columns\TextColumn::make('prodi.programstudi')
                    ->label('Program Studi')
                    ->sortable(),

                Tables\Columns\TextColumn::make('auditor.name')
                    ->label('Auditor'),

                Tables\Columns\TextColumn::make('deadline_perbaikan')
                    ->label('Deadline')
                    ->date('d M Y')
                    ->color(fn ($record) => $record->deadline_perbaikan && $record->deadline_perbaikan->isPast() && !$record->isDitutup()
                        ? 'danger' : null)
                    ->sortable(),

                Tables\Columns\TextColumn::make('verified_at')
                    ->label('Ditutup Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('verifiedBy.name')
                    ->label('Ditutup Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(Nonconformity::statusOptions()),

                Tables\Filters\SelectFilter::make('prodis_id')
                    ->label('Program Studi')
                    ->relationship('prodi', 'programstudi')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => !$isProdi),

                Tables\Filters\SelectFilter::make('standards_id')
                    ->label('Standar')
                    ->relationship('standard', 'nomor')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                // Prodi: ajukan tindakan perbaikan (saat status terbuka)
                Tables\Actions\Action::make('ajukan_perbaikan')
                    ->label('Ajukan Perbaikan')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('warning')
                    ->visible(fn (Nonconformity $record) => $isProdi && $record->isTerbuka())
                    ->form([
                        Forms\Components\Textarea::make('tindakan_perbaikan')
                            ->label('Uraian Tindakan Perbaikan')
                            ->required()
                            ->rows(5)
                            ->helperText('Jelaskan tindakan yang telah atau akan dilakukan untuk memperbaiki ketidaksesuaian ini.'),
                    ])
                    ->fillForm(fn (Nonconformity $record) => [
                        'tindakan_perbaikan' => $record->tindakan_perbaikan,
                    ])
                    ->action(function (Nonconformity $record, array $data) {
                        $record->update([
                            'tindakan_perbaikan'    => $data['tindakan_perbaikan'],
                            'status'                => Nonconformity::STATUS_DALAM_PERBAIKAN,
                            'perbaikan_diajukan_at' => now(),
                        ]);

                        // Notify auditor
                        if ($record->auditor) {
                            $record->auditor->notify(new KtsPerbaikanDiajukanNotification($record));
                        }

                        Notification::make()
                            ->title('Tindakan perbaikan berhasil diajukan')
                            ->success()
                            ->send();
                    }),

                // Auditor: verifikasi dan tutup (saat status dalam_perbaikan)
                Tables\Actions\Action::make('verifikasi_tutup')
                    ->label('Verifikasi & Tutup')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Nonconformity $record) => $user->hasRole('auditor') && $record->isDalamPerbaikan())
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Tindakan Perbaikan')
                    ->modalDescription('Apakah tindakan perbaikan yang diajukan sudah memadai? KTS akan ditutup setelah dikonfirmasi.')
                    ->action(function (Nonconformity $record) use ($user) {
                        $record->update([
                            'status'      => Nonconformity::STATUS_DITUTUP,
                            'verified_at' => now(),
                            'verified_by' => $user->id,
                        ]);

                        // Notify prodi user
                        if ($record->prodi?->user) {
                            $record->prodi->user->notify(new KtsDitutupNotification($record));
                        }

                        Notification::make()
                            ->title('KTS berhasil ditutup')
                            ->success()
                            ->send();
                    }),

                // Auditor: buka kembali KTS yang sudah ditutup
                Tables\Actions\Action::make('buka_kembali')
                    ->label('Buka Kembali')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn (Nonconformity $record) => $user->hasRole('auditor') && $record->isDitutup())
                    ->requiresConfirmation()
                    ->modalHeading('Buka Kembali KTS?')
                    ->modalDescription('KTS akan dikembalikan ke status Terbuka. Tindakan perbaikan sebelumnya tetap tersimpan.')
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

                Tables\Actions\EditAction::make()
                    ->visible(fn (Nonconformity $record) => !$record->isDitutup() || $user->hasRole('admin') || $user->hasRole('super_admin')),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => $user->hasRole('admin') || $user->hasRole('super_admin')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => $user->hasRole('admin') || $user->hasRole('super_admin')),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListNonconformities::route('/'),
            'create' => Pages\CreateNonconformity::route('/create'),
            'edit'   => Pages\EditNonconformity::route('/{record}/edit'),
        ];
    }
}
