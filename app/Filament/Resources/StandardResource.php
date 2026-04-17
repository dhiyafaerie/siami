<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StandardResource\Pages;
use App\Filament\Resources\StandardResource\RelationManagers;
use App\Models\Cycle;
use App\Models\Standard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Set;
use Illuminate\Support\Str;

class StandardResource extends Resource
{
    protected static ?string $model = Standard::class;
    protected static ?string $navigationGroup = "AMI";
    protected static ?string $navigationLabel = "Standar";
    protected static ?string $pluralModelLabel = 'Standar';    
    protected static ?string $title = 'Standar';
    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nomor')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('cycles_id')
                    ->relationship('cycle', 'name')
                    ->label("Siklus")
                    ->required()
                    ->preload()
                    ->searchable(),
                Forms\Components\RichEditor::make('deskriptor')
                    ->required()
                    ->helperText('Jika memiliki sub-deskriptor, gunakan format A., B., C. dst.')
                    ->columnSpanFull(),
                Forms\Components\TagsInput::make('keywords')
                    ->required()
                    ->separator(',')
                    ->placeholder('Ketik keyword lalu tekan Enter')
                    ->helperText('Tambahkan keyword per sub-deskriptor. Contoh: tingkat kepuasan mahasiswa, tindak lanjut kepuasan mahasiswa')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor')
                    ->searchable()
                    ->alignStart(),
                Tables\Columns\TextColumn::make('deskriptor')
                    ->wrap()
                    ->html()
                    ->searchable()
                    ->state(function ($record) {
                        $text = Standard::htmlToPlainText($record->deskriptor);
                        $keywords = array_filter(array_map('trim', explode(',', $record->keywords ?? '')));
                        if (count($keywords) > 1) {
                            $text = preg_replace('/\s*([B-Z])\.\s/', '<hr style="border-top:1px solid #d1d5db;margin:6px 0"><strong>$1.</strong> ', $text);
                            if (preg_match('/^A\.\s/', $text)) {
                                $text = '<strong>A.</strong> ' . substr($text, 3);
                            }
                        }
                        return nl2br($text);
                    }),
                Tables\Columns\TextColumn::make('keywords')
                    ->searchable()
                    ->html()
                    ->wrap()
                    ->state(function ($record) {
                        $keywords = array_filter(array_map('trim', explode(',', $record->keywords ?? '')));
                        if (count($keywords) <= 1) {
                            return $record->keywords;
                        }
                        $letters = range('A', 'Z');
                        return collect($keywords)->values()->map(fn ($kw, $i) =>
                            '<strong>' . ($letters[$i] ?? '') . '.</strong> ' . e(trim($kw))
                        )->implode('<hr style="border-top:1px solid #d1d5db;margin:6px 0">');
                    }),
                Tables\Columns\TextColumn::make('cycle.name')
                    ->label("Siklus"),
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
                Tables\Filters\SelectFilter::make('cycles_id')
                    ->label('Siklus')
                    ->options(Cycle::orderByDesc('year')->pluck('name', 'id'))
                    ->default(Cycle::where('is_active', true)->value('id'))
                    ->placeholder('Semua Siklus'),
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
