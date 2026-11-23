<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // contact_email already exists (scholarship detail tables migration).
    public function up(): void
    {
        Schema::table('scholarships', function (Blueprint $table) {
            if (! Schema::hasColumn('scholarships', 'contact_person')) {
                $table->string('contact_person')->nullable();
            }
            if (! Schema::hasColumn('scholarships', 'contact_phone')) {
                $table->string('contact_phone', 50)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('scholarships', function (Blueprint $table) {
            $table->dropColumn(['contact_person', 'contact_phone']);
        });
    }
};
