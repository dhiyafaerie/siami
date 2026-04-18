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

class BerkasProdi extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-folder-open';
    protected static ?string $navigationGroup = 'Berkas';
    protected static ?string $navigationLabel = 'Berkas Saya';
    protected static ?string $title           = 'Berkas dari Super Admin';
    protected static ?string $slug            = 'berkas-saya-prodi';
    protected static string $view             = 'filament.pages.berkas-prodi';

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && $user->prodi()->exists();
    }

    public function table(Table $table): Table
    {
        $prodiIds = Auth::user()->prodi->pluck('id')->all();

        return $table
            ->query(
                Berkas::query()
                    ->with(['uploader', 'cycle', 'targetProdi'])
                    ->forProdi($prodiIds)
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

                Tables\Columns\TextColumn::make('targetProdi.programstudi')
                    ->label('Untuk')
                    ->badge()
                    ->default('Semua Prodi')
                    ->color(fn ($state) => $state === 'Semua Prodi' ? 'gray' : 'info'),

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
