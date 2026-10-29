<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'admin'])->get('/_admin-probe', fn () => 'ok');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/_admin-probe')->assertRedirect('/login');
    }

    public function test_regular_user_gets_403(): void
    {
        $this->actingAs(User::factory()->create())->get('/_admin-probe')->assertForbidden();
    }

    public function test_admin_can_access(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $this->actingAs($admin)->get('/_admin-probe')->assertOk();
    }

    public function test_is_admin_cannot_be_mass_assigned(): void
    {
        $user = User::create([
            'name' => 'Sneaky',
            'email' => 'sneaky@example.com',
            'password' => 'password',
            'is_admin' => true,
        ]);

        $this->assertFalse($user->fresh()->is_admin);
    }
}
