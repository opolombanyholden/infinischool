<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@infini-eschool.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'), // Mot de passe par défaut
                'role' => 'admin',
                'status' => 'active',
                'is_approved' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin user created/updated successfully.');
        $this->command->info('Email: admin@infini-eschool.com');
        $this->command->info('Password: password');
    }
}
