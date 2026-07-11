<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@lumisktechnology.com'],
            [
                'name' => 'Lumisk Admin',
                'password' => Hash::make('admin123'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 'role' is not mass-assignable — set it explicitly.
        $admin->role = 'super_admin';
        $admin->save();
    }
}
