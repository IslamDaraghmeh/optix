<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create permissions
        $permissions = [
            // Patient permissions
            'view-patients',
            'create-patients',
            'edit-patients',
            'delete-patients',

            // Exam permissions
            'view-exams',
            'create-exams',
            'edit-exams',
            'delete-exams',
            'print-prescriptions',

            // Glass permissions
            'view-glasses',
            'create-glasses',
            'edit-glasses',
            'delete-glasses',
            'update-glass-status',

            // Sale permissions
            'view-sales',
            'create-sales',
            'edit-sales',
            'delete-sales',

            // Report permissions
            'view-reports',
            'export-reports',

            // User management permissions
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'manage-roles',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);

        // Assign all permissions to admin
        $adminRole->givePermissionTo(Permission::all());

        // Assign limited permissions to user
        $userRole->givePermissionTo([
            'view-patients',
            'create-patients',
            'edit-patients',
            'view-exams',
            'create-exams',
            'edit-exams',
            'print-prescriptions',
            'view-glasses',
            'create-glasses',
            'edit-glasses',
            'update-glass-status',
            'view-sales',
            'create-sales',
            'view-reports',
        ]);

        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@optical-crm.com',
            'password' => Hash::make('password'),
        ]);

        $admin->assignRole('admin');

        // Create regular user
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@optical-crm.com',
            'password' => Hash::make('password'),
        ]);

        $user->assignRole('user');
    }
}
