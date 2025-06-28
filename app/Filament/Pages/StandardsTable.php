<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Standard;
use App\Models\Prodi;
use App\Models\Prodiattachment;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class StandardsTable extends Page implements HasTable
{
    use HasPageShield;
    use InteractsWithTable;
    
    protected static ?string $model = Standard::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.standards-table';

    public static function table(Table $table): Table
    {
        $user = Auth::user()->load('prodi');
        $userProdi = $user->prodi;
        
        $isProdi = $userProdi->isNotEmpty();
        $currentProdiId = $isProdi ? $userProdi->first()?->id : null;
        
        return $table
            ->query(function () use ($isProdi, $currentProdiId) {
                $query = Standard::query()
                    ->with(['prodiattachment' => function($query) use ($isProdi, $currentProdiId) {
                        if ($isProdi) {
                            $query->where('prodis_id', $currentProdiId)
                                  ->where('users_id', Auth::id());
                        }
                    }]);
                
                return $query;
            })
            ->filters([
                Tables\Filters\SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->visible(fn () => !$isProdi)
                    ->options(Prodi::pluck('programstudi', 'id'))
                    ->placeholder('Semua Program Studi')
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('prodiattachment', function($q) use ($data) {
                                $q->where('prodis_id', $data['value']);
                            });
                        }
                    }),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('nomor')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('deskriptor')
                    ->wrap()
                    ->html()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('prodiattachment.link_bukti')
                    ->label('Link Bukti')
                    ->state(function ($record, $livewire) {
                        $filterValue = $livewire->tableFilters['prodi_id']['value'] ?? null;
                        $attachments = $record->prodiattachment
                            ->when($filterValue, function($collection) use ($filterValue) {
                                return $collection->where('prodis_id', $filterValue);
                            });
                        
                        return $attachments->map(function($attachment) {
                            return '<a href="'.$attachment->link_bukti.'" target="_blank">'.$attachment->link_bukti.'</a>';
                        })->implode('<br>');
                    })
                    ->html(),
                
                Tables\Columns\TextColumn::make('prodiattachment.prodi.programstudi')
                    ->label('Program Studi')
                    ->visible(fn () => !$isProdi)
                    ->state(function ($record, $livewire) {
                        $filterValue = $livewire->tableFilters['prodi_id']['value'] ?? null;
                        $attachments = $record->prodiattachment
                            ->when($filterValue, function($collection) use ($filterValue) {
                                return $collection->where('prodis_id', $filterValue);
                            });
                        
                        return $attachments->map(function($attachment) {
                            return $attachment->prodi->programstudi;
                        })->unique()->implode('<br>');
                    })
                    ->html(),
                
                Tables\Columns\TextColumn::make('prodiattachment.keterangan')
                    ->label('Keterangan')
                    ->state(function ($record, $livewire) {
                        $filterValue = $livewire->tableFilters['prodi_id']['value'] ?? null;
                        $attachments = $record->prodiattachment
                            ->when($filterValue, function($collection) use ($filterValue) {
                                return $collection->where('prodis_id', $filterValue);
                            });
                        
                        return $attachments->map(function($attachment) {
                            return $attachment->keterangan;
                        })->implode('<br>');
                    })
                    ->html()
                    ->wrap(),
            ])
            ->actions([
                Tables\Actions\Action::make('input_dokumen')
                    ->form(function () use ($userProdi) {
                        $prodi = $userProdi->first();
                        
                        return [
                            Forms\Components\TextInput::make('prodi_info')
                                ->label('Program Studi')
                                ->default($prodi ? $prodi->programstudi : 'Not assigned')
                                ->disabled(),
                            Forms\Components\Hidden::make('prodis_id')
                                ->default($prodi?->id)
                                ->required(),
                            Forms\Components\Textarea::make('keterangan')
                                ->required(),
                            Forms\Components\TextInput::make('link_bukti')
                                ->url()
                                ->required()
                        ];
                    })
                    ->action(function (Standard $record, array $data) {
                        Prodiattachment::updateOrCreate(
                            [
                                'standards_id' => $record->id,
                                'users_id' => Auth::id()
                            ],
                            [
                                'prodis_id' => $data['prodis_id'],
                                'keterangan' => $data['keterangan'],
                                'link_bukti' => $data['link_bukti']
                            ]
                        );
                        
                        Notification::make()
                            ->title('Data berhasil disimpan')
                            ->success()
                            ->send();
                    })
                    ->visible(fn () => $userProdi->isNotEmpty())
            ])
            ->bulkActions([]);
    }
}