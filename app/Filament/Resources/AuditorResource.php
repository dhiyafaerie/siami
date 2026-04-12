<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditorResource\Pages;
use App\Filament\Resources\AuditorResource\RelationManagers;
use App\Models\Auditor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rule;

class AuditorResource extends Resource
{
    protected static ?string $model = Auditor::class;
    protected static ?string $navigationGroup = "User Management";
    protected static ?string $navigationLabel = 'Auditor';
    protected static ?string $pluralModelLabel = 'Auditor';    
    protected static ?string $title = 'Auditor';
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('prodis_id')
                ->relationship('prodi', 'programstudi')
                ->label("Prodi")
                ->preload()
                ->required()
                ->searchable()
                ->live()
                ->afterStateUpdated(function ($state, Forms\Set $set, $get) {
                    if ($state) {
                        $prodi = \App\Models\Prodi::find($state);
                        if ($prodi) {
                            $set('faculties_id', $prodi->faculties_id);
                        }
                    }
                }),

                Forms\Components\Select::make('faculties_id')
                    ->relationship('faculty', 'fakultas')
                    ->label("Fakultas")
                    ->required()
                    ->disabled() // Make it read-only since it will be auto-set
                    ->dehydrated(),
                Forms\Components\TextInput::make('user.name')
                        ->label('Nama Auditor')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1)
                        ->afterStateHydrated(function (Forms\Components\TextInput $component) {
                            $component->state(
                                $component->getRecord()?->user?->name ?? ''
                            );
                        }),
                Forms\Components\TextInput::make('nidn')
                    ->required()
                    ->maxLength(255)
                    ->rules(fn ($record) => [
                        Rule::unique('auditors', 'nidn')->ignore($record?->id),
                    ]),
                Forms\Components\TextInput::make('nik_nip')
                    ->required()
                    ->maxLength(255)
                    ->rules(fn ($record) => [
                        Rule::unique('auditors', 'nik_nip')->ignore($record?->id),
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
                        ->afterStateHydrated(function (Forms\Components\TextInput $component) {
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
            ->columns([
                Tables\Columns\TextColumn::make('faculty.fakultas')
                    ->label('Fakultas / Prodi')
                    ->description(fn ($record) => $record->prodi?->programstudi)
                    ->alignCenter()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Auditor'),
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
            'index' => Pages\ListAuditors::route('/'),
            'create' => Pages\CreateAuditor::route('/create'),
            'edit' => Pages\EditAuditor::route('/{record}/edit'),
        ];
    }
}
