<?php

namespace App\Filament\Resources\ProdiaatachmentResource\Pages;

use App\Filament\Resources\ProdiaatachmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProdiaatachments extends ListRecords
{
    protected static string $resource = ProdiaatachmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
