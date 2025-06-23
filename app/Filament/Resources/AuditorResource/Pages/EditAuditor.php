<?php

namespace App\Filament\Resources\AuditorResource\Pages;

use App\Filament\Resources\AuditorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAuditor extends EditRecord
{
    protected static string $resource = AuditorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
