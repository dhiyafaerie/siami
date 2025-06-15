<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Create a super admin user
        $user = User::firstOrCreate([
            'email' => 'superadmin@gmail.com'
        ], [
            'name' => 'Super Admin',
            'password' => bcrypt('123456'),
        ]);
        $this->command->call('shield:super-admin');
        $this->command->call('filament:clear-cached-components');
        $this->command->call('shield:generate', [
        '--all' => true
        ]);
        $this->command->call('icon:cache');
    }
}