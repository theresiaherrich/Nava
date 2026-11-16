<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'siakad:create-admin';

    protected $description = 'Membuat akun admin baru secara interaktif, tanpa password bawaan';

    public function handle(): int
    {
        $data = [
            'name' => $this->ask('Nama admin'),
            'username' => strtolower(trim((string) $this->ask('Username (huruf kecil, angka, underscore)'))),
            'email' => strtolower(trim((string) $this->ask('Email'))),
            'password' => $this->secret('Password (12-72 karakter, huruf besar/kecil, angka, simbol)'),
            'password_confirmation' => $this->secret('Ulangi password'),
        ];
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-z0-9_]+$/', 'unique:users,username'],
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'string', 'max:72', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()->symbols()],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }
        DB::transaction(function () use ($data) {
            $user = User::create(collect($data)->except('password_confirmation')->all());
            $user->forceFill(['is_active' => true])->save();
            $role = Role::firstOrCreate(['name' => 'admin']);
            $user->roles()->sync([$role->id]);
            AuditService::record('create_admin', 'users', $user->id, null, ['roles' => ['admin']], actorId: $user->id);
        });
        $this->info('Admin dibuat. Login menggunakan username/email dan password tadi.');

        return self::SUCCESS;
    }
}
