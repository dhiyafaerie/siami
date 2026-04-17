<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NonconformityResource\Pages;
use App\Models\Nonconformity;
use Filament\Forms;
use Filament\Forms\Form;
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
    protected static ?string $pluralModelLabel = 'KTS';
    protected static ?string $title = 'KTS';

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user->hasRole('super_admin') || $user->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kts')
                    ->label('Kode KTS')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('kategori')
                    ->label('Kategori')
                    ->placeholder('Contoh: Mayor, Minor, Observasi')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->whereNull('standards_id'))
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
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kategori')
                    ->label('Kategori')
                    ->options(Nonconformity::kategoriOptions()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'asc');
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
