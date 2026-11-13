<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $map = [
            'operator' => ['users.view','users.create','users.update','users.deactivate'],
            'wakakur' => ['nilai.lock','rapor.export'],
            'wakasis' => [],
            'guru_mapel' => ['nilai.input'],
            'wali_kelas' => ['nilai.verify','rapor.export'],
            'guru_bk' => ['bk.view','bk.manage'],
            'budaya_positif' => ['disiplin.manage'],
            'siswa' => ['nilai.view-own'],
        ];
        foreach ($map as $name => $abilities) {
            foreach ($abilities as $ability) {
                Permission::findOrCreate($ability, 'web');
            }
            Role::findOrCreate($name, 'web')->syncPermissions($abilities);
        }
    }
}


