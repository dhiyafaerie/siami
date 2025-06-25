<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProdiaatachmentResource\Pages;
use App\Filament\Resources\ProdiaatachmentResource\RelationManagers;
use App\Models\Prodiaatachment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use AmidEsfahani\FilamentTinyEditor\TinyEditor;


class ProdiaatachmentResource extends Resource
{
    protected static ?string $model = Prodiaatachment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TinyEditor::make('keterangan')
                    ->required()
                    ->columnSpanFull(),
                TinyEditor::make('link_bukti')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('users_id')
                    ->default(auth()->user()->id)
                    ->readOnly(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListProdiaatachments::route('/'),
            'create' => Pages\CreateProdiaatachment::route('/create'),
            'edit' => Pages\EditProdiaatachment::route('/{record}/edit'),
        ];
    }
}
