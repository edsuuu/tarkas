<?php

namespace App\Services;

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
    protected const ENDPOINT = 'https://api.tarkov.dev/graphql';

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

    protected function fetch(string $query, array $variables): array
    {
        $payload = ['query' => $query];

        if ($variables !== []) {
            // Um array vazio viraria "[]" no JSON e a API exige um objeto.
            $payload['variables'] = $variables;
        }

        $response = Http::timeout(45)
            ->retry(2, 500, throw: false)
            ->post(self::ENDPOINT, $payload);

        $json = $response->json();

        if (! $response->successful() || isset($json['errors'])) {
            throw new RuntimeException(
                'Erro na API tarkov.dev: '.($json['errors'][0]['message'] ?? 'HTTP '.$response->status())
            );
        }

        return $json['data'];
    }

    /** Esvazia as duas camadas de cache, forçando dados novos. */
    public function flush(): void
    {
        Cache::flush();
    }
}
