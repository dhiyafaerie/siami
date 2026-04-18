<?php

namespace App\Filament\Resources\BerkasProdiResource\Pages;

use App\Filament\Resources\BerkasProdiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBerkasProdis extends ListRecords
{
    protected static string $resource = BerkasProdiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Upload Berkas'),
        ];
    }
}
