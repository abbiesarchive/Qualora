<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Supabase exposes tables through its own API. RLS with no policies blocks the
     * anon key; Laravel's DB role is unaffected. Postgres only.
     */
    private array $tables = [
        'users',
        'password_reset_tokens',
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'migrations',
        'profiles',
        'scholarships',
        'saved_scholarships',
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->tables as $table) {
            DB::statement("ALTER TABLE \"{$table}\" ENABLE ROW LEVEL SECURITY");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->tables as $table) {
            DB::statement("ALTER TABLE \"{$table}\" DISABLE ROW LEVEL SECURITY");
        }
    }
};
