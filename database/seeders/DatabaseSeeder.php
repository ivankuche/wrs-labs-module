<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Guarantor;
use App\Models\Insurance;
use App\Models\Order;
use Illuminate\Database\Seeder;
use App\Models\Patient;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $patients= Patient::factory(30)->create();
        $insurances= Insurance::factory(10)->create();
        $guarantors= Guarantor::factory(30)->create();
        $orders= Order::factory(20)->create();
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
