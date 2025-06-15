<?php

namespace App\Filament\Resources\FacultyResource\Pages;

use App\Filament\Resources\FacultyResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;

class CreateFaculty extends CreateRecord
{
    protected static string $resource = FacultyResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Create the user first
        $user = User::create([
            'name' => $data['user']['name'],
            'email' => $data['user']['email'],
            'password' => bcrypt($data['user']['password']),
        ]);

        // Assign user_id to faculty
        $data['users_id'] = $user->id;

        // Remove nested user data from faculty data
        unset($data['user']);

        return $data;
    }
}
