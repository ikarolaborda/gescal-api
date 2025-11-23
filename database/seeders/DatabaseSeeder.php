<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed reference data first (roles, federation units, etc.)
        $this->call([
            RoleSeeder::class,
            ReferenceDataSeeder::class,
            BenefitProgramSeeder::class,
        ]);

        // Create test users with different roles (only in non-production)
        if (! app()->environment('production')) {
            $this->createTestUsers();
        }
    }

    /**
     * Create test users for development.
     */
    protected function createTestUsers(): void
    {
        // Create admin user
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@gescal.test',
        ]);

        // Create coordinator user
        User::factory()->coordinator()->create([
            'name' => 'Coordinator User',
            'email' => 'coordinator@gescal.test',
        ]);

        // Create social worker user
        User::factory()->socialWorker()->create([
            'name' => 'Social Worker User',
            'email' => 'social@gescal.test',
        ]);

        $this->command->info('Test users created successfully.');
    }
}
