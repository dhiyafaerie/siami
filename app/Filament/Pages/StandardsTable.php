<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Standard;
use App\Models\Prodi;
use App\Models\Prodiattachment;
use App\Models\Auditscore;
use App\Exports\StandardsTableExport;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;

class StandardsTable extends Page implements HasTable
{
    use HasPageShield;
    use InteractsWithTable;
    
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = "Tabel Kompilasi";
    protected static ?string $pluralModelLabel = 'Tabel Kompilasi';
    protected static ?string $title = 'Tabel Kompilasi';
    protected static string $view = 'filament.pages.standards-table';

    protected function getHeaderActions(): array
    {
        $isAuditor = Auth::user()->hasRole('auditor');

        return [
            Action::make('export_excel')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->visible($isAuditor)
                ->action(fn () => Excel::download(new StandardsTableExport, 'tabel-kompilasi-' . now()->format('Y-m-d') . '.xlsx')),
        ];
    }

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $user->load('prodi', 'roles');
        $userProdi = $user->prodi;
        
        $isProdi = $userProdi->isNotEmpty();
        $currentProdiId = $isProdi ? $userProdi->first()?->id : null;
        $isAuditor = $user->hasRole('auditor');

        return $table
            ->query(function () use ($isProdi, $currentProdiId, $isAuditor) {
                $query = Standard::query()
                    ->with(['prodiattachment' => function($query) use ($isProdi, $currentProdiId) {
                        if ($isProdi) {
                            $query->where('prodis_id', $currentProdiId)
                                  ->where('users_id', Auth::id());
                        }
                    }])
                    ->with(['auditscore' => function($query) use ($isAuditor, $isProdi, $currentProdiId) {
                        if ($isAuditor) {
                            $query->where('auditors_id', Auth::id());
                        } elseif ($isProdi) {
                            $query->where('prodis_id', $currentProdiId);
                        }
                    }]);
                
                return $query;
            })
            ->filters([
                Tables\Filters\SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->visible(fn () => !$isProdi || $isAuditor)
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

                Tables\Columns\TextColumn::make('keywords')
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
                            $url = e($attachment->link_bukti);
                            return '<a href="'.$url.'" target="_blank" rel="noopener noreferrer">'.$url.'</a>';
                        })->implode('<br>');
                    })
                    ->html(),
                
                Tables\Columns\TextColumn::make('prodiattachment.prodi.programstudi')
                    ->label('Program Studi')
                    ->visible(fn () => !$isProdi || $isAuditor)
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
                
                Tables\Columns\TextColumn::make('auditscore.score')
                    ->label('Nilai Audit')
                    ->visible($isAuditor || $isProdi)
                    ->state(function ($record, $livewire) {
                        $filterValue = $livewire->tableFilters['prodi_id']['value'] ?? null;

                        $scores = $record->auditscore
                            ->when($filterValue, function($collection) use ($filterValue) {
                                return $collection->where('prodis_id', $filterValue);
                            });

                        return $scores->map(function($score) {
                            return match($score->score) {
                                1 => '1 - Kurang Cukup',
                                2 => '2 - Kurang',
                                3 => '3 - Cukup',
                                4 => '4 - Sangat Cukup',
                                default => 'N/A'
                            };
                        })->implode('<br>');
                    })
                    ->html(),

                Tables\Columns\TextColumn::make('auditscore.notes')
                    ->label('Catatan Audit')
                    ->visible($isAuditor || $isProdi)
                    ->state(function ($record, $livewire) {
                        $filterValue = $livewire->tableFilters['prodi_id']['value'] ?? null;

                        $scores = $record->auditscore
                            ->when($filterValue, function($collection) use ($filterValue) {
                                return $collection->where('prodis_id', $filterValue);
                            });

                        return $scores->map(fn($score) => e($score->notes))->implode('<br>');
                    })
                    ->html()
                    ->wrap(),
            ])
            ->actions([
                Tables\Actions\Action::make('tambah_dokumen')
                    ->label('Tambah')
                    ->icon('heroicon-o-plus')
                    ->visible(fn ($record) => $userProdi->isNotEmpty() && $record->prodiattachment->isEmpty())
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
                                ->required(),
                        ];
                    })
                    ->action(function (Standard $record, array $data) {
                        Prodiattachment::create([
                            'standards_id' => $record->id,
                            'users_id' => Auth::id(),
                            'prodis_id' => $data['prodis_id'],
                            'keterangan' => $data['keterangan'],
                            'link_bukti' => $data['link_bukti'],
                        ]);

                        Notification::make()
                            ->title('Data berhasil disimpan')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('edit_dokumen')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->visible(fn ($record) => $userProdi->isNotEmpty() && $record->prodiattachment->isNotEmpty())
                    ->fillForm(fn ($record) => [
                        'keterangan' => $record->prodiattachment->first()->keterangan,
                        'link_bukti' => $record->prodiattachment->first()->link_bukti,
                    ])
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
                                ->required(),
                        ];
                    })
                    ->action(function (Standard $record, array $data) {
                        $record->prodiattachment->first()->update([
                            'prodis_id' => $data['prodis_id'],
                            'keterangan' => $data['keterangan'],
                            'link_bukti' => $data['link_bukti'],
                        ]);

                        Notification::make()
                            ->title('Data berhasil diperbarui')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('input_nilai')
                    ->label('Input Nilai')
                    ->visible($isAuditor)
                    ->form(function ($livewire) {
                        $filterValue = $livewire->tableFilters['prodi_id']['value'] ?? null;
                        $prodi = $filterValue ? Prodi::find($filterValue) : null;
                        
                        $formFields = [
                            Forms\Components\Select::make('score')
                                ->label('Nilai')
                                ->options([
                                    1 => '1 - Kurang Cukup',
                                    2 => '2 - Kurang',
                                    3 => '3 - Cukup',
                                    4 => '4 - Sangat Cukup'
                                ])
                                ->required(),
                                
                            Forms\Components\Textarea::make('notes')
                                ->label('Catatan Audit')
                        ];

                        if ($filterValue) {
                            array_unshift($formFields, 
                                Forms\Components\TextInput::make('prodi_display')
                                    ->label('Program Studi')
                                    ->default($prodi ? $prodi->programstudi : '')
                                    ->disabled(),
                                    
                                Forms\Components\Hidden::make('prodis_id')
                                    ->default($filterValue)
                            );
                        } else {
                            array_unshift($formFields,
                                Forms\Components\Select::make('prodis_id')
                                    ->label('Program Studi')
                                    ->options(Prodi::pluck('programstudi', 'id'))
                                    ->required()
                            );
                        }

                        return $formFields;
                    })
                    ->action(function (Standard $record, array $data) {
                        if (empty($data['prodis_id'])) {
                            Notification::make()
                                ->danger()
                                ->title('Program Studi harus dipilih')
                                ->send();
                            return;
                        }

                        AuditScore::updateOrCreate(
                            [
                                'standards_id' => $record->id,
                                'auditors_id' => Auth::id(),
                                'prodis_id' => $data['prodis_id']
                            ],
                            [
                                'score' => $data['score'],
                                'notes' => $data['notes']
                            ]
                        );
                        
                        Notification::make()
                            ->title('Nilai audit berhasil disimpan')
                            ->success()
                            ->send();
                    })
            ])
            ->bulkActions([]);
    }
}