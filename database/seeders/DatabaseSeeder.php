<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DataSeeder::class,
        ]);

        $this->command->call('filament:clear-cached-components');
        $this->command->call('icon:cache');
    }
}
