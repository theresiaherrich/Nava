<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'username' => 'inactive', 
            'is_active' => false,
            'password' => 'CorrectPassword!2026',
        ]);

        $this->post('/login', [
            'username' => 'inactive', 
            'password' => 'CorrectPassword!2026',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }
}
