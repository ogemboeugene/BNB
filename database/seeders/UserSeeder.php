<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@bnb.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        // Create test regular user
        User::create([
            'name' => 'John Doe',
            'email' => 'user@bnb.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_USER,
            'email_verified_at' => now(),
        ]);

        // Create additional test users
        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_USER,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Mike Johnson',
            'email' => 'mike@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_USER,
            'email_verified_at' => now(),
        ]);

        $this->command->info('Test users created successfully!');
        $this->command->line('Admin User: admin@bnb.com / password123');
        $this->command->line('Regular User: user@bnb.com / password123');
        $this->command->line('Additional Users: jane@example.com / password123');
        $this->command->line('                mike@example.com / password123');
    }
}