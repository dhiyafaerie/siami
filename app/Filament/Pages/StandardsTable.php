<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Standard;
use App\Models\Prodi;
use App\Models\Cycle;
use App\Models\Prodiattachment;
use App\Models\Auditscore;
use App\Exports\StandardsTableExport;
use App\Exports\FakultasRekapExport;
use App\Models\Nonconformity;
use App\Notifications\AuditScoreSavedNotification;
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
        $user = Auth::user();
        $user->loadMissing('faculty');
        $isAuditor  = $user->hasRole('auditor');
        $isAdmin    = $user->hasRole('admin') || $user->hasRole('super_admin');
        $isFakultas = $user->faculty !== null;
        $fakultasProdiOptions = $isFakultas
            ? Prodi::where('faculties_id', $user->faculty->id)->pluck('programstudi', 'id')
            : Prodi::pluck('programstudi', 'id');

        return [
            Action::make('export_excel')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->visible($isAuditor)
                ->action(fn () => Excel::download(new StandardsTableExport, 'tabel-kompilasi-' . now()->format('Y-m-d') . '.xlsx')),

            Action::make('export_rekap_fakultas')
                ->label('Rekap per Fakultas')
                ->icon('heroicon-o-building-library')
                ->visible($isAdmin)
                ->action(fn () => Excel::download(new FakultasRekapExport, 'rekap-fakultas-' . now()->format('Y-m-d') . '.xlsx')),

            Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->visible($isAuditor || $isAdmin || $isFakultas)
                ->form([
                    Forms\Components\Select::make('prodis_id')
                        ->label('Program Studi')
                        ->options($fakultasProdiOptions)
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data) {
                    $cycle = Cycle::where('is_active', true)->first();
                    $url = route('pdf.prodi', ['prodi' => $data['prodis_id'], 'cycle' => $cycle?->id]);
                    $this->redirect($url, navigate: false);
                }),
        ];
    }

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $user->load('prodi', 'faculty', 'roles');
        $userProdi = $user->prodi;

        $isProdi    = $userProdi->isNotEmpty();
        $currentProdiId = $isProdi ? $userProdi->first()?->id : null;
        $isAuditor  = $user->hasRole('auditor');
        $isAdmin    = $user->hasRole('admin') || $user->hasRole('super_admin');
        $isFakultas = $user->faculty !== null;
        $fakultasProdiIds = $isFakultas
            ? Prodi::where('faculties_id', $user->faculty->id)->pluck('id')
            : null;

        $activeCycle = Cycle::where('is_active', true)->first();
        $isLocked = $activeCycle?->is_locked ?? false;

        return $table
            ->query(function () use ($isProdi, $currentProdiId, $isAuditor, $isFakultas, $fakultasProdiIds) {
                $query = Standard::query()
                    ->with(['prodiattachment' => function ($query) use ($isProdi, $currentProdiId, $isFakultas, $fakultasProdiIds) {
                        if ($isProdi) {
                            $query->where('prodis_id', $currentProdiId)
                                ->where('users_id', Auth::id());
                        } elseif ($isFakultas) {
                            $query->whereIn('prodis_id', $fakultasProdiIds)->with('prodi');
                        } else {
                            $query->with('prodi');
                        }
                    }])
                    ->with(['auditscore' => function ($query) use ($isAuditor, $isProdi, $currentProdiId, $isFakultas, $fakultasProdiIds) {
                        if ($isAuditor) {
                            $query->where('auditors_id', Auth::id());
                        } elseif ($isProdi) {
                            $query->where('prodis_id', $currentProdiId);
                        } elseif ($isFakultas) {
                            $query->whereIn('prodis_id', $fakultasProdiIds)->with('prodi');
                        } else {
                            $query->with('prodi');
                        }
                    }]);

                return $query;
            })
            ->filters([
                Tables\Filters\SelectFilter::make('cycles_id')
                    ->label('Siklus')
                    ->options(Cycle::pluck('name', 'id'))
                    ->default($activeCycle?->id)
                    ->placeholder('Semua Siklus')
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->where('cycles_id', $data['value']);
                        }
                    }),

                Tables\Filters\SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->visible(fn () => !$isProdi || $isAuditor || $isFakultas)
                    ->options(
                        $isFakultas
                            ? Prodi::whereIn('id', $fakultasProdiIds)->pluck('programstudi', 'id')
                            : Prodi::pluck('programstudi', 'id')
                    )
                    ->default($isAuditor
                        ? \App\Models\Auditor::where('users_id', Auth::id())->first()?->prodis_id
                        : null
                    )
                    ->placeholder('Semua Program Studi')
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('prodiattachment', function ($q) use ($data) {
                                $q->where('prodis_id', $data['value']);
                            });
                        }
                    }),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('nomor')
                    ->searchable()
                    ->width('80px'),

                Tables\Columns\TextColumn::make('deskriptor')
                    ->wrap()
                    ->html()
                    ->searchable()
                    ->state(function ($record) {
                        $text = strip_tags($record->deskriptor);
                        $keywords = array_filter(array_map('trim', explode(',', $record->keywords ?? '')));
                        if (count($keywords) > 1) {
                            $text = preg_replace('/\s*([B-Z])\.\s/', '<hr style="border-top:1px solid #d1d5db;margin:6px 0"><strong>$1.</strong> ', $text);
                            if (preg_match('/^A\.\s/', $text)) {
                                $text = '<strong>A.</strong> ' . substr($text, 3);
                            }
                        }
                        return $text;
                    })
                    ->tooltip(fn ($record) => strip_tags($record->deskriptor)),

                Tables\Columns\TextColumn::make('keywords')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
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

                Tables\Columns\TextColumn::make('prodiattachment.link_bukti')
                    ->label('Link Bukti')
                    ->state(function ($record, $livewire) use ($isAuditor) {
                        $filterValue = $livewire->tableFilters['prodi_id']['value'] ?? null;
                        $attachments = $record->prodiattachment
                            ->when($filterValue, fn ($c) => $c->where('prodis_id', $filterValue));

                        $keywords = array_filter(array_map('trim', explode(',', $record->keywords ?? '')));
                        $hasMultiple = count($keywords) > 1;
                        $letters = range('A', 'Z');
                        $separator = $hasMultiple ? '<hr style="border-top:1px solid #d1d5db;margin:6px 0">' : '<br>';

                        return $attachments->values()->map(function ($attachment, $index) use ($isAuditor, $hasMultiple, $letters) {
                            $url = e($attachment->link_bukti);
                            $label = $isAuditor ? (strlen($url) > 40 ? substr($url, 0, 40) . '…' : $url) : $url;
                            $prefix = $hasMultiple ? '<strong>' . ($letters[$index] ?? '') . '.</strong> ' : '';
                            return $prefix . '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">' . $label . '</a>';
                        })->implode($separator);
                    })
                    ->html(),

                Tables\Columns\TextColumn::make('prodiattachment.keterangan')
                    ->label('Keterangan')
                    ->state(function ($record, $livewire) {
                        $filterValue = $livewire->tableFilters['prodi_id']['value'] ?? null;
                        $attachments = $record->prodiattachment
                            ->when($filterValue, fn ($c) => $c->where('prodis_id', $filterValue));

                        $keywords = array_filter(array_map('trim', explode(',', $record->keywords ?? '')));
                        $hasMultiple = count($keywords) > 1;
                        $letters = range('A', 'Z');
                        $separator = $hasMultiple ? '<hr style="border-top:1px solid #d1d5db;margin:6px 0">' : '<br>';

                        return $attachments->values()->map(function ($a, $index) use ($hasMultiple, $letters) {
                            $prefix = $hasMultiple ? '<strong>' . ($letters[$index] ?? '') . '.</strong> ' : '';
                            return $prefix . e($a->keterangan);
                        })->implode($separator);
                    })
                    ->html()
                    ->wrap(),

                Tables\Columns\TextColumn::make('prodiattachment.prodi.programstudi')
                    ->label('Program Studi')
                    ->visible(fn () => !$isProdi || $isAuditor || $isFakultas)
                    ->state(function ($record, $livewire) {
                        $filterValue = $livewire->tableFilters['prodi_id']['value'] ?? null;
                        $attachments = $record->prodiattachment
                            ->when($filterValue, fn ($c) => $c->where('prodis_id', $filterValue));

                        return $attachments->map(fn ($a) => $a->prodi->programstudi)->unique()->implode('<br>');
                    })
                    ->html()
                    ->width($isAuditor ? '150px' : null),

                Tables\Columns\TextColumn::make('auditscore.score')
                    ->label('Nilai Audit')
                    ->visible($isAuditor || $isProdi || $isFakultas || $isAdmin)
                    ->state(function ($record, $livewire) {
                        $filterValue = $livewire->tableFilters['prodi_id']['value'] ?? null;
                        $scores = $record->auditscore
                            ->when($filterValue, fn ($c) => $c->where('prodis_id', $filterValue));

                        return $scores->map(function ($score) {
                            return match ($score->score) {
                                1 => '1 - Kurang Cukup',
                                2 => '2 - Kurang',
                                3 => '3 - Cukup',
                                4 => '4 - Sangat Cukup',
                                default => 'N/A'
                            };
                        })->implode('<br>');
                    })
                    ->html()
                    ->width($isAuditor ? '130px' : null),

                Tables\Columns\TextColumn::make('auditscore.notes')
                    ->label('Catatan Audit')
                    ->visible($isAuditor || $isProdi || $isFakultas || $isAdmin)
                    ->state(function ($record, $livewire) {
                        $filterValue = $livewire->tableFilters['prodi_id']['value'] ?? null;
                        $scores = $record->auditscore
                            ->when($filterValue, fn ($c) => $c->where('prodis_id', $filterValue));

                        return $scores->map(fn ($score) => e($score->notes))->implode('<br>');
                    })
                    ->html()
                    ->wrap()
                    ->limit($isAuditor ? 80 : null)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions($isAdmin ? [] : [
                Tables\Actions\Action::make('tambah_dokumen')
                    ->label('Tambah')
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->visible(fn ($record) => !$isLocked && !$isFakultas && $userProdi->isNotEmpty() && $record->prodiattachment->isEmpty())
                    ->form(function (Standard $record) use ($userProdi) {
                        $prodi = $userProdi->first();
                        $keywords = array_filter(array_map('trim', explode(',', $record->keywords ?? '')));
                        $letters = range('A', 'Z');

                        $fields = [
                            Forms\Components\TextInput::make('prodi_info')
                                ->label('Program Studi')
                                ->default($prodi ? $prodi->programstudi : 'Not assigned')
                                ->disabled(),
                            Forms\Components\Hidden::make('prodis_id')
                                ->default($prodi?->id)
                                ->required(),
                        ];

                        if (count($keywords) <= 1) {
                            $fields[] = Forms\Components\Textarea::make('items.0.keterangan')
                                ->label('Keterangan')
                                ->required();
                            $fields[] = Forms\Components\TextInput::make('items.0.link_bukti')
                                ->label('Link Bukti')
                                ->url()
                                ->required();
                        } else {
                            foreach ($keywords as $index => $keyword) {
                                $letter = $letters[$index] ?? ($index + 1);
                                $fields[] = Forms\Components\Section::make("{$letter}. " . ucfirst(trim($keyword)))
                                    ->schema([
                                        Forms\Components\Textarea::make("items.{$index}.keterangan")
                                            ->label('Keterangan')
                                            ->required(),
                                        Forms\Components\TextInput::make("items.{$index}.link_bukti")
                                            ->label('Link Bukti')
                                            ->url()
                                            ->required(),
                                    ]);
                            }
                        }

                        return $fields;
                    })
                    ->action(function (Standard $record, array $data) {
                        foreach ($data['items'] as $item) {
                            Prodiattachment::create([
                                'standards_id' => $record->id,
                                'users_id' => Auth::id(),
                                'prodis_id' => $data['prodis_id'],
                                'keterangan' => $item['keterangan'],
                                'link_bukti' => $item['link_bukti'],
                            ]);
                        }

                        Notification::make()
                            ->title('Data berhasil disimpan')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('edit_dokumen')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->iconButton()
                    ->visible(fn ($record) => !$isLocked && !$isFakultas && $userProdi->isNotEmpty() && $record->prodiattachment->isNotEmpty())
                    ->fillForm(function (Standard $record) {
                        $items = [];
                        foreach ($record->prodiattachment->values() as $index => $attachment) {
                            $items[$index] = [
                                'keterangan' => $attachment->keterangan,
                                'link_bukti' => $attachment->link_bukti,
                            ];
                        }
                        return ['items' => $items];
                    })
                    ->form(function (Standard $record) use ($userProdi) {
                        $prodi = $userProdi->first();
                        $keywords = array_filter(array_map('trim', explode(',', $record->keywords ?? '')));
                        $letters = range('A', 'Z');

                        $fields = [
                            Forms\Components\TextInput::make('prodi_info')
                                ->label('Program Studi')
                                ->default($prodi ? $prodi->programstudi : 'Not assigned')
                                ->disabled(),
                            Forms\Components\Hidden::make('prodis_id')
                                ->default($prodi?->id)
                                ->required(),
                        ];

                        if (count($keywords) <= 1) {
                            $fields[] = Forms\Components\Textarea::make('items.0.keterangan')
                                ->label('Keterangan')
                                ->required();
                            $fields[] = Forms\Components\TextInput::make('items.0.link_bukti')
                                ->label('Link Bukti')
                                ->url()
                                ->required();
                        } else {
                            foreach ($keywords as $index => $keyword) {
                                $letter = $letters[$index] ?? ($index + 1);
                                $fields[] = Forms\Components\Section::make("{$letter}. " . ucfirst(trim($keyword)))
                                    ->schema([
                                        Forms\Components\Textarea::make("items.{$index}.keterangan")
                                            ->label('Keterangan')
                                            ->required(),
                                        Forms\Components\TextInput::make("items.{$index}.link_bukti")
                                            ->label('Link Bukti')
                                            ->url()
                                            ->required(),
                                    ]);
                            }
                        }

                        return $fields;
                    })
                    ->action(function (Standard $record, array $data) {
                        $record->prodiattachment()
                            ->where('prodis_id', $data['prodis_id'])
                            ->where('users_id', Auth::id())
                            ->delete();

                        foreach ($data['items'] as $item) {
                            Prodiattachment::create([
                                'standards_id' => $record->id,
                                'users_id' => Auth::id(),
                                'prodis_id' => $data['prodis_id'],
                                'keterangan' => $item['keterangan'],
                                'link_bukti' => $item['link_bukti'],
                            ]);
                        }

                        Notification::make()
                            ->title('Data berhasil diperbarui')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('input_nilai')
                    ->label('Input Nilai')
                    ->icon('heroicon-o-star')
                    ->iconButton()
                    ->visible(fn () => $isAuditor && !$isLocked)
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
                                    4 => '4 - Sangat Cukup',
                                ])
                                ->required(),

                            Forms\Components\Textarea::make('notes')
                                ->label('Catatan Audit'),
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
                                    ->searchable()
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

                        $auditScore = Auditscore::updateOrCreate(
                            [
                                'standards_id' => $record->id,
                                'auditors_id' => Auth::id(),
                                'prodis_id' => $data['prodis_id'],
                            ],
                            [
                                'score' => $data['score'],
                                'notes' => $data['notes'],
                            ]
                        );

                        // Notify the prodi user that their submission was scored
                        $prodiUser = Prodi::find($data['prodis_id'])?->user;
                        if ($prodiUser) {
                            $prodiUser->notify(new AuditScoreSavedNotification($auditScore));
                        }

                        Notification::make()
                            ->title('Nilai audit berhasil disimpan')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('input_kts')
                    ->label('Input KTS')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->iconButton()
                    ->visible(fn () => $isAuditor && !$isLocked)
                    ->form(function ($livewire) {
                        $filterValue = $livewire->tableFilters['prodi_id']['value'] ?? null;
                        $prodi = $filterValue ? Prodi::find($filterValue) : null;

                        $fields = [
                            Forms\Components\TextInput::make('kts')
                                ->label('Kode KTS')
                                ->placeholder('Contoh: KTS-001')
                                ->required(),

                            Forms\Components\Textarea::make('description')
                                ->label('Deskripsi Ketidaksesuaian')
                                ->rows(3)
                                ->required(),

                            Forms\Components\DatePicker::make('deadline_perbaikan')
                                ->label('Deadline Perbaikan')
                                ->nullable(),
                        ];

                        if ($filterValue) {
                            array_unshift($fields,
                                Forms\Components\TextInput::make('prodi_display')
                                    ->label('Program Studi')
                                    ->default($prodi?->programstudi ?? '')
                                    ->disabled(),
                                Forms\Components\Hidden::make('prodis_id')
                                    ->default($filterValue)
                            );
                        } else {
                            array_unshift($fields,
                                Forms\Components\Select::make('prodis_id')
                                    ->label('Program Studi')
                                    ->options(Prodi::pluck('programstudi', 'id'))
                                    ->searchable()
                                    ->required()
                            );
                        }

                        return $fields;
                    })
                    ->action(function (Standard $record, array $data) {
                        if (empty($data['prodis_id'])) {
                            Notification::make()
                                ->danger()
                                ->title('Program Studi harus dipilih')
                                ->send();
                            return;
                        }

                        Nonconformity::create([
                            'kts'                => $data['kts'],
                            'description'        => $data['description'],
                            'standards_id'       => $record->id,
                            'prodis_id'          => $data['prodis_id'],
                            'auditors_id'        => Auth::id(),
                            'status'             => Nonconformity::STATUS_TERBUKA,
                            'deadline_perbaikan' => $data['deadline_perbaikan'] ?? null,
                        ]);

                        Notification::make()
                            ->title('KTS berhasil dicatat')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([]);

    }
}
