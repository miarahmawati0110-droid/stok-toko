<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create(['nama' => 'Admin Toko',   'username' => 'admin',   'password' => 'admin123',  'role' => 'Admin']);
        User::create(['nama' => 'Siti Aminah',  'username' => 'kasir1',  'password' => 'kasir123',  'role' => 'Kasir']);
        User::create(['nama' => 'Budi Santoso', 'username' => 'gudang1', 'password' => 'gudang123', 'role' => 'Gudang']);
    }
}