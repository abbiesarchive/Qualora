<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_study_plans', function (Blueprint $table): void {
            $table->dropColumn([
                'desired_major',
                'preferred_university',
                'study_mode',
                'preferred_start_year',
                'preferred_duration',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('user_study_plans', function (Blueprint $table): void {
            $table->text('desired_major')->nullable();
            $table->text('preferred_university')->nullable();
            $table->text('study_mode')->nullable();
            $table->integer('preferred_start_year')->nullable();
            $table->text('preferred_duration')->nullable();
        });
    }
};
