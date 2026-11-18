<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string, array<string, string>> table => [column => type] */
    private const COLUMNS = [
        'profiles' => [
            'faculty' => 'text',
            'gender' => 'text',
            'city_of_residence' => 'text',
            'phone_number' => 'text',
            'graduation_year' => 'integer',
            'graduation_status' => 'text',
            'university_accreditation' => 'text',
        ],
        'user_language' => [
            'proficiency_level' => 'text',
            'certificate_expiry' => 'date',
        ],
        'user_experiences' => [
            'position' => 'text',
            'organization_name' => 'text',
            'start_date' => 'date',
            'end_date' => 'date',
        ],
    ];

    public function up(): void
    {
        foreach (self::COLUMNS as $table => $columns) {
            $existing = array_values(array_filter(
                array_keys($columns),
                fn (string $column) => Schema::hasColumn($table, $column),
            ));

            if ($existing !== []) {
                Schema::table($table, fn (Blueprint $t) => $t->dropColumn($existing));
            }
        }
    }

    public function down(): void
    {
        foreach (self::COLUMNS as $table => $columns) {
            Schema::table($table, function (Blueprint $t) use ($table, $columns): void {
                foreach ($columns as $column => $type) {
                    if (! Schema::hasColumn($table, $column)) {
                        $t->{$type}($column)->nullable();
                    }
                }
            });
        }
    }
};
