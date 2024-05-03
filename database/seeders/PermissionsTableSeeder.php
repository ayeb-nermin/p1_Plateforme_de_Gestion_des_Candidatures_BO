<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use App\Models\PermissionTranslation;
use App\Models\RoleTranslation;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $beginTime = time();

        Schema::disableForeignKeyConstraints();

        Cache::forget('spatie.permission.cache');

        PermissionTranslation::truncate();
        Permission::truncate();

        // TODO: create roles table seeder and put all roles there
        RoleTranslation::truncate();
        Role::truncate();

        \DB::beginTransaction();
        $languages = languages();

        // create super admin role
        Role::create([
            'name' => 'super.admin',
            'guard_name' => 'backpack',
        ])->users()->sync([1]);

        // create admin role
        $admin = Role::create([
            'name' => 'admin',
            'guard_name' => 'backpack',
        ]);

        if ($admin) {
            if ($languages) {
                foreach ($languages as $abbr => $language) {
                    RoleTranslation::create([
                        'role_id' => $admin->id,
                        'title' => ucfirst(str_replace('_', ' ', $admin->name)),
                        'locale' => $abbr,
                    ]);
                }
            }
        }

        $roles = [
            'user',
        ];
        foreach ($roles as $item) {
            $role = Role::create([
                'name' => $item,
                'guard_name' => 'web',
            ]);

            if ($role) {
                if ($languages) {
                    foreach ($languages as $abbr => $language) {
                        RoleTranslation::create([
                            'role_id' => $role->id,
                            'title' => ucfirst(str_replace('_', ' ', $role->name)),
                            'locale' => $abbr,
                        ]);
                    }
                }
            }
        }

        $elFinderPermission = Permission::where('name', 'elfinder')->first();

        if (! $elFinderPermission) {
            $permissionDB = Permission::create([
                'name' => 'elfinder',
                'guard_name' => 'backpack',
            ]);
            $permissionDB->roles()->sync([$admin->id]);

            if ($permissionDB && $languages) {
                foreach ($languages as $abbr => $language) {
                    PermissionTranslation::create([
                        'permission_id' => $permissionDB->id,
                        'title' => ucfirst(str_replace('_', ' ', $permissionDB->name)),
                        'locale' => $abbr,
                    ]);
                }
            }
        }

        foreach (glob('app/Http/Controllers/Admin/*CrudController*.php') as $controller) {
            $className = basename($controller, 'CrudController.php');

            $class = 'App\\Http\\Controllers\\Admin\\' . basename($controller, '.php');
            if (class_exists($class)) {
                $class = new $class;
                if (method_exists($class, 'getCrudPermissionsAndRole')) {
                    $permissions = $class::getCrudPermissionsAndRole()['permissions'];

                    foreach ($permissions as $key => $value) {
                        $permissionDB = Permission::create([
                            'name' => $value,
                            'guard_name' => 'backpack',
                        ]);
                        $permissionDB->roles()->sync([$admin->id]);

                        if ($permissionDB && $languages) {
                            foreach ($languages as $abbr => $language) {
                                PermissionTranslation::create([
                                    'permission_id' => $permissionDB->id,
                                    'title' => ucfirst(str_replace('_', ' ', $permissionDB->name)),
                                    'locale' => $abbr,
                                ]);
                            }
                        }
                    }
                }
            }
        }

        Schema::enableForeignKeyConstraints();
        \DB::commit();

        echo (time() - $beginTime) . "s\n\r";
    }
}
