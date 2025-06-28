<?php

namespace App\Filament\Resources\ProdiattachmentResource\Pages;

use App\Filament\Resources\ProdiattachmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProdiattachment extends EditRecord
{
    protected static string $resource = ProdiattachmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
