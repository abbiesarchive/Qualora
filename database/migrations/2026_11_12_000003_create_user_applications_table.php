<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scholarship_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('submitted'); // preparing, submitted, interview, accepted, rejected, withdrawn
            $table->date('applied_at');
            $table->string('registration_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // 1 user only tracks 1 record per scholarship
            $table->unique(['user_id', 'scholarship_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_applications');
    }
};
