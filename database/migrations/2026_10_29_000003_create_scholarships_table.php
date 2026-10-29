<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A null requirement column means "no such rule" and is skipped by EligibilityService.
        Schema::create('scholarships', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('provider');
            $table->string('campus')->nullable(); // null = national
            $table->string('level')->nullable();
            $table->decimal('min_gpa', 3, 2)->nullable();
            $table->unsignedTinyInteger('min_semester')->nullable();
            $table->unsignedTinyInteger('max_semester')->nullable();
            $table->unsignedTinyInteger('max_age')->nullable();
            $table->boolean('requires_financial_need')->nullable();
            $table->json('majors')->nullable(); // empty/null = any major
            $table->text('other_requirements')->nullable(); // messy rules, for the AI to mention, not to score
            $table->json('required_documents')->nullable();
            $table->text('benefits')->nullable();
            $table->date('open_date')->nullable();
            $table->date('deadline')->nullable();
            $table->string('official_url');
            $table->date('last_verified');
            $table->unsignedSmallInteger('year');
            $table->boolean('is_active')->default(true);
            $table->text('ai_explanation')->nullable(); // cached
            $table->json('ai_checklist')->nullable(); // cached
            $table->timestamps();

            // One row per scholarship per year.
            $table->unique(['name', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarships');
    }
};
