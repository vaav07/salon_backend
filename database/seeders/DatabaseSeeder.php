<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();


        // \App\Models\Admin::factory()->create([
        //     'name' => 'Test Admin',
        //     'username' => 'admin1',
        //     'email' => 'admin@example.com',
        //     'password' => Hash::make('123456')
        // ]);
        \App\Models\User::factory()->create([
            'name' => 'Test User2',
            'admin_id' => 1,
            'username' => "user2",
            'email' => 'test2@example.com',
            'password' => Hash::make('123456')
        ]);
        // \App\Models\SubAdmin::factory()->create([
        //     'name' => 'Test SubAdmin',
        //     'email' => 'subAdmin@example.com',
        //     'password' => Hash::make('123456')
        // ]);
    }
}
