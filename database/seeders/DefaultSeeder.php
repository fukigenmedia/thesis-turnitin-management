<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

final class DefaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            'admin',
            'dosen',
            'mahasiswa',
        ];

        foreach ($users as $user) {
            User::create([
                'name' => ucwords($user),
                'email' => $user . '@example.com',
                'password' => bcrypt('password'),
                'role' => $user,
            ]);
        }
    }
}
