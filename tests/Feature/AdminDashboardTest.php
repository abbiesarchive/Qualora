<?php

namespace Tests\Feature;

use App\Models\Scholarship;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_can_sign_in_and_is_redirected_to_admin_dashboard(): void
    {
        $this->seed(AdminUserSeeder::class);

        $this->post('/login', [
            'email' => 'admin01@gmail.com',
            'password' => 'admin01',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Scholarship management');
    }

    public function test_admin_dashboard_shows_scholarships_and_placeholder_actions(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        Scholarship::forceCreate([
            'name' => 'Merit Award',
            'provider' => 'Example Foundation',
            'min_gpa' => 3.25,
            'open_date' => today(),
            'scholarship_type' => 'full',
            'official_url' => 'https://example.org/apply',
            'last_verified' => today(),
            'year' => 2026,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Merit Award')
            ->assertSee('Example Foundation')
            ->assertSee('3.25')
            ->assertSee('Add scholarship')
            ->assertSee('Edit')
            ->assertSee('Delete')
            ->assertSee('disabled', false);
    }

    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }
}
