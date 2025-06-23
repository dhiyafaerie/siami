<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StandardResource\Pages;
use App\Filament\Resources\StandardResource\RelationManagers;
use App\Models\Standard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use AmidEsfahani\FilamentTinyEditor\TinyEditor;
use Filament\Forms\Set;
use Illuminate\Support\Str;

class StandardResource extends Resource
{
    protected static ?string $model = Standard::class;
    protected static ?string $navigationGroup = "AMI";
    protected static ?string $navigationLabel = "Standar";
    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nomor')
                    ->required()
                    ->maxLength(255),
                TinyEditor::make('deskriptor')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('keywords')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('standar_mutu')
                    
                    ->maxLength(255),
                Forms\Components\TextInput::make('pernyataan_standar')
                    
                    ->maxLength(255),
                Forms\Components\TextInput::make('iku')
                    ->label("IKU")
                    ->maxLength(255),
                Forms\Components\TextInput::make('ikt')
                    ->label("IKT")
                    ->maxLength(255),
                    
                Forms\Components\Select::make('cycles_id')
                    ->relationship('cycle', 'name')
                    ->label("Siklus")
                    ->preload()
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('deskriptor')
                    ->wrap()
                    ->html()
                    ->searchable(),
                Tables\Columns\TextColumn::make('keywords')
                    ->searchable(),
                Tables\Columns\TextColumn::make('standar_mutu')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pernyataan_standar')
                    ->searchable(),
                Tables\Columns\TextColumn::make('iku')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ikt')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cycle.name'),
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
            'index' => Pages\ListStandards::route('/'),
            'create' => Pages\CreateStandard::route('/create'),
            'edit' => Pages\EditStandard::route('/{record}/edit'),
        ];
    }
}
