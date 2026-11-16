<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

/** Satu-satunya cara membuat akun SUPERADMIN. Tidak ada sandi bawaan. */
class CreateSuperadmin extends Command
{
    protected $signature = 'siakad:create-superadmin
        {--username= : Username (opsional, ditanya bila kosong)}
        {--nama= : Nama lengkap}
        {--email= : Email (opsional)}
        {--force : Tetap buat walau sudah ada SUPERADMIN}';

    protected $description = 'Buat akun Super Admin SIAKAD secara interaktif';

    public function handle(AuditService $audit): int
    {
        $ada = User::whereHas('roles', fn ($q) => $q->where('kode', 'SUPERADMIN'))->exists();
        if ($ada && ! $this->option('force')) {
            $this->error('SUPERADMIN sudah ada. Gunakan --force bila memang perlu menambah SUPERADMIN lain.');

            return self::FAILURE;
        }

        $role = Role::where('kode', 'SUPERADMIN')->first();
        if (! $role) {
            $this->error('Peran belum tersedia. Jalankan: php artisan db:seed --class=RoleSeeder');

            return self::FAILURE;
        }

        $data = [
            'username' => Str::lower(trim((string) ($this->option('username') ?: $this->ask('Username')))),
            'nama' => (string) ($this->option('nama') ?: $this->ask('Nama lengkap')),
            'email' => $this->option('email') ?: ($this->ask('Email (kosongkan bila tidak ada)') ?: null),
            'password' => (string) $this->secret('Kata sandi (min. '.config('siakad.password.min').' karakter, huruf besar, kecil, angka, simbol)'),
        ];
        $data['password_confirmation'] = (string) $this->secret('Ulangi kata sandi');

        $v = Validator::make($data, [
            'username' => ['required', 'min:3', 'max:50', 'alpha_dash:ascii', 'unique:user,username'],
            'nama' => ['required', 'max:120'],
            'email' => ['nullable', 'email', 'max:100', 'unique:user,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($v->fails()) {
            foreach ($v->errors()->all() as $pesan) {
                $this->error($pesan);
            }

            return self::FAILURE;
        }

        $user = DB::transaction(function () use ($data, $role, $audit) {
            $user = User::create([
                'username' => $data['username'],
                'nama' => $data['nama'],
                'email' => $data['email'],
                'password' => $data['password'],
                'status_akun' => 'aktif',
                'must_change_password' => false,
            ]);
            $user->roles()->attach($role->id_role, ['created_at' => now()]);
            $audit->catat('buat_superadmin', 'user', $user->id_user, null, ['username' => $user->username, 'via' => 'artisan'], null, $user->id_user);

            return $user;
        });

        $this->info("SUPERADMIN '{$user->username}' berhasil dibuat.");

        return self::SUCCESS;
    }
}
