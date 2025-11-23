<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Social Worker',
                'slug' => 'social_worker',
                'description' => 'Creates and submits requests, responds to document requests, manages cases and benefits',
                'is_system' => true,
            ],
            [
                'name' => 'Coordinator',
                'slug' => 'coordinator',
                'description' => 'Reviews, approves, or rejects benefit requests and case decisions',
                'is_system' => true,
            ],
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Full system access, can override decisions, manage users, and access all data including PII',
                'is_system' => true,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }

        $this->command->info('System roles seeded successfully.');
    }
}
