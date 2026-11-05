<?php

namespace Tests\Feature;

use App\Models\Scholarship;
use Database\Seeders\ScholarshipSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScholarshipSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeds_five_valid_scholarships_and_is_idempotent(): void
    {
        $this->seed(ScholarshipSeeder::class);
        $this->seed(ScholarshipSeeder::class);

        $this->assertSame(5, Scholarship::count());
        $this->assertSame(0, Scholarship::whereNull('official_url')->orWhereNull('last_verified')->count());
        $this->assertTrue(Scholarship::where('deadline', '<', today())->doesntExist());
    }
}
