<?php

namespace Tests\Feature;

use App\Models\Scholarship;
use App\Models\User;
use App\Services\EligibilityService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    private function scholarship(array $attrs = []): Scholarship
    {
        return Scholarship::forceCreate($attrs + [
            'name' => 'Locale Test', 'provider' => 'Test Foundation', 'level' => 'S1',
            'official_url' => 'https://example.org', 'last_verified' => today(),
            'year' => 2026, 'is_active' => true, 'min_gpa' => 3.25,
        ]);
    }

    public function test_the_default_language_is_english(): void
    {
        $this->get('/')->assertOk()->assertSee('Discover where you')->assertDontSee('Temukan tempat');
    }

    public function test_a_guest_can_switch_language_and_the_choice_is_kept_in_a_cookie(): void
    {
        $this->from('/')->post('/language', ['locale' => 'id'])
            ->assertRedirect('/')
            ->assertCookie('locale', 'id');
    }

    public function test_the_landing_page_is_shown_in_indonesian_when_chosen(): void
    {
        $this->withCookie('locale', 'id')->get('/')
            ->assertOk()
            ->assertSee('Temukan tempat')
            ->assertSee('Cara kerjanya')
            ->assertDontSee('How it works');
    }

    public function test_an_unsupported_language_is_rejected(): void
    {
        $this->from('/')->post('/language', ['locale' => 'fr'])
            ->assertSessionHasErrors('locale')
            ->assertCookieMissing('locale');
    }

    public function test_an_unsupported_cookie_value_falls_back_to_english(): void
    {
        $this->withCookie('locale', 'fr')->get('/')->assertOk()->assertSee('How it works');
    }

    public function test_the_language_switcher_is_on_the_landing_page_login_and_app_pages(): void
    {
        $this->get('/')->assertSee('name="locale"', false);
        $this->get('/login')->assertSee('name="locale"', false);

        $this->actingAs(User::factory()->create())->get('/dashboard')->assertSee('name="locale"', false);
    }

    public function test_the_dashboard_and_tracker_are_translated(): void
    {
        $user = User::factory()->create(['name' => 'Bee Tan']);

        $this->actingAs($user)->withCookie('locale', 'id')->get('/dashboard')
            ->assertOk()
            ->assertSee('Selamat datang, Bee.')
            ->assertSee('Total pendaftaran')
            ->assertSee('Belum ada pendaftaran yang dilacak')
            ->assertDontSee('No applications tracked yet');
    }

    public function test_the_scholarship_list_is_translated(): void
    {
        $this->scholarship();

        $this->actingAs(User::factory()->create())->withCookie('locale', 'id')->get('/scholarships')
            ->assertOk()
            ->assertSee('Cari beasiswa')
            ->assertSee('1 beasiswa ditemukan')
            ->assertDontSee('Search scholarships');
    }

    public function test_eligibility_reasons_are_translated_but_the_numbers_stay_exact(): void
    {
        $user = User::factory()->create();
        $user->profile()->create(['gpa' => 3.20]);

        $this->actingAs($user)->withCookie('locale', 'id')->get(route('scholarships.show', $this->scholarship()))
            ->assertOk()
            ->assertSee('Hampir sampai')
            ->assertSee('IPK 3.20, dibutuhkan 3.25')
            ->assertSee('0 dari 1 syarat terpenuhi.')
            ->assertSee('bukan jaminan');
    }

    public function test_dates_follow_the_chosen_language(): void
    {
        $scholarship = $this->scholarship(['deadline' => '2030-12-15']);

        $this->actingAs(User::factory()->create())->get(route('scholarships.show', $scholarship))->assertSee('15 Dec 2030');
        $this->actingAs(User::factory()->create())->withCookie('locale', 'id')->get(route('scholarships.show', $scholarship))->assertSee('15 Des 2030');
    }

    public function test_validation_messages_are_translated(): void
    {
        $this->withCookie('locale', 'id')->from('/register')->post('/register', ['name' => 'A'])
            ->assertSessionHasErrors(['email' => 'email wajib diisi.']);
    }

    public function test_the_switch_never_changes_eligibility_results(): void
    {
        $user = User::factory()->create();
        $user->profile()->create(['gpa' => 3.20]);
        $scholarship = $this->scholarship();

        $service = app(EligibilityService::class);
        $english = $service->check($user->profile, $scholarship)['status'];
        app()->setLocale('id');
        $indonesian = $service->check($user->profile, $scholarship)['status'];

        $this->assertSame('almost', $english);
        $this->assertSame($english, $indonesian);
    }

    public function test_account_emails_are_translated(): void
    {
        app()->setLocale('id');
        $user = User::factory()->unverified()->create();

        $verify = (new VerifyEmail)->toMail($user);
        $reset = (new ResetPassword('token'))->toMail($user);

        $this->assertSame('Verifikasi alamat emailmu', $verify->subject);
        $this->assertSame('Reset kata sandimu', $reset->subject);
    }
}
