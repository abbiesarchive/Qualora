<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table): void {
            if (! Schema::hasColumn('profiles', 'faculty')) {
                $table->text('faculty')->nullable();
            }

            if (! Schema::hasColumn('profiles', 'gender')) {
                $table->text('gender')->nullable();
            }

            if (! Schema::hasColumn('profiles', 'city_of_residence')) {
                $table->text('city_of_residence')->nullable();
            }

            if (! Schema::hasColumn('profiles', 'phone_number')) {
                $table->text('phone_number')->nullable();
            }

            if (! Schema::hasColumn('profiles', 'graduation_year')) {
                $table->integer('graduation_year')->nullable();
            }

            if (! Schema::hasColumn('profiles', 'graduation_status')) {
                $table->text('graduation_status')->nullable();
            }

            if (! Schema::hasColumn('profiles', 'university_accreditation')) {
                $table->text('university_accreditation')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table): void {
            $table->dropColumn([
                'faculty',
                'gender',
                'city_of_residence',
                'phone_number',
                'graduation_year',
                'graduation_status',
                'university_accreditation',
            ]);
        });
    }
};
