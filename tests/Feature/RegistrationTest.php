<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PDO;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('O driver pdo_sqlite não está instalado neste ambiente.');
        }

        parent::setUp();
    }

    public function test_registration_page_renders(): void
    {
        $this->get('/cadastro')->assertOk();
    }

    public function test_new_users_are_registered_with_pmc_role(): void
    {
        $response = $this->post('/cadastro', [
            'name' => 'Novo PMC',
            'email' => 'novo@example.com',
            'password' => 'senha12345',
            'password_confirmation' => 'senha12345',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $user = User::where('email', 'novo@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('PMC'));
    }

    public function test_registration_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'usado@example.com']);

        $this->post('/cadastro', [
            'name' => 'Outro',
            'email' => 'usado@example.com',
            'password' => 'senha12345',
            'password_confirmation' => 'senha12345',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_users_cannot_view_registration(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/cadastro')
            ->assertRedirect();
    }
}
