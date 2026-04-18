<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BerkasAuditorResource\Pages;
use App\Models\Berkas;
use App\Models\Cycle;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BerkasAuditorResource extends Resource
{
    protected static ?string $model = Berkas::class;

    protected static ?string $navigationIcon  = 'heroicon-o-folder-arrow-down';
    protected static ?string $navigationGroup = 'Berkas';
    protected static ?string $navigationLabel = 'Berkas Auditor';
    protected static ?string $modelLabel      = 'Berkas Auditor';
    protected static ?string $pluralModelLabel = 'Berkas Auditor';
    protected static ?string $slug            = 'berkas-auditor';

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('super_admin') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('target_role', Berkas::TARGET_AUDITOR);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Hidden::make('target_role')
                ->default(Berkas::TARGET_AUDITOR),

            Forms\Components\Hidden::make('uploaded_by')
                ->default(fn () => Auth::id()),

            Forms\Components\TextInput::make('judul')
                ->label('Judul')
                ->required()
                ->maxLength(255),

            Forms\Components\Textarea::make('deskripsi')
                ->label('Deskripsi')
                ->rows(3)
                ->columnSpanFull(),

            Forms\Components\Select::make('cycles_id')
                ->label('Siklus (opsional)')
                ->options(Cycle::orderByDesc('year')->pluck('name', 'id'))
                ->searchable()
                ->placeholder('Berlaku semua siklus'),

            Forms\Components\Select::make('target_id')
                ->label('Target Auditor')
                ->options(fn () => User::role('auditor')->orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->placeholder('Semua Auditor')
                ->helperText('Kosongkan untuk mengirim ke semua auditor.'),

            Forms\Components\FileUpload::make('file_path')
                ->label('File')
                ->disk('public')
                ->directory('berkas/auditor')
                ->preserveFilenames()
                ->downloadable()
                ->openable()
                ->required()
                ->maxSize(51200)
                ->columnSpanFull()
                ->afterStateUpdated(function ($state, Forms\Set $set) {
                    if (! $state) {
                        return;
                    }
                    $set('file_name', method_exists($state, 'getClientOriginalName') ? $state->getClientOriginalName() : basename((string) $state));
                    $set('file_size', method_exists($state, 'getSize') ? $state->getSize() : null);
                }),

            Forms\Components\Hidden::make('file_name'),
            Forms\Components\Hidden::make('file_size'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('judul')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('targetUser.name')
                    ->label('Target')
                    ->default('Semua Auditor')
                    ->badge()
                    ->color(fn ($state) => $state === 'Semua Auditor' ? 'gray' : 'info'),

                Tables\Columns\TextColumn::make('cycle.name')
                    ->label('Siklus')
                    ->default('—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('file_name')
                    ->label('Nama File')
                    ->limit(40),

                Tables\Columns\TextColumn::make('file_size_formatted')
                    ->label('Ukuran')
                    ->state(fn (Berkas $r) => $r->file_size_formatted),

                Tables\Columns\TextColumn::make('uploader.name')
                    ->label('Diupload oleh')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cycles_id')
                    ->label('Siklus')
                    ->options(Cycle::orderByDesc('year')->pluck('name', 'id')),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Unduh')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->iconButton()
                    ->url(fn (Berkas $r) => Storage::disk('public')->url($r->file_path), shouldOpenInNewTab: true),

                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBerkasAuditors::route('/'),
            'create' => Pages\CreateBerkasAuditor::route('/create'),
            'edit'   => Pages\EditBerkasAuditor::route('/{record}/edit'),
        ];
    }
}
