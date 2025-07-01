<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NonconformityResource\Pages;
use App\Filament\Resources\NonconformityResource\RelationManagers;
use App\Models\Nonconformity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
        return $form
            ->schema([
                Forms\Components\TextInput::make('kts')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kts')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNonconformities::route('/'),
            'create' => Pages\CreateNonconformity::route('/create'),
            'edit' => Pages\EditNonconformity::route('/{record}/edit'),
        ];
    }
}
