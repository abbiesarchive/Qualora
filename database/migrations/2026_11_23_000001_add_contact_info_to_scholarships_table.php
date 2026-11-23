<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // One free-text field so any kind of contact fits: email, phone, Instagram, a form link...
    public function up(): void
    {
        Schema::table('scholarships', function (Blueprint $table) {
            $table->text('contact_info')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('scholarships', function (Blueprint $table) {
            $table->dropColumn('contact_info');
        });
    }
};
