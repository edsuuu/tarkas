<?php

namespace Tests\Feature;

use App\Livewire\Tasks\Index;
use App\Models\CompletedTask;
use App\Models\User;
use App\Services\TarkovDevService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PDO;
use Tests\TestCase;

class TaskProgressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('O driver pdo_sqlite não está instalado neste ambiente.');
        }

        parent::setUp();

        $this->mock(TarkovDevService::class)
            ->shouldReceive('tasks')
            ->andReturn([]);
    }

    public function test_authenticated_user_can_toggle_task_completion(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Index::class)
            ->call('toggleCompleted', 'task-abc');

        $this->assertDatabaseHas('completed_tasks', [
            'user_id' => $user->id,
            'task_id' => 'task-abc',
        ]);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->call('toggleCompleted', 'task-abc');

        $this->assertDatabaseMissing('completed_tasks', [
            'user_id' => $user->id,
            'task_id' => 'task-abc',
        ]);
    }

    public function test_guests_cannot_save_task_completion(): void
    {
        Livewire::test(Index::class)
            ->call('toggleCompleted', 'task-abc');

        $this->assertSame(0, CompletedTask::count());
    }
}
