<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CycleResource\Pages;
use App\Models\Cycle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class CycleResource extends Resource
{
    protected static ?string $model = Cycle::class;
    protected static ?string $navigationGroup = "AMI";
    protected static ?string $navigationLabel = "Siklus";
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $pluralModelLabel = 'Siklus';
    protected static ?string $title = 'Siklus';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('year')
                    ->label('Tahun')
                    ->required()
                    ->maxLength(4),
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('start_date')
                    ->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->helperText('Hanya satu siklus yang bisa aktif. Mengaktifkan siklus ini akan menonaktifkan siklus lain secara otomatis.')
                    ->required(),
                Forms\Components\Toggle::make('is_locked')
                    ->label('Kunci Siklus')
                    ->helperText('Siklus yang dikunci tidak dapat diisi atau diedit oleh auditor dan prodi.')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('year')
                    ->label('Tahun')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Selesai')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_locked')
                    ->label('Dikunci')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('danger')
                    ->falseColor('success'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\Action::make('toggle_lock')
                    ->label(fn (Cycle $record) => $record->is_locked ? 'Buka Kunci' : 'Kunci')
                    ->icon(fn (Cycle $record) => $record->is_locked ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
                    ->color(fn (Cycle $record) => $record->is_locked ? 'success' : 'danger')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Cycle $record) => $record->is_locked ? 'Buka Kunci Siklus?' : 'Kunci Siklus?')
                    ->modalDescription(fn (Cycle $record) => $record->is_locked
                        ? 'Siklus akan dibuka kembali dan dapat diedit.'
                        : 'Siklus akan dikunci. Auditor dan prodi tidak dapat mengedit data.')
                    ->action(function (Cycle $record) {
                        $record->update(['is_locked' => !$record->is_locked]);
                        Notification::make()
                            ->title($record->is_locked ? 'Siklus berhasil dikunci' : 'Siklus berhasil dibuka')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('year', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCycles::route('/'),
            'create' => Pages\CreateCycle::route('/create'),
            'edit' => Pages\EditCycle::route('/{record}/edit'),
        ];
    }
}
