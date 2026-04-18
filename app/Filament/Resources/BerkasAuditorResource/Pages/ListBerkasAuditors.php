<?php

namespace App\Filament\Resources\BerkasAuditorResource\Pages;

use App\Filament\Resources\BerkasAuditorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBerkasAuditors extends ListRecords
{
    protected static string $resource = BerkasAuditorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Upload Berkas'),
        ];
    }
}
