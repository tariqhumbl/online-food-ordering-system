<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::create(['name' => 'admin']);
        $admin = User::create([
            'name' => 'Admin Account',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id
        ]);

        $managerRole = Role::create(['name' => 'manager']);
        $manager = User::create([
            'name' => 'manager Account',
            'email' => 'manager@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => $managerRole->id
        ]);

        $userRole = Role::create(['name' => 'user']);
        $user = User::create([
            'name' => 'User Account',
            'email' => 'user@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => $userRole->id
        ]);
    }
}