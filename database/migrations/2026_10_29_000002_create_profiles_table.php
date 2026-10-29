<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Every profile field is nullable: null means "unknown", not "failed".
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('campus')->nullable();
            $table->string('major')->nullable();
            $table->unsignedTinyInteger('semester')->nullable();
            $table->decimal('gpa', 3, 2)->nullable(); // 4.0 scale
            $table->unsignedTinyInteger('age')->nullable();
            $table->boolean('has_financial_need')->nullable();
            $table->text('achievements')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
