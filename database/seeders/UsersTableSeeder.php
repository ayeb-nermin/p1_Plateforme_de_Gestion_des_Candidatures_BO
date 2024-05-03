<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $beginTime = time();
        echo " \n * Initializing default users: ";

        Schema::disableForeignKeyConstraints();
        User::truncate();
        \DB::beginTransaction();

        $users = [
            [
                'first_name' => 'User',
                'email' => 'user@test.test',
                'password' => 'secret',
                'role' => 'user',
            ],
        ];

        // create users
        foreach ($users as $user) {
            $createdUser = User::create([
                'first_name' => $user['first_name'],
                'email' => $user['email'],
                'password' => bcrypt($user['password']),
            ]);

            if ($createdUser) {
                $role = Role::where('name', $user['role'])->first();
                if ($role) {
                    $createdUser->assignRole($role->name);
                }
            }
            echo " \n     - " . $user['first_name'] . " : " . $user['email'] . " - Role : " . $user['role'];
        }
        echo "\n \n";

        Schema::enableForeignKeyConstraints();
        \DB::commit();

        echo (time() - $beginTime) . "s\n\r";
    }
}
