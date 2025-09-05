<?php

namespace Database\Seeders;

use App\Models\BNB;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * BNB Database Seeder
 * 
 * Seeds the database with sample BNB listings for development and testing.
 * Creates various types of properties with realistic data.
 */
class BNBSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have users to associate BNBs with
        $users = User::all();
        
        if ($users->isEmpty()) {
            // Create some default users if none exist
            $users = collect([
                User::factory()->create([
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'role' => 'admin'
                ]),
                User::factory()->create([
                    'name' => 'Jane Smith',
                    'email' => 'jane@example.com',
                    'role' => 'user'
                ]),
                User::factory()->create([
                    'name' => 'Mike Johnson',
                    'email' => 'mike@example.com',
                    'role' => 'user'
                ])
            ]);
        }

        // Create 50 regular BNBs
        BNB::factory()
            ->count(50)
            ->create()
            ->each(function ($bnb) use ($users) {
                $bnb->user_id = $users->random()->id;
                $bnb->save();
            });

        // Create 10 featured luxury BNBs
        BNB::factory()
            ->count(10)
            ->luxury()
            ->featured()
            ->create()
            ->each(function ($bnb) use ($users) {
                $bnb->user_id = $users->random()->id;
                $bnb->save();
            });

        // Create 15 budget-friendly BNBs
        BNB::factory()
            ->count(15)
            ->budget()
            ->available()
            ->create()
            ->each(function ($bnb) use ($users) {
                $bnb->user_id = $users->random()->id;
                $bnb->save();
            });

        // Create 5 unavailable BNBs
        BNB::factory()
            ->count(5)
            ->unavailable()
            ->create()
            ->each(function ($bnb) use ($users) {
                $bnb->user_id = $users->random()->id;
                $bnb->save();
            });

        $this->command->info('BNB seeder completed successfully!');
        $this->command->info('Created:');
        $this->command->info('- 50 regular BNBs');
        $this->command->info('- 10 luxury/featured BNBs');
        $this->command->info('- 15 budget-friendly BNBs');
        $this->command->info('- 5 unavailable BNBs');
        $this->command->info('Total: ' . BNB::count() . ' BNBs');
    }
}
