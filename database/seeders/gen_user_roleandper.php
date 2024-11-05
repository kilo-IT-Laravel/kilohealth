<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use GuzzleHttp\Psr7\Request;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class gen_user_roleandper extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->forceDelete(); 
        Role::query()->forceDelete();
        Permission::query()->forceDelete();

        foreach (Role::allRoles() as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
        $this->command->info('Roles created successfully.');

        foreach (Permission::defaultPermissions() as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }
        $this->command->info('Permissions created successfully.');

        foreach (Role::allRoles() as $roleName) {
            $role = Role::where('name', $roleName)->first();
            $permissionNames = Role::getPermissionsForRole($roleName);
            $permissionIds = Permission::whereIn('name', $permissionNames)->pluck('id')->toArray();
            $role->permissions()->sync($permissionIds);
            $this->command->info("Permissions assigned to role '$roleName' successfully.");
        }

        $users = [
            [
                'name' => 'owner',
                'email' => 'superadmin@gmail.com',
                'password' => 'superadmin@168',
                'role' => Role::SUPER_ADMIN
            ],
            [
                'name' => 'admin',
                'email' => 'admin@gmail.com',
                'password' => 'admin@168',
                'role' => Role::ADMIN
            ],
            [
                'name' => 'user',
                'email' => 'user@gmail.com',
                'password' => 'user@168',
                'role' => Role::USER
            ],
        ];

        foreach ($users as $userData) {
            $role = Role::where('name', $userData['role'])->first();
            if (!$role) {
                $this->command->error("Role {$userData['role']} not found for user {$userData['name']}.");
                continue;
            }

            User::updateOrCreate(
                [
                    'email' => $userData['email'],
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'role_id' => $role->id,
                ]
            );

            $this->command->info("User '{$userData['name']}' created and assigned role '{$userData['role']}'.");
        }

        $this->command->info('Users created successfully.');
    }

    
}
