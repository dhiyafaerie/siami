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
use Filament\Forms\Components\TextInput;


class FacultyResource extends Resource
{
    protected static ?string $model = Faculty::class;

    protected static ?string $navigationGroup = "User Management";
    protected static ?string $navigationLabel = "Fakultas";
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                    
                Forms\Components\TextInput::make('fakultas')
                    ->label('Nama Fakultas')
                    ->required()
                    ->maxLength(255),

            // Dekan Name Field
                Forms\Components\TextInput::make('user.name')
                    ->label('Nama Dekan')
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
                    ->label("NIDN")
                    ->maxLength(255),
                Forms\Components\TextInput::make('nik_nip')
                    ->required()
                    ->label("NIK / NIP")
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
                Tables\Columns\TextColumn::make('fakultas')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Dekan'),
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
