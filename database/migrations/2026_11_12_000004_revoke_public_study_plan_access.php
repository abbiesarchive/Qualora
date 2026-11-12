<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('REVOKE ALL PRIVILEGES ON TABLE public.user_study_plans FROM anon, authenticated, PUBLIC');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('GRANT ALL PRIVILEGES ON TABLE public.user_study_plans TO anon, authenticated');
    }
};
