<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Seed reference data after database refresh (for tests using RefreshDatabase)
        if (in_array(\Illuminate\Foundation\Testing\RefreshDatabase::class, class_uses_recursive($this))) {
            $this->seedReferenceData();
        }
    }

    protected function seedReferenceData(): void
    {
        $this->seed(\Database\Seeders\ReferenceDataSeeder::class);
    }
}
