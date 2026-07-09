<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PDO;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LogViewerAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('O driver pdo_sqlite não está instalado neste ambiente.');
        }

        parent::setUp();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/log-viewer')
            ->assertRedirect('/login');
    }

    public function test_authenticated_users_without_permission_are_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/log-viewer')
            ->assertForbidden();
    }

    public function test_tecnologia_role_can_open_log_viewer(): void
    {
        $permission = Permission::findOrCreate('log-viewer.view', 'web');
        $role = Role::findOrCreate('Tecnologia', 'web');
        $role->givePermissionTo($permission);

        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user)
            ->get('/log-viewer')
            ->assertOk();
    }
}
