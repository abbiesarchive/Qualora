<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ensure any missing profile columns exist
        Schema::table('profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('profiles', 'faculty')) {
                $table->string('faculty')->nullable()->after('major');
            }
            if (! Schema::hasColumn('profiles', 'gender')) {
                $table->string('gender')->nullable()->after('has_financial_need');
            }
            if (! Schema::hasColumn('profiles', 'country_of_residence')) {
                $table->string('country_of_residence')->nullable()->after('nationality');
            }
            if (! Schema::hasColumn('profiles', 'city_of_residence')) {
                $table->string('city_of_residence')->nullable()->after('country_of_residence');
            }
            if (! Schema::hasColumn('profiles', 'phone_number')) {
                $table->string('phone_number')->nullable()->after('city_of_residence');
            }
            if (! Schema::hasColumn('profiles', 'graduation_year')) {
                $table->unsignedSmallInteger('graduation_year')->nullable()->after('phone_number');
            }
            if (! Schema::hasColumn('profiles', 'graduation_status')) {
                $table->string('graduation_status')->nullable()->after('graduation_year');
            }
            if (! Schema::hasColumn('profiles', 'university_accreditation')) {
                $table->string('university_accreditation')->nullable()->after('graduation_status');
            }
        });

        // 2. user_study_plans (if not already created)
        if (! Schema::hasTable('user_study_plans')) {
            Schema::create('user_study_plans', function (Blueprint $table) {
                $table->id('user_study_plans_id');
                $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
                $table->string('desired_major')->nullable();
                $table->string('study_field_category')->nullable();
                $table->string('preferred_country')->nullable();
                $table->string('preferred_university')->nullable();
                $table->string('study_mode')->nullable();
                $table->unsignedSmallInteger('preferred_start_year')->nullable();
                $table->string('preferred_duration')->nullable();
                $table->timestamps();
            });
        }

        // 3. user_language (if not already created)
        if (! Schema::hasTable('user_language')) {
            Schema::create('user_language', function (Blueprint $table) {
                $table->id('user_language_id');
                $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
                $table->string('language_name');
                $table->string('proficiency_level')->nullable();
                $table->string('certificate_type')->nullable();
                $table->decimal('certificate_score', 5, 1)->nullable();
                $table->date('certificate_expiry')->nullable();
                $table->timestamps();
            });
        }

        // 4. user_experiences (if not already created)
        if (! Schema::hasTable('user_experiences')) {
            Schema::create('user_experiences', function (Blueprint $table) {
                $table->id('user_experiences_id');
                $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
                $table->string('experience_type')->nullable();
                $table->string('experience_name');
                $table->string('position')->nullable();
                $table->string('organization_name')->nullable();
                $table->text('description')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Safe down
    }
};
