<?php

namespace App\Filament\Resources\BerkasAuditorResource\Pages;

use App\Filament\Resources\BerkasAuditorResource;
use App\Models\Berkas;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateBerkasAuditor extends CreateRecord
{
    protected static string $resource = BerkasAuditorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['target_role'] = Berkas::TARGET_AUDITOR;

        if (! empty($data['file_path'])) {
            $path = $data['file_path'];
            if (empty($data['file_name'])) {
                $data['file_name'] = basename($path);
            }
            if (empty($data['file_size']) && Storage::disk('public')->exists($path)) {
                $data['file_size'] = Storage::disk('public')->size($path);
            }
        }

        return $data;
    }
}
