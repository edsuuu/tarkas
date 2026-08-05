<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Transporte e cache das consultas ao tarkov.dev.
 *
 * Cache em duas camadas:
 *  - principal (`tarkov.{chave}`): expira no TTL informado;
 *  - stale (`tarkov.stale.{chave}`): última resposta boa, sem validade —
 *    servida quando a API falha com o cache principal já expirado.
 */
class TarkovDevClient
{
    public function query(string $cacheKey, int $ttl, string $query, array $variables = [], bool $keepStale = true): array
    {
        $cached = Cache::get("tarkov.{$cacheKey}");

        if ($cached !== null) {
            return $cached;
        }

        try {
            $data = $this->fetch($query, $variables);
        } catch (\Throwable $e) {
            $stale = Cache::get("tarkov.stale.{$cacheKey}");

            if ($stale !== null) {
                return $stale;
            }

            throw $e;
        }

        Cache::put("tarkov.{$cacheKey}", $data, $ttl);

        if ($keepStale) {
            Cache::forever("tarkov.stale.{$cacheKey}", $data);
        }

        return $data;
    }

    /**
     * @throws ConnectionException
     */
    protected function fetch(string $query, array $variables): array
    {
        $payload = ['query' => $query];

        if ($variables !== []) {
            // Um array vazio viraria "[]" no JSON e a API exige um objeto.
            $payload['variables'] = $variables;
        }

        $response = Http::timeout(45)
            ->retry(2, 500, throw: false)
            ->post((string) config('services.tarkov.api_url'), $payload);

        $json = $response->json();

        if (! $response->successful() || isset($json['errors'])) {
            // A API devolve errors ora como lista de strings ("GraphQL server
            // unavailable"), ora como objetos {message}. Ler só [0]['message']
            // engolia o primeiro caso e virava um "HTTP 422" sem explicação.
            $first = $json['errors'][0] ?? null;

            throw new RuntimeException('Erro na API tarkov.dev: '.match (true) {
                is_string($first) => $first,
                is_array($first) => $first['message'] ?? 'HTTP '.$response->status(),
                default => 'HTTP '.$response->status(),
            });
        }

        return $json['data'];
    }

    /** Esvazia as duas camadas de cache, forçando dados novos. */
    public function flush(): void
    {
        Cache::flush();
    }
}
