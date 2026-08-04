<?php

namespace App\Livewire\Ammo;

use App\Services\TarkovDevService;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url]
    public string $caliber = '';

    #[Url]
    public string $tier = '';

    #[Url(as: 'q')]
    public string $search = '';

    public string $sortBy = 'penetrationPower';

    public string $sortDir = 'desc';

    /**
     * Tier = classe de armadura que a munição perfura com folga,
     * seguindo os cortes usuais da comunidade por penetração.
     */
    public static function tierOf(?int $pen): int
    {
        return match (true) {
            $pen >= 50 => 6,
            $pen >= 40 => 5,
            $pen >= 30 => 4,
            $pen >= 20 => 3,
            $pen >= 10 => 2,
            default => 1,
        };
    }

    public function sort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDir = $this->sortDir === 'desc' ? 'asc' : 'desc';
        } else {
            $this->sortBy = $field;
            $this->sortDir = 'desc';
        }
    }

    public function render()
    {
        $error = null;
        $rows = collect();
        $calibers = collect();

        try {
            $ammo = collect(app(TarkovDevService::class)->ammo());

            $calibers = $ammo->pluck('caliber')->filter()->unique()->sort()->values();

            $rows = $ammo
                ->when($this->caliber !== '', fn ($c) => $c->where('caliber', $this->caliber))
                ->when($this->tier !== '', fn ($c) => $c->filter(
                    fn ($a) => self::tierOf($a['penetrationPower'] ?? 0) === (int) $this->tier
                ))
                ->when($this->search !== '', fn ($c) => $c->filter(
                    fn ($a) => str_contains(mb_strtolower($a['item']['name'] ?? ''), mb_strtolower($this->search))
                ))
                ->sortBy($this->sortBy, SORT_REGULAR, $this->sortDir === 'desc')
                ->values();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('livewire.ammo.index', compact('rows', 'calibers', 'error'));
    }
}
