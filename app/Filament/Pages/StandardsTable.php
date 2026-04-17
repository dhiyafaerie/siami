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
use App\Models\Auditor;
use App\Models\User;
use App\Notifications\AuditScoreSavedNotification;
use App\Notifications\BuktiDiuploadNotification;
use App\Notifications\KtsDibuatNotification;
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
use Spatie\Activitylog\Models\Activity;

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
        $user->loadMissing('faculty', 'prodi');
        $isAuditor  = $user->hasRole('auditor');
        $isAdmin    = $user->hasRole('admin') || $user->hasRole('super_admin');
        $isFakultas = $user->faculty !== null;
        $isProdi    = $user->prodi->isNotEmpty();
        $userProdi  = $isProdi ? $user->prodi->first() : null;
        $fakultasProdiOptions = $isFakultas
            ? Prodi::where('faculties_id', $user->faculty->id)->pluck('programstudi', 'id')
            : Prodi::pluck('programstudi', 'id');

        return [
            Action::make('export_excel')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->visible($isAuditor)
                ->action(fn () => Excel::download(new StandardsTableExport, 'tabel-kompilasi-' . now()->format('Y-m-d') . '.xlsx')),

            Action::make('export_excel_fakultas')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->visible($isFakultas && !$isAdmin && !$isAuditor)
                ->form([
                    Forms\Components\Select::make('prodis_id')
                        ->label('Program Studi')
                        ->options($fakultasProdiOptions)
                        ->searchable()
                        ->placeholder('Semua Program Studi')
                        ->nullable(),
                ])
                ->action(function (array $data) use ($user) {
                    $prodiId = $data['prodis_id'] ?? null;
                    $filename = $prodiId
                        ? 'rekap-' . str(Prodi::find($prodiId)->programstudi)->slug() . '-' . now()->format('Y-m-d') . '.xlsx'
                        : 'rekap-' . str($user->faculty->fakultas)->slug() . '-' . now()->format('Y-m-d') . '.xlsx';

                    return Excel::download(
                        new FakultasRekapExport($user->faculty->id, $prodiId),
                        $filename
                    );
                }),

            Action::make('export_excel_admin')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->visible($isAdmin)
                ->form([
                    Forms\Components\Select::make('faculties_id')
                        ->label('Fakultas')
                        ->options(\App\Models\Faculty::pluck('fakultas', 'id'))
                        ->searchable()
                        ->placeholder('Semua Fakultas')
                        ->reactive()
                        ->nullable(),

                    Forms\Components\Select::make('prodis_id')
                        ->label('Program Studi')
                        ->options(fn (Forms\Get $get) => $get('faculties_id')
                            ? Prodi::where('faculties_id', $get('faculties_id'))->pluck('programstudi', 'id')
                            : Prodi::pluck('programstudi', 'id'))
                        ->searchable()
                        ->placeholder('Semua Program Studi')
                        ->nullable(),
                ])
                ->action(function (array $data) {
                    $facultyId = $data['faculties_id'] ?? null;
                    $prodiId = $data['prodis_id'] ?? null;

                    if ($prodiId) {
                        $prodi = Prodi::find($prodiId);
                        $filename = 'rekap-' . str($prodi->programstudi)->slug() . '-' . now()->format('Y-m-d') . '.xlsx';
                        return Excel::download(new FakultasRekapExport($prodi->faculties_id, $prodiId), $filename);
                    }

                    if ($facultyId) {
                        $faculty = \App\Models\Faculty::find($facultyId);
                        $filename = 'rekap-' . str($faculty->fakultas)->slug() . '-' . now()->format('Y-m-d') . '.xlsx';
                        return Excel::download(new FakultasRekapExport($facultyId), $filename);
                    }

                    return Excel::download(new FakultasRekapExport, 'rekap-semua-fakultas-' . now()->format('Y-m-d') . '.xlsx');
                }),

            Action::make('export_excel_prodi')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->visible($isProdi && !$isAdmin && !$isAuditor && !$isFakultas)
                ->action(function () use ($userProdi) {
                    $filename = 'rekap-' . str($userProdi->programstudi)->slug() . '-' . now()->format('Y-m-d') . '.xlsx';
                    return Excel::download(
                        new FakultasRekapExport($userProdi->faculties_id, $userProdi->id),
                        $filename
                    );
                }),

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

            Action::make('export_pdf_prodi')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->visible($isProdi && !$isAdmin && !$isAuditor && !$isFakultas)
                ->action(function () use ($userProdi) {
                    $cycle = Cycle::where('is_active', true)->first();
                    $url = route('pdf.prodi', ['prodi' => $userProdi->id, 'cycle' => $cycle?->id]);
                    $this->redirect($url, navigate: false);
                }),

            Action::make('laporan_ami_pdf')
                ->label('Export PDF 2')
                ->icon('heroicon-o-document-text')
                ->color('primary')
                ->visible($isAuditor || $isAdmin || $isFakultas)
                ->form([
                    Forms\Components\Select::make('cycles_id')
                        ->label('Siklus')
                        ->options(Cycle::orderByDesc('year')->pluck('name', 'id'))
                        ->default(fn () => Cycle::where('is_active', true)->first()?->id)
                        ->required(),

                    Forms\Components\Select::make('prodis_id')
                        ->label('Program Studi')
                        ->options($fakultasProdiOptions)
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data) {
                    $url = route('pdf.laporan-ami', [
                        'prodi' => $data['prodis_id'],
                        'cycle' => $data['cycles_id'],
                    ]);
                    $this->redirect($url, navigate: false);
                }),

            Action::make('laporan_ami_pdf_prodi')
                ->label('Export PDF 2')
                ->icon('heroicon-o-document-text')
                ->color('primary')
                ->visible($isProdi && !$isAdmin && !$isAuditor && !$isFakultas)
                ->form([
                    Forms\Components\Select::make('cycles_id')
                        ->label('Siklus')
                        ->options(Cycle::orderByDesc('year')->pluck('name', 'id'))
                        ->default(fn () => Cycle::where('is_active', true)->first()?->id)
                        ->required(),
                ])
                ->action(function (array $data) use ($userProdi) {
                    $url = route('pdf.laporan-ami', [
                        'prodi' => $userProdi->id,
                        'cycle' => $data['cycles_id'],
                    ]);
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

        return $table
            ->query(function () use ($isProdi, $currentProdiId, $isAuditor, $isFakultas, $fakultasProdiIds) {
                $query = Standard::query()
                    ->with(['cycle', 'prodiattachment' => function ($query) use ($isProdi, $currentProdiId, $isFakultas, $fakultasProdiIds) {
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
                    ->placeholder($isProdi ? 'Pilih Siklus' : 'Semua Siklus')
                    ->query(function (Builder $query, array $data) use ($isProdi) {
                        if (!empty($data['value'])) {
                            $query->where('cycles_id', $data['value']);
                        } elseif ($isProdi) {
                            $query->whereRaw('0 = 1');
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
                    ->width('80px')
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
                    })
                    ->tooltip(fn ($record) => Standard::htmlToPlainText($record->deskriptor)),

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
                            ->when($filterValue, fn ($c) => $c->where('prodis_id', $filterValue))
                            ->sortBy('keyword_index');

                        $keywords = array_filter(array_map('trim', explode(',', $record->keywords ?? '')));
                        $hasMultiple = count($keywords) > 1;
                        $letters = range('A', 'Z');
                        $separator = $hasMultiple ? '<hr style="border-top:1px solid #d1d5db;margin:6px 0">' : '<br>';

                        $scoreLabel = fn ($v) => match ($v) {
                            1 => '1 - Kurang',
                            2 => '2 - Cukup',
                            3 => '3 - Baik',
                            4 => '4 - Sangat Baik',
                            default => 'N/A',
                        };

                        return $scores->values()->map(function ($score, $i) use ($hasMultiple, $letters, $scoreLabel) {
                            $prefix = $hasMultiple ? '<strong>' . ($letters[$score->keyword_index ?? $i] ?? '') . '.</strong> ' : '';
                            return $prefix . $scoreLabel($score->score);
                        })->implode($separator);
                    })
                    ->html()
                    ->width($isAuditor ? '130px' : null),

                Tables\Columns\TextColumn::make('auditscore.notes')
                    ->label('Catatan Audit')
                    ->visible($isAuditor || $isProdi || $isFakultas || $isAdmin)
                    ->state(function ($record, $livewire) {
                        $filterValue = $livewire->tableFilters['prodi_id']['value'] ?? null;
                        $scores = $record->auditscore
                            ->when($filterValue, fn ($c) => $c->where('prodis_id', $filterValue))
                            ->sortBy('keyword_index');

                        $keywords = array_filter(array_map('trim', explode(',', $record->keywords ?? '')));
                        $hasMultiple = count($keywords) > 1;
                        $letters = range('A', 'Z');
                        $separator = $hasMultiple ? '<hr style="border-top:1px solid #d1d5db;margin:6px 0">' : '<br>';

                        return $scores->values()->map(function ($score, $i) use ($hasMultiple, $letters) {
                            $prefix = $hasMultiple ? '<strong>' . ($letters[$score->keyword_index ?? $i] ?? '') . '.</strong> ' : '';
                            return $prefix . e($score->notes);
                        })->implode($separator);
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
                    ->visible(fn ($record) => $record->cycle?->is_active && !$record->cycle?->is_locked && !$isFakultas && $userProdi->isNotEmpty() && $record->prodiattachment->isEmpty())
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

                        $prodi = Prodi::find($data['prodis_id']);
                        if ($prodi) {
                            $auditorUserIds = Auditor::where('prodis_id', $prodi->id)
                                ->pluck('users_id')->unique();
                            $auditors = User::whereIn('id', $auditorUserIds)->get();
                            foreach ($auditors as $auditor) {
                                $auditor->notify(new BuktiDiuploadNotification(
                                    $record, $prodi, count($data['items']), false
                                ));
                            }
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
                    ->visible(fn ($record) => $record->cycle?->is_active && !$record->cycle?->is_locked && !$isFakultas && $userProdi->isNotEmpty() && $record->prodiattachment->isNotEmpty())
                    ->fillForm(function (Standard $record) use ($userProdi) {
                        $prodi = $userProdi->first();
                        $items = [];
                        foreach ($record->prodiattachment->values() as $index => $attachment) {
                            $items[$index] = [
                                'keterangan' => $attachment->keterangan,
                                'link_bukti' => $attachment->link_bukti,
                            ];
                        }
                        return [
                            'prodi_info' => $prodi?->programstudi ?? 'Not assigned',
                            'prodis_id'  => $prodi?->id,
                            'items'      => $items,
                        ];
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

                        $prodi = Prodi::find($data['prodis_id']);
                        if ($prodi) {
                            $auditorUserIds = Auditor::where('prodis_id', $prodi->id)
                                ->pluck('users_id')->unique();
                            $auditors = User::whereIn('id', $auditorUserIds)->get();
                            foreach ($auditors as $auditor) {
                                $auditor->notify(new BuktiDiuploadNotification(
                                    $record, $prodi, count($data['items']), true
                                ));
                            }
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
                    ->visible(function ($record, $livewire) use ($isAuditor) {
                        if (!$isAuditor || !$record->cycle?->is_active || $record->cycle?->is_locked) return false;
                        $filterValue = $livewire->tableFilters['prodi_id']['value'] ?? null;
                        if (!$filterValue) return true;
                        // Cek prodi sudah upload bukti untuk standar ini
                        return Prodiattachment::where('standards_id', $record->id)
                            ->where('prodis_id', $filterValue)->exists();
                    })
                    ->form(function (Standard $record, $livewire) {
                        $filterValue = $livewire->tableFilters['prodi_id']['value'] ?? null;
                        $prodi = $filterValue ? Prodi::find($filterValue) : null;
                        $keywords = array_filter(array_map('trim', explode(',', $record->keywords ?? '')));
                        $hasMultiple = count($keywords) > 1;
                        $letters = range('A', 'Z');

                        $scoreOptions = [
                            1 => '1 - Kurang',
                            2 => '2 - Cukup',
                            3 => '3 - Baik',
                            4 => '4 - Sangat Baik',
                        ];

                        $formFields = [];

                        if ($filterValue) {
                            $formFields[] = Forms\Components\TextInput::make('prodi_display')
                                ->label('Program Studi')
                                ->default($prodi ? $prodi->programstudi : '')
                                ->disabled();
                            $formFields[] = Forms\Components\Hidden::make('prodis_id')
                                ->default($filterValue);
                        } else {
                            $formFields[] = Forms\Components\Select::make('prodis_id')
                                ->label('Program Studi')
                                ->options(Prodi::pluck('programstudi', 'id'))
                                ->searchable()
                                ->required();
                        }

                        if ($hasMultiple) {
                            foreach ($keywords as $index => $keyword) {
                                $letter = $letters[$index] ?? ($index + 1);
                                $formFields[] = Forms\Components\Section::make("{$letter}. " . ucfirst(trim($keyword)))
                                    ->schema([
                                        Forms\Components\Select::make("items.{$index}.score")
                                            ->label('Nilai')
                                            ->options($scoreOptions)
                                            ->required(),
                                        Forms\Components\Textarea::make("items.{$index}.notes")
                                            ->label('Catatan Audit'),
                                    ]);
                            }
                        } else {
                            $formFields[] = Forms\Components\Select::make('items.0.score')
                                ->label('Nilai')
                                ->options($scoreOptions)
                                ->required();
                            $formFields[] = Forms\Components\Textarea::make('items.0.notes')
                                ->label('Catatan Audit');
                        }

                        return $formFields;
                    })
                    ->fillForm(function (Standard $record, $livewire) {
                        $filterValue = $livewire->tableFilters['prodi_id']['value'] ?? null;
                        if (!$filterValue) return [];
                        $prodi = Prodi::find($filterValue);
                        $existing = Auditscore::where('standards_id', $record->id)
                            ->where('auditors_id', Auth::id())
                            ->where('prodis_id', $filterValue)
                            ->get();
                        $items = [];
                        foreach ($existing as $score) {
                            $idx = $score->keyword_index ?? 0;
                            $items[$idx] = ['score' => $score->score, 'notes' => $score->notes];
                        }
                        return [
                            'prodi_display' => $prodi?->programstudi ?? '',
                            'prodis_id'     => $filterValue,
                            'items'         => $items,
                        ];
                    })
                    ->action(function (Standard $record, array $data) {
                        if (empty($data['prodis_id'])) {
                            Notification::make()
                                ->danger()
                                ->title('Program Studi harus dipilih')
                                ->send();
                            return;
                        }

                        $hasBukti = Prodiattachment::where('standards_id', $record->id)
                            ->where('prodis_id', $data['prodis_id'])->exists();
                        if (!$hasBukti) {
                            Notification::make()
                                ->danger()
                                ->title('Prodi belum mengupload bukti untuk standar ini')
                                ->send();
                            return;
                        }

                        $keywords = array_filter(array_map('trim', explode(',', $record->keywords ?? '')));
                        $hasMultiple = count($keywords) > 1;
                        $lastScore = null;

                        foreach ($data['items'] as $index => $item) {
                            $lastScore = Auditscore::updateOrCreate(
                                [
                                    'standards_id'  => $record->id,
                                    'auditors_id'   => Auth::id(),
                                    'prodis_id'     => $data['prodis_id'],
                                    'keyword_index' => $hasMultiple ? (int) $index : null,
                                ],
                                [
                                    'score' => $item['score'],
                                    'notes' => $item['notes'],
                                ]
                            );
                        }

                        $prodiUser = Prodi::find($data['prodis_id'])?->user;
                        if ($prodiUser && $lastScore) {
                            $prodiUser->notify(new AuditScoreSavedNotification($lastScore));
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
                    ->visible(function ($record) use ($isAuditor) {
                        return $isAuditor && $record->cycle?->is_active && !$record->cycle?->is_locked;
                    })
                    ->form(function ($livewire) {
                        $filterValue = $livewire->tableFilters['prodi_id']['value'] ?? null;
                        $prodi = $filterValue ? Prodi::find($filterValue) : null;

                        // Master KTS (template yang bisa dipakai berulang)
                        $availableKts = Nonconformity::whereNull('standards_id')
                            ->get(['id', 'kts', 'kategori'])
                            ->mapWithKeys(fn ($nc) => [$nc->id => $nc->kts . ' (' . ($nc->kategori ?? '-') . ')']);

                        $fields = [
                            Forms\Components\Select::make('nonconformity_id')
                                ->label('Pilih KTS')
                                ->options($availableKts)
                                ->searchable()
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

                        // Buat KTS baru berdasarkan master (master tetap utuh)
                        $master = Nonconformity::find($data['nonconformity_id']);
                        $kts = Nonconformity::create([
                            'kts'                => $master->kts,
                            'kategori'           => $master->kategori,
                            'description'        => $data['description'],
                            'standards_id'       => $record->id,
                            'prodis_id'          => $data['prodis_id'],
                            'auditors_id'        => Auth::id(),
                            'status'             => Nonconformity::STATUS_TERBUKA,
                            'deadline_perbaikan' => $data['deadline_perbaikan'] ?? null,
                        ]);

                        $prodiUser = Prodi::find($data['prodis_id'])?->user;
                        if ($prodiUser) {
                            $kts->load('standard');
                            $prodiUser->notify(new KtsDibuatNotification($kts));
                        }

                        Notification::make()
                            ->title('KTS berhasil dicatat')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('riwayat_nilai')
                    ->label('Riwayat Nilai')
                    ->icon('heroicon-o-clock')
                    ->color('gray')
                    ->iconButton()
                    ->visible(fn ($record) => ($isAuditor || $isProdi || $isFakultas || $isAdmin) && $record->auditscore->isNotEmpty())
                    ->modalHeading('Riwayat Perubahan Nilai')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(function (Standard $record, $livewire) {
                        $filterValue = $livewire->tableFilters['prodi_id']['value'] ?? null;
                        $scoreIds = $record->auditscore
                            ->when($filterValue, fn ($c) => $c->where('prodis_id', $filterValue))
                            ->pluck('id');

                        $activities = Activity::where('subject_type', Auditscore::class)
                            ->whereIn('subject_id', $scoreIds)
                            ->latest()
                            ->limit(20)
                            ->get();

                        $scoreLabel = fn ($v) => match ((int) $v) {
                            1 => '1 - Kurang',
                            2 => '2 - Cukup',
                            3 => '3 - Baik',
                            4 => '4 - Sangat Baik',
                            default => $v,
                        };

                        $html = '<div style="font-size:0.875rem;">';
                        if ($activities->isEmpty()) {
                            $html .= '<p style="color:#6b7280;">Belum ada riwayat perubahan.</p>';
                        } else {
                            foreach ($activities as $activity) {
                                $causer = $activity->causer?->name ?? 'System';
                                $date = $activity->created_at->format('d M Y H:i');
                                $props = $activity->properties;

                                $html .= '<div style="border-bottom:1px solid #e5e7eb;padding:8px 0;">';
                                $html .= '<strong>' . e($causer) . '</strong> — <span style="color:#6b7280;">' . $date . '</span><br>';

                                if ($activity->event === 'created') {
                                    $score = $props['attributes']['score'] ?? null;
                                    $html .= 'Nilai diberikan: <strong>' . $scoreLabel($score) . '</strong>';
                                    $notes = $props['attributes']['notes'] ?? null;
                                    if ($notes) $html .= '<br>Catatan: ' . e($notes);
                                } elseif ($activity->event === 'updated') {
                                    $old = $props['old'] ?? [];
                                    $new = $props['attributes'] ?? [];
                                    if (isset($old['score'], $new['score'])) {
                                        $html .= 'Nilai diubah: ' . $scoreLabel($old['score']) . ' → <strong>' . $scoreLabel($new['score']) . '</strong>';
                                    }
                                    if (array_key_exists('notes', $old)) {
                                        $html .= '<br>Catatan: ' . e($old['notes'] ?: '-') . ' → <strong>' . e($new['notes'] ?: '-') . '</strong>';
                                    }
                                }
                                $html .= '</div>';
                            }
                        }
                        $html .= '</div>';

                        return new \Illuminate\Support\HtmlString($html);
                    }),
            ])
            ->bulkActions([]);

    }
}
