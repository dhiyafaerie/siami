<?php

namespace App\Filament\Pages;

use App\Models\Berkas;
use App\Models\Cycle;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BerkasAuditor extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-folder-open';
    protected static ?string $navigationGroup = 'Berkas';
    protected static ?string $navigationLabel = 'Berkas Saya';
    protected static ?string $title           = 'Berkas dari Super Admin';
    protected static ?string $slug            = 'berkas-saya-auditor';
    protected static string $view             = 'filament.pages.berkas-auditor';

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('auditor') ?? false;
    }

    public function table(Table $table): Table
    {
        $userId = Auth::id();

        return $table
            ->query(
                Berkas::query()
                    ->with(['uploader', 'cycle'])
                    ->forAuditor($userId)
            )
            ->columns([
                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->wrap()
                    ->limit(80)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('cycle.name')
                    ->label('Siklus')
                    ->default('—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('target_id')
                    ->label('Untuk')
                    ->badge()
                    ->color(fn ($state) => $state ? 'info' : 'gray')
                    ->formatStateUsing(fn ($state) => $state ? 'Khusus Anda' : 'Semua Auditor'),

                Tables\Columns\TextColumn::make('file_name')
                    ->label('Nama File')
                    ->limit(40),

                Tables\Columns\TextColumn::make('file_size_formatted')
                    ->label('Ukuran')
                    ->state(fn (Berkas $r) => $r->file_size_formatted),

                Tables\Columns\TextColumn::make('uploader.name')
                    ->label('Diupload oleh')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Upload')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cycles_id')
                    ->label('Siklus')
                    ->options(Cycle::orderByDesc('year')->pluck('name', 'id')),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Unduh')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn (Berkas $r) => Storage::disk('public')->url($r->file_path), shouldOpenInNewTab: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum ada berkas')
            ->emptyStateDescription('Berkas dari super admin akan muncul di sini.');
    }
}
