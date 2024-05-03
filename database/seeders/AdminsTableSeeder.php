<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;
use App\Models\Admin;

class AdminsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $beginTime = time();
        echo " \n * Initializing default admins: ";

        Schema::disableForeignKeyConstraints();
        Admin::truncate();
        \DB::beginTransaction();

        $admins = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@test.test',
                'password' => 'secret',
                'role' => 'super.admin',
            ],
        ];

        // crete user admin
        foreach ($admins as $admin) {
            $adminUser = Admin::create([
                'name' => $admin['name'],
                'email' => $admin['email'],
                'password' => bcrypt($admin['password']),
            ]);

            if ($adminUser) {
                if (is_array($admin['role'])) {
                    foreach ($admin['role'] as $role) {
                        $adminUser->assignRole($role);
                    }
                } else {
                    $adminUser->assignRole($admin['role']);
                }
            }
            echo " \n     - " . $admin['name'] . " : " . $admin['email'] . " - Password : " . $admin['password'] . " - Role : " . $admin['role'];
        }
        echo "\n \n";

        Schema::enableForeignKeyConstraints();
        \DB::commit();

        echo (time() - $beginTime) . "s\n\r";
    }
}
