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

        // user_study_plans, user_language and user_experiences are created by their
        // own dedicated migrations (000003 / 000005 / 000006).
    }

    public function down(): void
    {
        // Safe down
    }
};
