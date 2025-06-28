<?php

namespace App\Filament\Resources\ProdiattachmentResource\Pages;

use App\Filament\Resources\ProdiattachmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProdiattachments extends ListRecords
{
    protected static string $resource = ProdiattachmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
