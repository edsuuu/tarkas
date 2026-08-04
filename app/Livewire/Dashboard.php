<?php

namespace App\Livewire;

use App\Services\TarkovDevService;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $error = null;
        $status = null;
        $traders = [];

        try {
            $svc = app(TarkovDevService::class);
            $status = $svc->status();
            $traders = $svc->traders();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('livewire.dashboard', compact('status', 'traders', 'error'));
    }
}
