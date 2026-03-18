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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['user'])) {
            $this->record->user->update([
                'name' => $data['user']['name'],
                'email' => $data['user']['email'],
                ...(filled($data['user']['password']) ? ['password' => bcrypt($data['user']['password'])] : []),
            ]);

            unset($data['user']);
        }

        return $data;
    }
}
