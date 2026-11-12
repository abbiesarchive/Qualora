<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_experiences', function (Blueprint $table): void {
            $table->uuid('user_experiences_id')->primary();
            $table->foreignId('profile_id')
                ->constrained('profiles', 'id')
                ->cascadeOnDelete();
            $table->text('experience_type')->nullable();
            $table->text('experience_name')->nullable();
            $table->text('position')->nullable();
            $table->text('organization_name')->nullable();
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE public.user_experiences ALTER COLUMN user_experiences_id SET DEFAULT gen_random_uuid()');
            DB::statement('REVOKE ALL PRIVILEGES ON TABLE public.user_experiences FROM anon, authenticated, PUBLIC');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_experiences');
    }
};
