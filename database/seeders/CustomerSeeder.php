<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        Customer::create([
            'name' => 'Andi Pratama',
            'email' => 'andi@example.com',
            'phone' => '081234567890',
            'address' => 'Bandung, Jawa Barat',
        ]);

        Customer::create([
            'name' => 'Siti Amelia',
            'email' => 'siti@example.com',
            'phone' => '081298765432',
            'address' => 'Jakarta, Indonesia',
        ]);

        Customer::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '082112223333',
            'address' => 'Tangerang, Banten',
        ]);
    }
}
