<?php
namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class UserSeeder extends Seeder {
    public function run(): void {
        abort_unless(app()->environment('local'), 403);
        $password = env('NAVA_DEMO_PASSWORD');
        if (!is_string($password) || strlen($password) < 12) {
            throw new \RuntimeException('Atur NAVA_DEMO_PASSWORD di .env minimal 12 karakter');
        }
        $accounts = [
            ['Operator','operator','admin','operator'],
            ['Wakakur','wakakur','admin','wakakur'],
            ['Wakasis','wakasis','admin','wakasis'],
            ['Guru BK','guru_bk','guru','bk'],
            ['Wali Kelas','wali_kelas','guru','wali_kelas'],
            ['Guru Mapel','guru_mapel','guru','mapel'],
            ['Budaya Positif','budaya_positif','tendik','budaya_positif'],
            ['Siswa Demo','siswa_demo','siswa',null],
        ];
        foreach ($accounts as [$name,$username,$role,$subRole]) {
            User::updateOrCreate(['username'=>$username], [
                'name'=>$name, 'email'=>null, 'password'=>Hash::make($password),
                'role'=>$role, 'sub_role'=>$subRole, 'is_active'=>true
            ]);
        }
    }
}


