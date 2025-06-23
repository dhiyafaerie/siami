<?php

namespace App\Filament\Resources\AuditorResource\Pages;

use App\Filament\Resources\AuditorResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;
class CreateAuditor extends CreateRecord
{
    protected static string $resource = AuditorResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Create the user first
        $user = User::create([
            'name' => $data['user']['name'],
            'email' => $data['user']['email'],
            'password' => bcrypt($data['user']['password']),
        ]);

        // Assign user_id to prodi
        $data['users_id'] = $user->id;

        // Remove nested user data from faculty data
        unset($data['user']);

        return $data;
    }
}
