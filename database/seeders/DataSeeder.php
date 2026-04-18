<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DataSeeder extends Seeder
{
    public function run(): void
    {
        $sql = file_get_contents(__DIR__ . '/data_dump.sql');

        $tables = [
            'role_has_permissions',
            'model_has_roles',
            'model_has_permissions',
            'auditscores',
            'nonconformities',
            'prodiattachments',
            'standards',
            'auditors',
            'prodis',
            'faculties',
            'berkas',
            'cycles',
            'permissions',
            'roles',
            'users',
        ];

        Schema::disableForeignKeyConstraints();

        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        DB::unprepared($sql);

        Schema::enableForeignKeyConstraints();
    }
}
