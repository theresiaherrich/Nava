<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName().Str::lower(Str::random(3)),
            'nama' => fake()->name(),
            'email' => null,
            'password' => 'Rahasia-Uji-123',
            'status_akun' => 'aktif',
            'must_change_password' => false,
        ];
    }

    public function wajibGantiSandi(): static
    {
        return $this->state(['must_change_password' => true]);
    }
}
