<?php

namespace App\Filament\Resources\BerkasAuditorResource\Pages;

use App\Filament\Resources\BerkasAuditorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditBerkasAuditor extends EditRecord
{
    protected static string $resource = BerkasAuditorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['file_path'])) {
            $path = $data['file_path'];
            if (empty($data['file_name'])) {
                $data['file_name'] = basename($path);
            }
            if (Storage::disk('public')->exists($path)) {
                $data['file_size'] = Storage::disk('public')->size($path);
            }
        }

        return $data;
    }
}
