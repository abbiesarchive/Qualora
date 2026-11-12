<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Enrich scholarships table with details, links, and dates
        Schema::table('scholarships', function (Blueprint $table) {
            if (!Schema::hasColumn('scholarships', 'provider_logo')) {
                $table->string('provider_logo')->nullable()->after('provider');
            }
            if (!Schema::hasColumn('scholarships', 'application_link')) {
                $table->string('application_link')->nullable()->after('official_url');
            }
            if (!Schema::hasColumn('scholarships', 'contact_email')) {
                $table->string('contact_email')->nullable()->after('application_link');
            }
            if (!Schema::hasColumn('scholarships', 'scholarship_category')) {
                $table->string('scholarship_category')->nullable()->default('Fully Funded')->after('scholarship_type'); // Fully Funded, Partial, etc.
            }
            if (!Schema::hasColumn('scholarships', 'application_cycle')) {
                $table->string('application_cycle')->nullable()->after('year'); // e.g. "2026/2027"
            }
            if (!Schema::hasColumn('scholarships', 'interview_date')) {
                $table->date('interview_date')->nullable()->after('deadline');
            }
            if (!Schema::hasColumn('scholarships', 'announcement_date')) {
                $table->date('announcement_date')->nullable()->after('interview_date');
            }
            if (!Schema::hasColumn('scholarships', 'program_start_date')) {
                $table->date('program_start_date')->nullable()->after('announcement_date');
            }
            if (!Schema::hasColumn('scholarships', 'description')) {
                $table->text('description')->nullable()->after('benefits');
            }
        });

        // 2. scholarship_eligibilities
        if (!Schema::hasTable('scholarship_eligibilities')) {
            Schema::create('scholarship_eligibilities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('scholarship_id')->constrained()->cascadeOnDelete();
                $table->decimal('min_gpa', 3, 2)->nullable();
                $table->unsignedTinyInteger('min_semester')->nullable();
                $table->unsignedTinyInteger('max_semester')->nullable();
                $table->unsignedTinyInteger('min_age')->nullable();
                $table->unsignedTinyInteger('max_age')->nullable();
                $table->string('eligible_gender')->nullable()->default('all'); // all, male, female
                $table->json('allowed_nationalities')->nullable();
                $table->boolean('requires_financial_need')->nullable();
                $table->string('min_university_accreditation')->nullable();
                $table->json('allowed_universities')->nullable();
                $table->boolean('requires_work_experience')->default(false);
                $table->decimal('min_experience_years', 3, 1)->nullable();
                $table->text('additional_notes')->nullable();
                $table->timestamps();
            });
        }

        // 3. scholarship_study_requirements
        if (!Schema::hasTable('scholarship_study_requirements')) {
            Schema::create('scholarship_study_requirements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('scholarship_id')->constrained()->cascadeOnDelete();
                $table->string('eligible_major')->nullable();
                $table->string('study_field_category')->nullable(); // Tech, Business, Medical, etc.
                $table->string('study_country')->nullable();
                $table->string('eligible_university')->nullable();
                $table->string('study_mode')->nullable(); // Online, On-campus, Hybrid
                $table->string('study_duration')->nullable(); // e.g. "4 years", "1 semester"
                $table->string('requirement_status')->default('Required'); // Required, Preferred, Optional
                $table->text('additional_notes')->nullable();
                $table->timestamps();
            });
        }

        // 4. scholarship_language_requirements
        if (!Schema::hasTable('scholarship_language_requirements')) {
            Schema::create('scholarship_language_requirements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('scholarship_id')->constrained()->cascadeOnDelete();
                $table->string('language_name');
                $table->string('certificate_type')->nullable(); // IELTS, TOEFL, TOPIK, etc.
                $table->decimal('minimum_score', 5, 1)->nullable();
                $table->string('minimum_level')->nullable(); // B2, C1, Advanced, etc.
                $table->string('requirement_status')->default('Required'); // Required, Preferred, Optional
                $table->unsignedSmallInteger('validity_period_months')->nullable();
                $table->text('additional_notes')->nullable();
                $table->timestamps();
            });
        }

        // 5. scholarship_benefits
        if (!Schema::hasTable('scholarship_benefits')) {
            Schema::create('scholarship_benefits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('scholarship_id')->constrained()->cascadeOnDelete();
                $table->string('benefit_type'); // Tuition, Living Allowance, Accommodation, Travel, etc.
                $table->string('benefit_name');
                $table->string('coverage_status')->nullable(); // Fully Covered, Partially Covered, Not Covered
                $table->decimal('amount', 14, 2)->nullable();
                $table->string('currency', 10)->default('IDR');
                $table->text('benefit_description')->nullable();
                $table->timestamps();
            });
        }

        // 6. scholarship_documents
        if (!Schema::hasTable('scholarship_documents')) {
            Schema::create('scholarship_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('scholarship_id')->constrained()->cascadeOnDelete();
                $table->string('document_name');
                $table->text('document_description')->nullable();
                $table->boolean('is_required')->default(true);
                $table->string('template_url')->nullable();
                $table->text('additional_notes')->nullable();
                $table->timestamps();
            });
        }

        // 7. scholarship_selections
        if (!Schema::hasTable('scholarship_selections')) {
            Schema::create('scholarship_selections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('scholarship_id')->constrained()->cascadeOnDelete();
                $table->string('stage_name'); // Seleksi Berkas, Wawancara, dll
                $table->unsignedTinyInteger('stage_order')->default(1);
                $table->date('estimated_date')->nullable();
                $table->text('stage_description')->nullable();
                $table->boolean('is_required')->default(true);
                $table->text('additional_notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarship_selections');
        Schema::dropIfExists('scholarship_documents');
        Schema::dropIfExists('scholarship_benefits');
        Schema::dropIfExists('scholarship_language_requirements');
        Schema::dropIfExists('scholarship_study_requirements');
        Schema::dropIfExists('scholarship_eligibilities');
    }
};
