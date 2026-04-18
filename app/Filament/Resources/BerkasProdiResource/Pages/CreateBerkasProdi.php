<?php

namespace App\Filament\Resources\BerkasProdiResource\Pages;

use App\Filament\Resources\BerkasProdiResource;
use App\Models\Berkas;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateBerkasProdi extends CreateRecord
{
    protected static string $resource = BerkasProdiResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['target_role'] = Berkas::TARGET_PRODI;

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
