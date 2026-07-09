<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $logViewerUser = [
            'name' => 'Tecnologia',
            'email' => 'tecnologia@tarkas.local',
            'password' => '123',
        ];

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission = Permission::findOrCreate('log-viewer.view', 'web');
        $role = Role::findOrCreate('Tecnologia', 'web');
        $role->syncPermissions([$permission]);

        Role::findOrCreate('PMC', 'web');

        $user = User::updateOrCreate([
            'email' => $logViewerUser['email'],
        ], [
            'name' => $logViewerUser['name'],
            'password' => Hash::make($logViewerUser['password']),
            'email_verified_at' => now(),
        ]);

        $user->syncRoles([$role]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
