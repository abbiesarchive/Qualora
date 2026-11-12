<?php

namespace Tests\Feature;

use App\Models\Scholarship;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminScholarshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_can_sign_in_and_is_redirected_to_scholarships(): void
    {
        $this->seed(AdminUserSeeder::class);

        $this->post('/login', [
            'email' => 'admin01@gmail.com',
            'password' => 'admin001',
        ])->assertRedirect(route('admin.scholarships.index'));

        $this->get(route('admin.scholarships.index'))
            ->assertOk()
            ->assertSee('Scholarships')
            ->assertSee('Add scholarship')
            ->assertDontSee('Dashboard');
    }

    public function test_non_admin_cannot_access_admin_scholarships(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.scholarships.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_update_view_and_delete_scholarship(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $this->actingAs($admin);

        $this->get(route('admin.scholarships.create'))
            ->assertOk()
            ->assertSee('Add scholarship')
            ->assertSee('Official URL');

        $this->post(route('admin.scholarships.store'), $this->validScholarship([
            'name' => 'Admin Added Grant',
            'majors' => 'Informatics, Computer Science',
            'required_documents' => "Transcript\nStudent ID",
        ]))->assertRedirect();

        $scholarship = Scholarship::where('name', 'Admin Added Grant')->firstOrFail();
        $this->assertSame(['Informatics', 'Computer Science'], $scholarship->majors);
        $this->assertSame(['Transcript', 'Student ID'], $scholarship->required_documents);

        $this->get(route('admin.scholarships.show', $scholarship))
            ->assertOk()
            ->assertSee('Admin Added Grant')
            ->assertSee('Example Foundation');

        $this->get(route('admin.scholarships.edit', $scholarship))
            ->assertOk()
            ->assertSee('Save changes')
            ->assertSee('Admin Added Grant');

        $this->put(route('admin.scholarships.update', $scholarship), $this->validScholarship([
            'name' => 'Admin Updated Grant',
        ]))->assertRedirect(route('admin.scholarships.show', $scholarship));

        $this->assertDatabaseHas('scholarships', ['id' => $scholarship->id, 'name' => 'Admin Updated Grant']);

        $this->delete(route('admin.scholarships.destroy', $scholarship))
            ->assertRedirect(route('admin.scholarships.index'));

        $this->assertDatabaseMissing('scholarships', ['id' => $scholarship->id]);
    }

    public function test_scholarship_create_validation_prevents_duplicate_name_in_same_year(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $this->actingAs($admin);

        Scholarship::forceCreate($this->validScholarship(['name' => 'Existing Grant']));

        $this->from(route('admin.scholarships.create'))
            ->post(route('admin.scholarships.store'), $this->validScholarship(['name' => 'Existing Grant']))
            ->assertSessionHasErrors('name');
    }

    private function validScholarship(array $overrides = []): array
    {
        return array_replace([
            'name' => 'Test Grant',
            'provider' => 'Example Foundation',
            'scholarship_type' => 'full',
            'is_abroad' => '0',
            'official_url' => 'https://example.org/apply',
            'last_verified' => today()->toDateString(),
            'year' => today()->year,
            'is_active' => '1',
        ], $overrides);
    }
}
