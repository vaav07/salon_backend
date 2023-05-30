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


        \App\Models\Admin::factory()->create([
            'name' => 'Test Admin',
            'username' => 'admin1',
            'email' => 'admin@example.com',
            'password' => Hash::make('123456')
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Test User',
            'admin_id' => 1,
            'username' => "user",
            'email' => 'test@example.com',
            'password' => Hash::make('123456')
        ]);

        // \App\Models\SubAdmin::factory()->create([
        //     'name' => 'Test SubAdmin',
        //     'email' => 'subAdmin@example.com',
        //     'password' => Hash::make('123456')
        // ]);

        // \App\Models\Customer::factory()->create([
        //     'admin_id' => 1,
        //     'user_id' => 1,
        //     'fullname' => 'Joker',
        //     'email' => "joker@gmail.com",
        //     'phone_no' => "78945632102",
        //     'address' => "Gotham",
        //     'state' => "Arkam",
        //     'city' => "New York",
        //     'pincode' => "789456",
        //     'dob' => "1990-12-12",
        //     'gender' => "male",
        // ]);

        // \App\Models\Employee::factory()->create([
        //     'admin_id' => 1,
        //     'user_id' => 1,
        //     'fullname' => 'Batman',
        //     'email' => "batman@gmail.com",
        //     'phone_no' => "78945632102",
        //     'address' => "Gotham",
        //     'state' => "Arkam",
        //     'city' => "New York",
        //     'pincode' => "789456",
        //     'dob' => "1990-12-12",
        //     'gender' => "male",
        // ]);

        // \App\Models\Service::factory()->create([
        //     'admin_id' => 1,
        //     'service_name' => 'Haircut',
        //     'description' => "cutting hair",
        //     'price' => "300",
        // ]);

        // \App\Models\Sale::factory()->create([
        //     'admin_id' => 1,
        //     'user_id' => 1,
        //     'employee_id' => 1,
        //     'customer_id' => 1,
        //     'sale_date' => "2023-05-24",
        //     'sale_time' => "23:59:59",
        //     'payment_method' => "Cash",
        //     'total_price' => 400,
        // ]);
    }
}
