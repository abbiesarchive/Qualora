<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin01@gmail.com'],
            [
                'name' => 'Admin',
                'password' => 'admin01',
                'email_verified_at' => now(),
            ],
        );

        $admin->forceFill(['is_admin' => true])->save();
    }
}
