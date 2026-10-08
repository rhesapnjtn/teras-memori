<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
        ]);

        $admin = User::updateOrCreate(
            [
                'email' => 'admin@teras-memori.test',
            ],
            [
                'name' => 'Admin Teras Memori',
                'password' => Hash::make('password'),
            ]
        );

        $admin->syncRoles(['admin']);

        $this->call([
            ServiceSeeder::class,
            PortfolioSeeder::class,
            CustomerSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
