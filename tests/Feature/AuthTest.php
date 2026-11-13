<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class AuthTest extends TestCase {
    use RefreshDatabase;
    public function test_guest_redirected_to_login(): void {
        $this->get('/admin/dashboard')->assertRedirect('/login');
    }
    public function test_siswa_forbidden_from_admin(): void {
        $siswa = User::factory()->create([
            'username'=>'siswa_pengujian','role'=>'siswa','is_active'=>true,
        ]);
        $this->actingAs($siswa)->get('/admin/dashboard')->assertForbidden();
    }
}
