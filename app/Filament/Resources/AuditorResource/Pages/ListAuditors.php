<?php

namespace App\Filament\Resources\AuditorResource\Pages;

use App\Filament\Resources\AuditorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAuditors extends ListRecords
{
    protected static string $resource = AuditorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
