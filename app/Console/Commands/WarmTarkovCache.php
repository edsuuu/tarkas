<?php

namespace App\Console\Commands;

use App\Services\TarkovDevService;
use Illuminate\Console\Command;

class WarmTarkovCache extends Command
{
    protected $signature = 'tarkas:warm {--fresh : Limpa o cache antes, forçando busca nova na API}';

    protected $description = 'Pré-aquece o cache da API tarkov.dev com os dados de todas as telas';

    public function handle(TarkovDevService $svc): int
    {
        if ($this->option('fresh')) {
            $svc->flush();
            $this->info('Cache limpo — buscando tudo de novo na API.');
        }

        $consultas = [
            'status do jogo' => fn () => $svc->status(),
            'itens (lista inicial)' => fn () => $svc->searchItems(),
            'munição' => fn () => $svc->ammo(),
            'quests' => fn () => $svc->tasks(),
            'hideout' => fn () => $svc->hideoutStations(),
            'traders' => fn () => $svc->traders(),
            'trocas' => fn () => $svc->barters(),
            'crafts' => fn () => $svc->crafts(),
            'mapas' => fn () => $svc->maps(),
        ];

        $falhas = 0;

        foreach ($consultas as $nome => $consulta) {
            try {
                $inicio = microtime(true);
                $consulta();
                $this->line(sprintf('  ✔ %s (%.1fs)', $nome, microtime(true) - $inicio));
            } catch (\Throwable $e) {
                $falhas++;
                $this->error("  ✖ {$nome}: {$e->getMessage()}");
            }
        }

        $this->info($falhas === 0 ? 'Cache aquecido.' : "Concluído com {$falhas} falha(s).");

        return $falhas === 0 ? self::SUCCESS : self::FAILURE;
    }
}
