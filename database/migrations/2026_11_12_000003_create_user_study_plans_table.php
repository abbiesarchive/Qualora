<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_study_plans', function (Blueprint $table): void {
            $table->uuid('user_study_plans_id')->primary();
            $table->foreignId('profile_id')
                ->unique()
                ->constrained('profiles', 'id')
                ->cascadeOnDelete();
            $table->text('desired_major')->nullable();
            $table->text('study_field_category')->nullable();
            $table->text('preferred_country')->nullable();
            $table->text('preferred_university')->nullable();
            $table->text('study_mode')->nullable();
            $table->integer('preferred_start_year')->nullable();
            $table->text('preferred_duration')->nullable();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE user_study_plans ALTER COLUMN user_study_plans_id SET DEFAULT gen_random_uuid()');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_study_plans');
    }
};
