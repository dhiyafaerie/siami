<?php

namespace App\Filament\Resources\ProdiResource\Pages;

use App\Filament\Resources\ProdiResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;

class CreateProdi extends CreateRecord
{
    protected static string $resource = ProdiResource::class;

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
