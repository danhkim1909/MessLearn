<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'user1@example.com'],
            [
                'name' => 'Nguyen Van A',
                'password' => bcrypt('password'),
                'role' => 'user',
            ]
        );

        User::firstOrCreate(
            ['email' => 'user2@example.com'],
            [
                'name' => 'Tran Thi B',
                'password' => bcrypt('password'),
                'role' => 'user',
            ]
        );
    }
}
