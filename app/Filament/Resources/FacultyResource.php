<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FacultyResource\Pages;
use App\Filament\Resources\FacultyResource\RelationManagers;
use App\Models\Faculty;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class FacultyResource extends Resource
{
    protected static ?string $model = Faculty::class;

    protected static ?string $navigationGroup = "User Management";
    
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                    Forms\Components\TextInput::make('user.name')
                        ->label('Dekan')
                        ->required()
                        ->afterStateHydrated(fn ($component, $state) =>
                            $component->state(optional($component->getRecord()->user)->name)
                        ),
                    Forms\Components\TextInput::make('user.email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->afterStateHydrated(fn ($component) =>
                            $component->state(optional($component->getRecord()->user)->email)
                        ),
                    Forms\Components\TextInput::make('user.password')
                        ->label('Password')
                        ->password()
                        ->required(),
                
                Forms\Components\TextInput::make('fakultas')
                    ->required()
                    ->maxLength(255),
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Dekan'),
                Tables\Columns\TextColumn::make('fakultas')
                    ->searchable(),
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
            'index' => Pages\ListFaculties::route('/'),
            'create' => Pages\CreateFaculty::route('/create'),
            'edit' => Pages\EditFaculty::route('/{record}/edit'),
        ];
    }
}
