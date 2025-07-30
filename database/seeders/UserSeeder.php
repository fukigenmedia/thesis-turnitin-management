<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin user
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::ADMIN,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        // Create Lecturer users
        User::create([
            'name' => 'Dr. Ahmad Dosen',
            'email' => 'ahmad.dosen@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::LECTURE,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Prof. Siti Nurhaliza',
            'email' => 'siti.nurhaliza@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::LECTURE,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Dr. Budi Santoso',
            'email' => 'budi.santoso@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::LECTURE,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        // Create Student users
        User::create([
            'name' => 'Andi Mahasiswa',
            'email' => 'andi.mahasiswa@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::STUDENT,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Sari Dewi',
            'email' => 'sari.dewi@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::STUDENT,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Rudi Hartono',
            'email' => 'rudi.hartono@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::STUDENT,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Maya Sari',
            'email' => 'maya.sari@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::STUDENT,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Dimas Pratama',
            'email' => 'dimas.pratama@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::STUDENT,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        // Create some inactive/suspended users for testing
        User::create([
            'name' => 'Inactive User',
            'email' => 'inactive.user@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::STUDENT,
            'status' => UserStatus::INACTIVE,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Suspended User',
            'email' => 'suspended.user@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::STUDENT,
            'status' => UserStatus::SUSPENDED,
            'email_verified_at' => now(),
        ]);

        // Create unverified email user
        User::create([
            'name' => 'Unverified User',
            'email' => 'unverified.user@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::STUDENT,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => null,
        ]);
    }
}
