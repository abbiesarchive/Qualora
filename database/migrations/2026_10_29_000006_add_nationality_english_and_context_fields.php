<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            // Eligibility fields (used by EligibilityService).
            $table->string('nationality')->nullable()->default('Indonesian')->after('age');
            $table->string('english_test_type')->nullable()->after('has_financial_need');
            $table->decimal('english_score', 5, 1)->nullable()->after('english_test_type');

            // Context and preferences: ordering and AI explanations only, never pass/fail.
            $table->text('organizations')->nullable()->after('achievements');
            $table->text('goals')->nullable()->after('organizations');
            $table->string('preferred_scholarship_type')->nullable()->after('goals'); // full / partial
            $table->boolean('open_to_abroad')->nullable()->after('preferred_scholarship_type');
            $table->text('circumstances')->nullable()->after('open_to_abroad'); // sensitive, optional
        });

        Schema::table('scholarships', function (Blueprint $table) {
            $table->json('allowed_nationalities')->nullable()->after('majors'); // empty/null = any
            $table->string('english_test_type')->nullable()->after('allowed_nationalities');
            $table->decimal('min_english_score', 5, 1)->nullable()->after('english_test_type');
            $table->string('scholarship_type')->nullable()->after('min_english_score'); // full / partial
            $table->boolean('is_abroad')->default(false)->after('scholarship_type');
        });
    }

    public function down(): void
    {
        Schema::table('scholarships', function (Blueprint $table) {
            $table->dropColumn([
                'allowed_nationalities', 'english_test_type', 'min_english_score',
                'scholarship_type', 'is_abroad',
            ]);
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn([
                'nationality', 'english_test_type', 'english_score', 'organizations',
                'goals', 'preferred_scholarship_type', 'open_to_abroad', 'circumstances',
            ]);
        });
    }
};
