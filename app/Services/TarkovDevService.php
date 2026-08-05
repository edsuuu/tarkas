<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Consultas do tarkov.dev usadas pelas telas (API pública, sem autenticação).
 *
 * Cada método corresponde a uma tela e lê sua query em resources/graphql/.
 * O transporte e o cache em duas camadas ficam no TarkovDevClient.
 */
class TarkovDevService
{
    /** Textos vêm traduzidos da própria API. */
    public const string LANG = 'pt';

    /** Nenhuma consulta bate na API mais de uma vez por hora. */
    public const int TTL_HORA = 3600;

    /** Quests, hideout e mapas mudam só em patch — 6 horas. */
    public const int TTL_ESTATICO = 21600;

    public function __construct(protected TarkovDevClient $client) {}

    /**
     * @throws Throwable
     */
    public function status(): array
    {
        return $this->query('status', self::TTL_HORA, lang: false)['status'];
    }

    /**
     * @throws Throwable
     */
    public function searchItems(string $name = '', int $limit = 48, int $offset = 0): array
    {
        $cacheKey = 'items.'.md5($name).".{$limit}.{$offset}";
        $variables = ['limit' => $limit, 'offset' => $offset];

        if ($name === '') {
            return $this->query('items-list', self::TTL_HORA, $variables, $cacheKey)['items'];
        }

        // Buscas digitadas não guardam camada stale para não inflar o cache.
        return $this->query('items-search', self::TTL_HORA, [...$variables, 'name' => $name], $cacheKey, keepStale: false)['items'];
    }

    public function item(string $id): array
    {
        return $this->query('item', self::TTL_HORA, ['id' => $id], "item.{$id}")['item'] ?? [];
    }

    /**
     * @throws Throwable
     */
    public function ammo(): array
    {
        try {
            return $this->query('ammo', self::TTL_HORA)['ammo'];
        } catch (Throwable $exception) {
            return $this->ammoFromTarkovData($exception);
        }
    }

    public function tasks(): array
    {
        return $this->query('tasks', self::TTL_ESTATICO)['tasks'];
    }

    public function hideoutStations(): array
    {
        return $this->query('hideout', self::TTL_ESTATICO)['hideoutStations'];
    }

    public function traders(): array
    {
        return $this->query('traders', self::TTL_HORA)['traders'];
    }

    public function barters(): array
    {
        return $this->query('barters', self::TTL_HORA)['barters'];
    }

    public function crafts(): array
    {
        return $this->query('crafts', self::TTL_HORA)['crafts'];
    }

    public function maps(): array
    {
        return $this->query('maps', self::TTL_ESTATICO)['maps'];
    }

    /** Esvazia o cache para forçar dados novos (usado por tarkas:warm --fresh). */
    public function flush(): void
    {
        $this->client->flush();
    }

    /**
     * Balística vinda do TarkovTracker/tarkovdata, usada só quando o tarkov.dev
     * cai. A fonte não tem preço nem ícone (são exclusivos da rede de scanners
     * deles), então esses campos vêm nulos — a tela já renderiza "—" no lugar.
     * Se o fallback também falhar, relança o erro original do tarkov.dev.
     *
     * @throws Throwable
     */
    private function ammoFromTarkovData(Throwable $original): array
    {
        try {
            $rows = Cache::remember('tarkov.fallback.ammo', self::TTL_ESTATICO, function (): array {
                $response = Http::timeout(20)
                    ->get(config('services.tarkov.data_url').'/ammunition.json')
                    ->throw();

                return $response->json();
            });
        } catch (Throwable $exception) {
            Log::channel('daily')->error('[ERRO] fallback tarkovdata falhou para munição', [
                'exception' => $exception,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            throw $original;
        }

        Log::channel('daily')->warning('[WARN] tarkov.dev indisponivel, munição servida pelo tarkovdata', [
            'message' => $original->getMessage(),
        ]);

        $ammo = [];

        foreach ($rows as $row) {
            $ballistics = $row['ballistics'] ?? [];

            $ammo[] = [
                'item' => [
                    'id' => $row['id'] ?? null,
                    'name' => $row['name'] ?? null,
                    'shortName' => $row['shortName'] ?? null,
                    'iconLink' => null,
                    'avg24hPrice' => null,
                    'lastLowPrice' => null,
                ],
                'caliber' => $row['caliber'] ?? null,
                'penetrationPower' => $ballistics['penetrationPower'] ?? null,
                'damage' => $ballistics['damage'] ?? null,
                'armorDamage' => $ballistics['armorDamage'] ?? null,
                'fragmentationChance' => $ballistics['fragmentationChance'] ?? null,
                'initialSpeed' => $ballistics['initialSpeed'] ?? null,
                'tracer' => $row['tracer'] ?? false,
                'projectileCount' => $row['projectileCount'] ?? 1,
                'accuracyModifier' => $ballistics['accuracy'] ?? null,
                'recoilModifier' => $ballistics['recoil'] ?? null,
            ];
        }

        return $ammo;
    }

    /**
     * Executa a query de resources/graphql/{$file}.graphql com cache.
     *
     * @param  string  $file  nome do arquivo .graphql (sem extensão)
     * @param  int  $ttl  validade do cache principal, em segundos
     * @param  array  $variables  variáveis além de lang (limit, name, id…)
     * @param  string|null  $cacheKey  chave de cache; por padrão, o nome do arquivo
     * @param  bool  $lang  inclui a variável lang (só status não usa)
     * @param  bool  $keepStale  guarda cópia stale para fallback de API fora
     *
     * @throws Throwable
     */
    protected function query(
        string $file,
        int $ttl,
        array $variables = [],
        ?string $cacheKey = null,
        bool $lang = true,
        bool $keepStale = true,
    ): array {
        if ($lang) {
            $variables = ['lang' => self::LANG, ...$variables];
        }

        return $this->client->query(
            $cacheKey ?? $file,
            $ttl,
            file_get_contents(resource_path("graphql/{$file}.graphql")),
            $variables,
            $keepStale,
        );
    }
}
