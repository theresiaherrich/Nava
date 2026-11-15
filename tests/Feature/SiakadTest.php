<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Role;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SiakadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function account(string $role = 'admin', bool $active = true): User
    {
        $user = User::factory()->create(['is_active' => $active, 'password' => 'SecretDemo123!']);
        $user->roles()->attach(Role::where('name', $role)->firstOrFail());

        return $user;
    }

    private function registration(): array
    {
        return ['name' => 'Wilda Demo', 'username' => 'wilda_demo', 'email' => 'wilda@example.test', 'password' => 'SecretDemo123!', 'password_confirmation' => 'SecretDemo123!'];
    }

    private function classRecord(): Kelas
    {
        $year = TahunAjaran::create(['nama' => '2026/2027', 'aktif' => true]);
        $semester = Semester::create(['tahun_ajaran_id' => $year->id, 'nama' => 'ganjil', 'aktif' => true]);

        return Kelas::create(['semester_id' => $semester->id, 'nama' => 'X-1', 'tingkat' => 10, 'fase' => 'E']);
    }

    public function test_registration_is_pending_without_roles_and_password_is_hashed(): void
    {
        $this->postJson('/api/register', $this->registration())->assertCreated();
        $u = User::firstOrFail();
        $this->assertFalse($u->is_active);
        $this->assertCount(0, $u->roles);
        $this->assertTrue(Hash::check('SecretDemo123!', $u->password));
        $this->assertGuest();
        $this->assertStringNotContainsString('SecretDemo123!', DB::table('log_aktivitas')->first()->data_sesudah);
    }

    public function test_registration_cannot_set_role_or_active_status(): void
    {
        $this->postJson('/api/register', $this->registration() + ['roles' => ['admin'], 'is_active' => true])->assertUnprocessable()->assertJsonValidationErrors(['roles', 'is_active']);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_duplicate_and_invalid_registration_are_rejected(): void
    {
        $this->postJson('/api/register', $this->registration())->assertCreated();
        $this->postJson('/api/register', $this->registration())->assertUnprocessable()->assertJsonValidationErrors(['username', 'email']);
        $bad = $this->registration();
        $bad['password_confirmation'] = 'different';
        $this->postJson('/api/register', $bad)->assertUnprocessable()->assertJsonValidationErrors('password');
    }

    public function test_normalization_and_login_by_username(): void
    {
        $u = $this->account();
        $this->postJson('/api/login', ['login' => ' '.strtoupper($u->username).' ', 'password' => 'SecretDemo123!'])->assertOk()->assertJsonMissingPath('data.password');
        $this->assertAuthenticatedAs($u);
        $this->getJson('/api/me')->assertOk()->assertJsonPath('data.id', $u->id);
    }

    public function test_login_by_email_and_logout(): void
    {
        $u = $this->account();
        $this->postJson('/api/login', ['login' => $u->email, 'password' => 'SecretDemo123!'])->assertOk();
        $this->postJson('/api/logout')->assertOk();
        $this->assertGuest();
        $this->getJson('/api/me')->assertUnauthorized();
    }

    public function test_wrong_password_and_pending_account_are_rejected(): void
    {
        $u = $this->account('siswa', false);
        $this->postJson('/api/login', ['login' => $u->email, 'password' => 'SecretDemo123!'])->assertUnauthorized();
        $u->forceFill(['is_active' => true])->save();
        $this->postJson('/api/login', ['login' => $u->email, 'password' => 'wrong'])->assertUnauthorized();
        $this->assertGuest();
    }

    public function test_login_rate_limited_after_five_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', ['login' => 'missing', 'password' => 'wrong'])->assertUnauthorized();
        }
        $this->postJson('/api/login', ['login' => 'missing', 'password' => 'wrong'])->assertStatus(429);
    }

    public function test_guest_without_accept_header_gets_json_401(): void
    {
        $this->get('/api/me')->assertUnauthorized()->assertHeader('Content-Type', 'application/json');
    }

    public function test_admin_approves_multiple_roles_and_audits_change(): void
    {
        $admin = $this->account();
        $u = $this->account('siswa', false);
        $this->actingAs($admin)->patchJson("/api/admin/users/{$u->id}/access", ['roles' => ['guru', 'wali_kelas'], 'is_active' => true, 'alasan' => 'Identitas telah diverifikasi sekolah'])->assertOk();
        $this->assertTrue($u->fresh()->is_active);
        $this->assertCount(2, $u->fresh()->roles);
        $this->assertDatabaseHas('log_aktivitas', ['aksi' => 'update_access', 'user_id' => $admin->id, 'entitas_id' => $u->id]);
    }

    public function test_self_access_change_and_unknown_role_rejected(): void
    {
        $admin = $this->account();
        $u = $this->account('siswa');
        $this->actingAs($admin)->patchJson("/api/admin/users/{$admin->id}/access", ['roles' => ['siswa'], 'is_active' => false, 'alasan' => 'Testing'])->assertUnprocessable();
        $this->patchJson("/api/admin/users/{$u->id}/access", ['roles' => ['root'], 'is_active' => true, 'alasan' => 'Testing'])->assertUnprocessable();
    }

    public function test_students_cannot_read_other_students_or_manage_users(): void
    {
        $this->actingAs($this->account('siswa'))->getJson('/api/siswa')->assertForbidden();
        $this->getJson('/api/admin/users')->assertForbidden();
        $this->postJson('/api/mapel', ['kode' => 'IF', 'nama' => 'Informatika', 'aktif' => true])->assertForbidden();
    }

    public function test_kurikulum_can_manage_master_but_not_user_access(): void
    {
        $this->actingAs($this->account('kurikulum'))->postJson('/api/mapel', ['kode' => 'IF', 'nama' => 'Informatika', 'aktif' => true])->assertCreated();
        $this->getJson('/api/admin/users')->assertForbidden();
    }

    public function test_inactive_logged_in_account_is_logged_out(): void
    {
        $this->actingAs($this->account('admin', false))->getJson('/api/me')->assertForbidden();
        $this->assertGuest();
    }

    public function test_master_mapel_create_update_duplicate_and_missing(): void
    {
        $this->actingAs($this->account());
        $id = $this->postJson('/api/mapel', ['kode' => 'IF', 'nama' => 'Informatika', 'aktif' => true])->assertCreated()->json('data.id');
        $this->putJson("/api/mapel/$id", ['kode' => 'IF', 'nama' => 'Informatika Baru', 'aktif' => false])->assertOk()->assertJsonPath('data.aktif', false);
        $this->postJson('/api/mapel', ['kode' => 'IF', 'nama' => 'Duplikat', 'aktif' => true])->assertUnprocessable();
        $this->getJson('/api/mapel/99999')->assertNotFound();
        $this->getJson('/api/mapel?q=Baru')->assertOk()->assertJsonPath('total', 1);
    }

    public function test_year_semester_class_validation_and_immutable_parent(): void
    {
        $this->actingAs($this->account());
        $this->postJson('/api/tahun-ajaran', ['nama' => '2026/2028', 'aktif' => true])->assertUnprocessable();
        $year = $this->postJson('/api/tahun-ajaran', ['nama' => '2026/2027', 'aktif' => true])->assertCreated()->json('data.id');
        $sem = $this->postJson('/api/semester', ['tahun_ajaran_id' => $year, 'nama' => 'ganjil', 'aktif' => true])->assertCreated()->json('data.id');
        $this->postJson('/api/semester', ['tahun_ajaran_id' => $year, 'nama' => 'ganjil', 'aktif' => true])->assertUnprocessable();
        $this->postJson('/api/kelas', ['semester_id' => $sem, 'nama' => 'X-1', 'tingkat' => 10, 'fase' => 'F'])->assertUnprocessable();
        $id = $this->postJson('/api/kelas', ['semester_id' => $sem, 'nama' => 'X-1', 'tingkat' => 10, 'fase' => 'E'])->assertCreated()->json('data.id');
        $next = Semester::create(['tahun_ajaran_id' => $year, 'nama' => 'genap', 'aktif' => false]);
        $this->putJson("/api/kelas/$id", ['semester_id' => $next->id, 'nama' => 'X-1', 'tingkat' => 10, 'fase' => 'E'])->assertUnprocessable();
    }

    public function test_enrollment_derives_semester_and_rejects_duplicates(): void
    {
        $class = $this->classRecord();
        $this->actingAs($this->account());
        $student = $this->postJson('/api/siswa', ['nis' => '001', 'nisn' => '0012345678', 'nama' => 'Siswa Demo', 'agama' => null, 'aktif' => true])->assertCreated()->json('data.id');
        $body = ['siswa_id' => $student, 'kelas_id' => $class->id];
        $this->postJson('/api/siswa-kelas', $body + ['semester_id' => 999])->assertUnprocessable();
        $this->postJson('/api/siswa-kelas', $body)->assertCreated()->assertJsonPath('data.semester_id', $class->semester_id);
        $this->postJson('/api/siswa-kelas', $body)->assertUnprocessable();
        $this->getJson('/api/siswa-kelas?kelas_id='.$class->id)->assertOk()->assertJsonPath('total', 1);
    }

    public function test_inactive_student_cannot_be_enrolled(): void
    {
        $class = $this->classRecord();
        $student = Siswa::create(['nis' => 'X', 'nama' => 'Nonaktif', 'aktif' => false]);
        $this->actingAs($this->account())->postJson('/api/siswa-kelas', ['siswa_id' => $student->id, 'kelas_id' => $class->id])->assertUnprocessable();
    }

    public function test_profile_link_requires_role_and_cannot_take_another_accounts_profile(): void
    {
        $admin = $this->account();
        $student = $this->account('siswa');
        $another = $this->account('siswa');
        $profile = Siswa::create(['nis' => 'LINK', 'nama' => 'Siswa', 'aktif' => true]);
        $this->actingAs($admin);
        $body = ['jenis' => 'siswa', 'profil_id' => $profile->id, 'alasan' => 'Identitas cocok'];
        $this->putJson("/api/admin/users/{$admin->id}/profile", $body)->assertUnprocessable();
        $this->putJson("/api/admin/users/{$student->id}/profile", $body)->assertOk();
        $this->putJson("/api/admin/users/{$another->id}/profile", $body)->assertStatus(409);
        $this->postJson('/api/siswa', ['nis' => 'X', 'nama' => 'Test', 'aktif' => true, 'user_id' => $student->id])->assertUnprocessable();
    }

    public function test_teacher_and_school_master(): void
    {
        $this->actingAs($this->account());
        $id = $this->postJson('/api/guru', ['nama' => 'Guru Demo', 'nip' => null, 'status_kepegawaian' => 'Honorer', 'aktif' => true])->assertCreated()->json('data.id');
        $this->getJson("/api/guru/$id")->assertOk();
        $this->putJson('/api/sekolah', ['npsn' => '12345678', 'nama' => 'SMA Demo', 'alamat' => 'Alamat dummy', 'nama_kepala_sekolah' => 'Kepala Demo'])->assertOk();
        $this->getJson('/api/sekolah')->assertOk()->assertJsonPath('data.npsn', '12345678');
    }

    public function test_admin_command_and_role_seeder_idempotency(): void
    {
        $this->seed(RoleSeeder::class);
        $this->assertDatabaseCount('roles', 7);
        $this->artisan('siakad:create-admin')
            ->expectsQuestion('Nama admin', 'Admin Demo')
            ->expectsQuestion('Username (huruf kecil, angka, underscore)', 'admin_demo')
            ->expectsQuestion('Email', 'admin@example.test')
            ->expectsQuestion('Password (12-72 karakter, huruf besar/kecil, angka, simbol)', 'SecretDemo123!')
            ->expectsQuestion('Ulangi password', 'SecretDemo123!')->assertSuccessful();
        $this->assertTrue(User::where('username', 'admin_demo')->firstOrFail()->hasAnyRole(['admin']));
    }
}
