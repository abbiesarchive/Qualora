<?php

namespace Tests\Feature;

use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminAiExtractScholarshipsTest extends TestCase
{
    use RefreshDatabase;

    private const TEXT = 'Beasiswa Unggulan 2026 offered by Example Foundation for S1 students with GPA 3.25 and above.';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.gemini.key' => 'test-key']);
    }

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();

        return $user;
    }

    private function reply(array $items): array
    {
        return ['candidates' => [['content' => ['parts' => [['text' => json_encode(['scholarships' => $items])]]]]]];
    }

    private function item(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'Beasiswa Unggulan', 'provider' => 'Example Foundation', 'campus' => null, 'level' => 'S1',
            'min_gpa' => 3.25, 'min_semester' => null, 'max_semester' => null, 'max_age' => null,
            'requires_financial_need' => null, 'majors' => [], 'allowed_nationalities' => [],
            'english_test_type' => null, 'min_english_score' => null, 'scholarship_type' => null,
            'is_abroad' => null, 'other_requirements' => null, 'required_documents' => ['KTP'],
            'contact_info' => null,
            'benefits' => null, 'open_date' => null, 'deadline' => '2026-12-31', 'official_url' => null, 'year' => 2026,
        ];
    }

    private function row(array $overrides = []): array
    {
        return $overrides + [
            'include' => '1', 'name' => 'Beasiswa Unggulan', 'provider' => 'Example Foundation', 'year' => 2026,
            'is_abroad' => '0', 'is_active' => '1', 'min_gpa' => '3.25', 'required_documents' => 'KTP, CV',
            'official_url' => 'https://example.org/beasiswa', 'last_verified' => '2026-11-20',
        ];
    }

    public function test_guests_and_students_cannot_extract(): void
    {
        Http::fake();

        $this->post(route('admin.ai.extract-scholarships'), ['text' => self::TEXT])->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())
            ->post(route('admin.ai.extract-scholarships'), ['text' => self::TEXT])->assertForbidden();

        Http::assertNothingSent();
    }

    public function test_extraction_stages_drafts_but_saves_nothing(): void
    {
        Http::fake(['*' => Http::response($this->reply([$this->item()]))]);

        $this->actingAs($this->admin())
            ->post(route('admin.ai.extract-scholarships'), ['text' => self::TEXT])
            ->assertRedirect(route('admin.scholarships.import'))
            ->assertSessionHas('admin_import.items.0.name', 'Beasiswa Unggulan');

        $this->assertSame(0, Scholarship::count(), 'AI output must never be saved without review');
    }

    public function test_review_page_shows_source_text_and_drafts(): void
    {
        Http::fake(['*' => Http::response($this->reply([$this->item()]))]);
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.ai.extract-scholarships'), ['text' => self::TEXT]);

        $this->get(route('admin.scholarships.import'))
            ->assertOk()
            ->assertSee('Example Foundation')
            ->assertSee('Source text')
            ->assertSee('Beasiswa Unggulan 2026 offered by');
    }

    public function test_bad_field_values_are_blanked_not_kept(): void
    {
        Http::fake(['*' => Http::response($this->reply([$this->item(['min_gpa' => 85, 'official_url' => 'not a url'])]))]);

        $this->actingAs($this->admin())
            ->post(route('admin.ai.extract-scholarships'), ['text' => self::TEXT])
            ->assertSessionHas('admin_import.items.0.name');

        $this->assertNull(session('admin_import.items.0.min_gpa'));
        $this->assertNull(session('admin_import.items.0.official_url'));
    }

    public function test_url_without_scheme_gets_https(): void
    {
        Http::fake(['*' => Http::response($this->reply([$this->item(['official_url' => 'example.org/beasiswa'])]))]);

        $this->actingAs($this->admin())
            ->post(route('admin.ai.extract-scholarships'), ['text' => self::TEXT])
            ->assertSessionHas('admin_import.items.0.official_url', 'https://example.org/beasiswa');
    }

    public function test_items_without_a_name_are_dropped(): void
    {
        Http::fake(['*' => Http::response($this->reply([$this->item(), $this->item(['name' => ''])]))]);

        $this->actingAs($this->admin())
            ->post(route('admin.ai.extract-scholarships'), ['text' => self::TEXT])
            ->assertSessionHas('admin_import.skipped', 1);

        $this->assertCount(1, session('admin_import.items'));
    }

    public function test_all_invalid_shows_an_error(): void
    {
        Http::fake(['*' => Http::response($this->reply([$this->item(['name' => ''])]))]);

        $this->actingAs($this->admin())
            ->post(route('admin.ai.extract-scholarships'), ['text' => self::TEXT])
            ->assertSessionHas('ai_error')
            ->assertSessionMissing('admin_import');
    }

    public function test_ai_failure_shows_a_friendly_error(): void
    {
        Http::fake(['*' => Http::response([], 500)]);

        $this->actingAs($this->admin())
            ->post(route('admin.ai.extract-scholarships'), ['text' => self::TEXT])
            ->assertRedirect(route('admin.scholarships.import'))
            ->assertSessionHas('ai_error');
    }

    public function test_saving_requires_official_url_and_last_verified(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.scholarships.import.store'), ['items' => [$this->row(['official_url' => '', 'last_verified' => ''])]])
            ->assertSessionHasErrors(['items.0.official_url', 'items.0.last_verified']);

        $this->assertSame(0, Scholarship::count());
    }

    public function test_admin_saves_reviewed_rows_and_only_ticked_ones(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.scholarships.import.store'), ['items' => [
                $this->row(),
                $this->row(['name' => 'Skipped One', 'include' => null]),
            ]])
            ->assertRedirect(route('admin.scholarships.index'));

        $saved = Scholarship::sole();
        $this->assertSame('Beasiswa Unggulan', $saved->name);
        $this->assertSame(['KTP', 'CV'], $saved->required_documents);
    }

    public function test_students_cannot_save_import(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.scholarships.import.store'), ['items' => [$this->row()]])
            ->assertForbidden();

        $this->assertSame(0, Scholarship::count());
    }

    public function test_majors_with_commas_in_their_names_stay_whole(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.scholarships.import.store'), ['items' => [
                $this->row(['majors' => "Biology and health\nFrench history, language, and civilization"]),
            ]])
            ->assertRedirect(route('admin.scholarships.index'));

        $this->assertSame(
            ['Biology and health', 'French history, language, and civilization'],
            Scholarship::sole()->majors,
        );
    }

    public function test_contact_info_is_saved(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.scholarships.import.store'), ['items' => [
                $this->row(['contact_info' => "help@example.org\n@beasiswa.official"]),
            ]])
            ->assertRedirect(route('admin.scholarships.index'));

        $this->assertSame("help@example.org\n@beasiswa.official", Scholarship::sole()->contact_info);
    }

    public function test_fetch_loads_page_text_into_the_box_without_calling_ai(): void
    {
        $html = '<html><head><style>x{}</style></head><body><nav>Menu</nav><h1>Beasiswa Unggulan</h1><p>'.str_repeat('Requirements apply to S1 students with GPA 3.25. ', 8).'</p></body></html>';
        Http::fake(['*' => Http::response($html, 200, ['Content-Type' => 'text/html'])]);

        $this->actingAs($this->admin())
            ->post(route('admin.scholarships.import.fetch'), ['url' => 'http://93.184.216.34/beasiswa'])
            ->assertRedirect(route('admin.scholarships.import'))
            ->assertSessionHas('import_notice');

        $this->assertStringContainsString('Beasiswa Unggulan', session()->getOldInput('text'));
        $this->assertStringContainsString('Source URL: http://93.184.216.34/beasiswa', session()->getOldInput('text'));
        $this->assertStringNotContainsString('Menu', session()->getOldInput('text'));
        Http::assertSentCount(1);
    }

    public function test_fetch_refuses_internal_addresses(): void
    {
        Http::fake();

        foreach (['http://127.0.0.1/admin', 'http://10.0.0.5/x', 'http://169.254.169.254/latest'] as $url) {
            $this->actingAs($this->admin())
                ->post(route('admin.scholarships.import.fetch'), ['url' => $url])
                ->assertSessionHas('import_error');
        }

        Http::assertNothingSent();
    }

    public function test_fetch_is_admin_only(): void
    {
        Http::fake();

        $this->actingAs(User::factory()->create())
            ->post(route('admin.scholarships.import.fetch'), ['url' => 'http://93.184.216.34/'])
            ->assertForbidden();

        Http::assertNothingSent();
    }

    public function test_fetch_rejects_pages_with_no_readable_text(): void
    {
        Http::fake(['*' => Http::response('<html><body><div id="app"></div><script>x()</script></body></html>', 200, ['Content-Type' => 'text/html'])]);

        $this->actingAs($this->admin())
            ->post(route('admin.scholarships.import.fetch'), ['url' => 'http://93.184.216.34/'])
            ->assertSessionHas('import_error');
    }
}
