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
use Illuminate\Validation\Rule;


class FacultyResource extends Resource
{
    protected static ?string $model = Faculty::class;

    protected static ?string $navigationGroup = "User Management";
    protected static ?string $navigationLabel = "Fakultas";
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $pluralModelLabel = 'Fakultas';    
    protected static ?string $title = 'Fakultas';

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
                    ->numeric()
                    ->label("NIDN")
                    ->maxLength(255)
                    ->rules(fn ($record) => [
                        Rule::unique('faculties', 'nidn')->ignore($record?->id),
                    ]),
                Forms\Components\TextInput::make('nik_nip')
                    ->required()
                    ->numeric()
                    ->label("NIK / NIP")
                    ->maxLength(255)
                    ->rules(fn ($record) => [
                        Rule::unique('faculties', 'nik_nip')->ignore($record?->id),
                    ]),
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
                    })
                    ->rules(fn ($record) => [
                        Rule::unique('users', 'email')->ignore($record?->user?->id),
                    ]),
                
                // Password Field (Only for new users)
                Forms\Components\TextInput::make('user.password')
                    ->label('Password')
                    ->password()
                    ->required(fn (string $operation) => $operation === 'create')
                    ->columnSpan(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('fakultas')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Dekan'),
                Tables\Columns\TextColumn::make('nidn')
                    ->label('NIDN')
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
