<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

use App\Models\Standard;
use App\Models\Auditscore;
use App\Models\Prodi;

class DataSemuaProdiTable extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    public function table(Table $table): Table
    {
        return $table
            ->query(
                $query = Standard::query()
            )
            ->columns([
                Tables\Columns\TextColumn::make('nomor')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('deskriptor')
                    ->wrap()
                    ->html()
                    ->searchable(),
                Tables\Columns\TextColumn::make('prodiattachment.link_bukti')
                    ->label('Link Bukti'),
                Tables\Columns\TextColumn::make('auditscore.score')
                    ->label('Score')
            ]);
    }
}
