<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminAccountSeeder extends Seeder
{
    public function run(): void
    {
        $username = trim((string) env('NAVA_ADMIN_USERNAME'));
        $password = (string) env('NAVA_ADMIN_PASSWORD');

        if ($username === '' || $password === '') {
            throw new RuntimeException(
                'Isi NAVA_ADMIN_USERNAME dan NAVA_ADMIN_PASSWORD di .env.'
            );
        }

        if (strlen($password) < 8) {
            throw new RuntimeException('Password minimal 8 karakter.');
        }

        DB::transaction(function () use ($username, $password): void {
            $user = DB::table('user')
                ->where('username', $username)
                ->first();

            if ($user && $user->role !== 'ADMIN') {
                throw new RuntimeException(
                    'Username sudah digunakan role lain. Gunakan username berbeda.'
                );
            }

            if (! $user) {
                $idUser = DB::table('user')->insertGetId([
                    'username' => $username,
                    'password' => Hash::make($password),
                    'must_change_password' => 0,
                    'role' => 'ADMIN',
                ]);
            } else {
                $idUser = (int) $user->id_user;
            }

            $adminExists = DB::table('admin')
                ->where('id_user', $idUser)
                ->exists();

            if (! $adminExists) {
                DB::table('admin')->insert([
                    'id_user' => $idUser,
                ]);
            }
        });
    }
}
