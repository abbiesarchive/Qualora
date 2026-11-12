<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COLUMNS = [
        'achievements',
        'english_test_type',
        'english_score',
        'organizations',
        'goals',
        'preferred_scholarship_type',
        'open_to_abroad',
        'circumstances',
    ];

    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table): void {
            $table->dropColumn(self::COLUMNS);
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table): void {
            $table->text('achievements')->nullable();
            $table->string('english_test_type')->nullable();
            $table->decimal('english_score', 5, 1)->nullable();
            $table->text('organizations')->nullable();
            $table->text('goals')->nullable();
            $table->string('preferred_scholarship_type')->nullable();
            $table->boolean('open_to_abroad')->nullable();
            $table->text('circumstances')->nullable();
        });
    }
};
