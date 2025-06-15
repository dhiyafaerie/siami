<?php

namespace App\Filament\Resources\FacultyResource\Pages;

use App\Filament\Resources\FacultyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFaculty extends EditRecord
{
    protected static string $resource = FacultyResource::class;

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
