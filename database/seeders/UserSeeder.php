<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['mobile' => '92062344'],
            ['name' => 'Torkel', 'email' => 'torkel@fargerike.no', 'sender_id' => 'Fargerike', 'is_active' => true, 'is_admin' => false]
        );

        User::firstOrCreate(
            ['mobile' => '92000000'],
            ['name' => 'Admin', 'email' => 'admin@example.com', 'sender_id' => 'Admin', 'is_active' => true, 'is_admin' => true]
        );
    }
}
