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

        User::factory()->create([
            'name' => 'IW Chiller',
            'email' => 'iw_chiller@mail.ru',
            'password' => bcrypt('1234567890')
        ]);
        User::factory()->create([
            'name' => 'aa@bb.com',
            'email' => 'aa@bb.com',
            'password' => bcrypt('1234567890')
        ]);
    }
}
