<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProdiattachmentResource\Pages;
use App\Filament\Resources\ProdiattachmentResource\RelationManagers;
use App\Models\Prodiattachment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ProdiattachmentResource extends Resource
{
    protected static ?string $model = Prodiattachment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('standards_id')
                            ->relationship('standard', 'nomor')
                            ->label('Standar')
                            ->preload()
                            ->searchable(),
                Forms\Components\Select::make('prodis_id')
                            ->relationship('prodi', 'programstudi')
                            ->label('Program Studi')
                            ->preload()
                            ->searchable(),
                Forms\Components\Hidden::make('users_id')
                    // ->default(auth()->user()->id)
                    ->default(fn () => Auth::id())
                    ->hidden(),
                Forms\Components\Textarea::make('keterangan')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('link_bukti')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('standard.nomor')
                    ->label('Standar')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('prodi.programstudi')
                    ->label('Prodi')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
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
            'index' => Pages\ListProdiattachments::route('/'),
            'create' => Pages\CreateProdiattachment::route('/create'),
            'edit' => Pages\EditProdiattachment::route('/{record}/edit'),
        ];
    }
}
