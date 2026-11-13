# Qualora

Qualora is a scholarship discovery and application tracking platform for enrolled S1 students. Students can explore verified scholarships, evaluate their eligibility against criteria, prepare required documents, and track their applications after applying on the official publisher website with deadline & announcement countdowns.

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
| Registration, login, email verification (Laravel Breeze) | Completed | Dashboard, scholarship exploration, and application tracking require a verified email. |
| Student Profile & Sub-tables | Completed | Core personal & academic data in `profiles` (GPA, semester, major, faculty, age, campus, accreditation, financial need), plus dedicated sub-tables for `user_study_plans`, `user_language`, and `user_experiences`. Users can delete their own account. |
| Scholarship Catalog & Browsing | Completed | Search by name or provider, filter by status and type (full/partial), pagination. Redesigned with consistent bottom-aligned dual buttons, status pills, and countdowns. |
| Scholarship Detail Page & Hero Card | Completed | Redesigned according to team design layout: top status pills, bookmark ribbon, big countdown highlight, two-column dates (Deadline & Announcement), and direct external portal link. Includes interactive document checklist and selection stepper timeline. |
| Bookmark / Saved Scholarships | Completed | Quick save/unsave toggle, with saved filter available on the scholarship catalog and dashboard. |
| External Application Tracker & Monitoring | Completed | Dedicated tracker (`/my-applications`) and dashboard widget. Users can log external submissions, update stages (`preparing`, `submitted`, `interview`, `accepted`, `rejected`), track announcement dates, and add registration numbers & personal notes. |
| Eligibility Check & Rule Evaluation | Completed (Initial Engine) | Dynamic comparison on scholarship detail page: compares user profile (GPA, semester, age, financial need, accreditation) against scholarship requirements with visual pass/fail/unknown status badges. |
| Admin Scholarship Management | Completed | Admin dashboard to create, view, edit, and archive scholarships, protected by admin middleware. |
| AI Integration (Google Gemini) | Planned | Free-text / CV profile parsing, AI scholarship explanation & checklist generator, and admin automated link ingestion. |
| Advanced Standalone Scoring Engine | Planned | Extended scoring and match percentage breakdown (`EligibilityService`). |

## Architecture & Database Schema (Supabase PostgreSQL)

Qualora utilizes a normalized, relational domain model on Supabase PostgreSQL:

```
├── 1. USER DOMAIN
│   ├── users (Authentication, role is_admin)
│   ├── profiles (Personal & academic info: GPA, semester, faculty, accreditation, etc.)
│   ├── user_study_plans (Desired major, study field, preferred destination)
│   ├── user_language (Language proficiency, certificate type & score)
│   └── user_experiences (Organizational & work experience history)
│
├── 2. SCHOLARSHIP DOMAIN
│   ├── scholarships (Core info, provider, official_url, application_link, deadline, announcement_date)
│   ├── scholarship_eligibilities (GPA, semester range, age limits, financial need, accreditation requirements)
│   ├── scholarship_study_requirements (Eligible majors, study field categories, study modes)
│   ├── scholarship_language_requirements (Required language, certificate type & minimum score)
│   ├── scholarship_benefits (Tuition, living allowance, mentorship, book allowance)
│   ├── scholarship_documents (Required documents, descriptions, and template URLs)
│   └── scholarship_selections (Stage names, order, and estimated dates for selection timeline)
│
└── 3. INTERACTION & TRACKING DOMAIN
    ├── saved_scholarships (User wishlists/bookmarks)
    └── user_applications (Application status tracker, submission date, registration number, notes)
```

## Tech Stack

- **Frontend:** Laravel Blade Templates, Tailwind CSS, Alpine.js (Vite asset pipeline)
- **Backend:** PHP 8.4+ and Laravel 11/13 (MVC Architecture)
- **Authentication:** Laravel Breeze
- **Database:** Supabase (Managed PostgreSQL) connected via Laravel Eloquent ORM
- **Testing:** PHPUnit (52/52 automated unit & feature tests passing)
- **Planned Deployment:** Google Gemini API (gemini-2.5-flash via server-side HTTP Client), Domainesia Hosting

## Getting Started

### Prerequisites

You need PHP 8.3+ (with `pdo_pgsql`), Composer, and Node.js.

```bash
git clone <repo-url>
cd Qualora
composer install
npm install
cp .env.example .env
php artisan key:generate
```

### Database Configuration

Edit your `.env` file to connect to the team's Supabase PostgreSQL instance:

```env
DB_CONNECTION=pgsql
DB_HOST=<supabase-pooler-host>
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=<supabase-user>
DB_PASSWORD=<supabase-password>
DB_SSLMODE=require
```

### Running the Application

Run the application in two terminal tabs:

```bash
php artisan serve   # http://127.0.0.1:8000
npm run dev         # Compiles Tailwind CSS & Alpine.js
```

### Seeders & Sample Data

To populate sample scholarships with complete eligibility, benefits, documents, and selection stages:

```bash
php artisan db:seed --class=ScholarshipSeeder
```

To create the default admin account (`admin01@gmail.com`):

```bash
php artisan db:seed --class=AdminUserSeeder
```

### Running Automated Tests

Qualora runs automated tests on an in-memory SQLite database to ensure the Supabase production database is never touched during testing:

```bash
php artisan test
```

Currently, **52/52 tests are passing**, covering Authentication, Profile Management, Scholarship Catalog & Details, Admin Controls, and the Application Tracker.

## Important Notes for the Team

- **Shared Database:** The whole team shares one Supabase instance.
  - Run `php artisan migrate` after pulling new commits.
  - **Never** run `migrate:fresh`, `migrate:refresh`, or `db:wipe` against the shared database.
- **External Application Flow:** Qualora acts as an informational aggregator and personal tracking platform. Users apply through the official publisher portal (`Web resmi`), then log their submission on Qualora (`Catat Pendaftaran`) to monitor countdowns and results.
- **Timezone:** All timestamps and countdown calculations use `Asia/Jakarta`.
