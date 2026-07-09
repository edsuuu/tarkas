<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Renova o cache da API a cada hora (requer o scheduler rodando via cron:
// * * * * * cd /var/www/projects/tarkas && php artisan schedule:run >> /dev/null 2>&1).
// Sem cron, o cache continua funcionando: o primeiro acesso após expirar renova.
Schedule::command('tarkas:warm')->hourly();
