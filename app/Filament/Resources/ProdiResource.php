<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProdiResource\Pages;
use App\Filament\Resources\ProdiResource\RelationManagers;
use App\Models\Prodi;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Forms\Components\TextInput;

class ProdiResource extends Resource
{
    protected static ?string $model = Prodi::class;
    protected static ?string $navigationGroup = "User Management";
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('faculties_id')
                    ->relationship('faculty', 'fakultas')
                    ->preload()
                    ->searchable(),
                Forms\Components\TextInput::make('programstudi')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Radio::make('jenjang')
                    ->label('Jenjang')
                    ->options([
                        'sarjana' => 'Sarjana',
                        'magister' => 'Magister'
                    ])
                    ->inline()
                    ->inlineLabel(false),
                Forms\Components\TextInput::make('user.name')
                        ->label('Kaprodi')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1)
                        ->afterStateHydrated(function (TextInput $component) {
                            $component->state(
                                $component->getRecord()?->user?->name ?? ''
                            );
                        }),
                Forms\Components\TextInput::make('nidn')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nik_nip')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('telpon')
                    ->tel()
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('user.email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1)
                        ->afterStateHydrated(function (TextInput $component) {
                            $component->state(
                                $component->getRecord()?->user?->email ?? ''
                            );
                        }),
                // Password Field (Only for new users)
                    Forms\Components\TextInput::make('user.password')
                        ->label('Password')
                        ->password()
                        ->columnSpan(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('programstudi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenjang'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Kaprodi'),
                Tables\Columns\TextColumn::make('nidn')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nik_nip')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telpon')
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
            'index' => Pages\ListProdis::route('/'),
            'create' => Pages\CreateProdi::route('/create'),
            'edit' => Pages\EditProdi::route('/{record}/edit'),
        ];
    }
}
