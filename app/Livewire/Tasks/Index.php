<?php

namespace App\Livewire\Tasks;

use App\Models\CompletedTask;
use App\Services\TarkovDevService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $trader = '';

    #[Url]
    public string $map = '';

    #[Url]
    public bool $kappa = false;

    #[Url]
    public bool $lightkeeper = false;

    #[Url]
    public bool $hideCompleted = false;

    public int $shown = 60;

    public function updated(): void
    {
        $this->shown = 60;
    }

    public function loadMore(): void
    {
        $this->shown += 60;
    }

    public function toggleCompleted(string $taskId): void
    {
        if (! Auth::check()) {
            return;
        }

        $existing = CompletedTask::where('user_id', Auth::id())
            ->where('task_id', $taskId)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            CompletedTask::create([
                'user_id' => Auth::id(),
                'task_id' => $taskId,
            ]);
        }
    }

    public function render()
    {
        $error = null;
        $tasks = collect();
        $traders = collect();
        $maps = collect();
        $total = 0;
        $doneCount = 0;

        $completedIds = Auth::check()
            ? CompletedTask::where('user_id', Auth::id())->pluck('task_id')->flip()
            : collect();

        try {
            $all = collect(app(TarkovDevService::class)->tasks());

            $doneCount = $all->filter(fn ($task) => isset($completedIds[$task['id']]))->count();

            $traders = $all->pluck('trader.name')->filter()->unique()->sort()->values();
            $maps = $all
                ->flatMap(fn ($task) => collect([$task['map']['name'] ?? null])
                    ->concat(collect($task['objectives'] ?? [])->flatMap(fn ($objective) => collect($objective['maps'] ?? [])->pluck('name'))))
                ->filter()
                ->unique()
                ->sort()
                ->values();
            $needle = mb_strtolower(trim($this->search));

            $filtered = $all
                ->when($this->trader !== '', fn ($c) => $c->where('trader.name', $this->trader))
                ->when($this->map !== '', fn ($c) => $c->filter(fn ($task) => $this->taskHasMap($task, $this->map)))
                ->when($this->kappa, fn ($c) => $c->where('kappaRequired', true))
                ->when($this->lightkeeper, fn ($c) => $c->where('lightkeeperRequired', true))
                ->when($this->hideCompleted, fn ($c) => $c->reject(fn ($task) => isset($completedIds[$task['id']])))
                ->when($needle !== '', fn ($c) => $c->filter(fn ($task) => $this->taskMatches($task, $needle)))
                ->values();

            $total = $filtered->count();
            $tasks = $filtered->take($this->shown);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('livewire.tasks.index', compact('tasks', 'traders', 'maps', 'total', 'error', 'completedIds', 'doneCount'));
    }

    private function taskHasMap(array $task, string $map): bool
    {
        if (($task['map']['name'] ?? null) === $map) {
            return true;
        }

        return collect($task['objectives'] ?? [])->contains(
            fn ($objective) => collect($objective['maps'] ?? [])->contains(fn ($objectiveMap) => ($objectiveMap['name'] ?? null) === $map)
        );
    }

    private function taskMatches(array $task, string $needle): bool
    {
        $haystack = collect([
            $task['name'] ?? '',
            $task['trader']['name'] ?? '',
            $task['map']['name'] ?? '',
        ])
            ->concat(collect($task['taskRequirements'] ?? [])->pluck('task.name'))
            ->concat(collect($task['objectives'] ?? [])->pluck('description'))
            ->concat(collect($task['finishRewards']['items'] ?? [])->pluck('item.name'))
            ->filter()
            ->implode(' ');

        return str_contains(mb_strtolower($haystack), $needle);
    }
}
