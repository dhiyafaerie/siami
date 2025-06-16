<?php

namespace App\Filament\Resources\ProdiResource\Pages;

use App\Filament\Resources\ProdiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProdi extends EditRecord
{
    protected static string $resource = ProdiResource::class;

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
                    // only update password if it's filled
                    ...(filled($data['user']['password']) ? ['password' => $data['user']['password']] : []),
                ]);

                unset($data['user']); // Don't try to save this into the faculty table
            }

            return $data;
        }
}
