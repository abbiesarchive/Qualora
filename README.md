# Qualora

Qualora is a scholarship matching website for enrolled S1 students. Students enter their profile and browse scholarships, with the goal of seeing which ones they may be eligible for, with a clear reason for every requirement. Results are never a guarantee: always verify on the scholarship's official site.

ALP Cloud Computing, Team 9.

## Team

| Name | Role |
|---|---|
| Graciella Chelsea Lu | Frontend Developer |
| Chrisensia Abigail Gani | Backend Developer |
| Michelle Mercy Phe | Database Developer |

## Feature Progress

| Feature | Status | Notes |
|---|---|---|
| Registration, login, email verification (Laravel Breeze) | Completed | Dashboard and scholarship pages require a verified email. |
| Student profile | Completed | Eligibility fields (GPA, semester, major, age, campus, nationality, financial need, English test) and context fields (achievements, organizations, goals, scholarship preferences, circumstances). Users can delete their own account and data. |
| Scholarship browsing | Completed | Search by name or provider, filter by status and type (full/partial), pagination. |
| Scholarship detail page | Completed | |
| Save / unsave scholarships | Completed | Saved list is a filter on the scholarship list. |
| Admin scholarship management | Completed | Create, edit, view and delete scholarships. Admin-only, enforced by middleware. |
| Eligibility engine (`eligible` / `almost` / `not_eligible`, with reasons) | Not started | Planned as `EligibilityService`, with unit tests. |
| Results page, match summary on the detail page | Not started | Depends on the eligibility engine. |
| AI features (Gemini): profile extraction, explanation and checklist, admin extraction | Not started | |
| Deadline countdown and tracker | Not started | |

## Tech Stack

- **Frontend:** Laravel Blade, Tailwind CSS, Alpine.js (built with Vite)
- **Backend:** PHP 8.3+ and Laravel 13, MVC, single repository
- **Auth:** Laravel Breeze
- **Database:** Supabase (managed PostgreSQL), accessed through Laravel's `pgsql` driver and Eloquent
- **Tests:** PHPUnit
- **Planned:** Google Gemini API (called from the server only), hosting on Domainesia

## Getting Started

You need PHP 8.3+ (with `pdo_pgsql`), Composer, and Node.js.

```bash
git clone <repo-url>
cd Qualora
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Then edit `.env`. `.env.example` defaults to SQLite, so for the shared Supabase database replace the `DB_*` lines with the values the team shares privately (never commit them):

```
DB_CONNECTION=pgsql
DB_HOST=<supabase pooler host>
DB_PORT=<pooler port>
DB_DATABASE=postgres
DB_USERNAME=<postgres.project-ref>
DB_PASSWORD=<database password>
DB_SSLMODE=require
```

Run the app in two terminals:

```bash
php artisan serve   # http://127.0.0.1:8000
npm run dev         # Tailwind and Alpine assets (pages look unstyled without it)
```

For production assets use `npm run build`.

### Shared database: read this first

The whole team uses **one** Supabase database.

- Run `php artisan migrate` after pulling new migrations. It only applies missing ones.
- **Never** run `migrate:fresh`, `migrate:refresh`, `migrate:rollback` or `db:wipe` against it. They delete everyone's data.
- Do not run a plain `php artisan db:seed` on a database that already has data. `DatabaseSeeder` creates `test@example.com` (it fails if that user exists), and `ScholarshipSeeder` can create duplicates. To run one seeder, use `php artisan db:seed --class=AdminUserSeeder`.
- `php artisan test` is safe: `phpunit.xml` forces tests onto in-memory SQLite. Do not run `php artisan config:cache` while developing, because a cached config can bypass that setting.
- Need to experiment freely? Use a local Postgres or a second Supabase project.

### Admin account

`database/seeders/AdminUserSeeder.php` creates an admin (`admin01@gmail.com`, password in that file). Run it once with:

```bash
php artisan db:seed --class=AdminUserSeeder
```

Change this password before any deployment. To promote another account, use `php artisan tinker`:

```php
$u = App\Models\User::where('email', 'you@example.com')->first();
$u->is_admin = true;
$u->save();
```

`is_admin` is intentionally not mass-assignable, so users cannot make themselves admin.

### Tests

```bash
php artisan test
```

## Notes

- GPA is on a 4.0 scale. App timezone is `Asia/Jakarta`.
- Every scholarship shows its `last_verified` date and `official_url`. Always verify requirements on the official site.
- No scraping and no external scholarship API: all scholarship data lives in our own `scholarships` table.
- Row Level Security is enabled on the tables, so the Supabase public API (anon key) cannot read them. Laravel connects with its own database role.
- The scholarships currently seeded are sample data. Real data will be added and re-verified before the demo.

## Known Challenges

- Keeping scholarship data accurate and up to date (each row needs a verification date).
- Building transparent, deterministic eligibility logic that explains every rule, including near misses.
- Keeping a shared database safe across three developers.
