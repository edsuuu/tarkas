<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\TarkovDevClient;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class TarkovDevClientTest extends TestCase
{
    private function client(): TarkovDevClient
    {
        return app(TarkovDevClient::class);
    }

    /** A API devolve errors como lista de strings quando o servidor dela cai. */
    public function test_surfaces_error_given_as_a_plain_string(): void
    {
        Http::fake([
            'api.tarkov.dev/*' => Http::response(
                ['errors' => ['GraphQL server unavailable. Try again later.']], 422
            ),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('GraphQL server unavailable. Try again later.');

        $this->client()->query('k', 60, '{ __typename }');
    }

    /** E como lista de objetos quando o erro é da própria query. */
    public function test_surfaces_error_given_as_an_object(): void
    {
        Http::fake([
            'api.tarkov.dev/*' => Http::response(
                ['errors' => [['message' => 'Cannot query field "nope".']]], 200
            ),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot query field "nope".');

        $this->client()->query('k', 60, '{ nope }');
    }

    /** Sem errors legíveis, cai no status HTTP em vez de estourar. */
    public function test_falls_back_to_the_http_status(): void
    {
        Http::fake(['api.tarkov.dev/*' => Http::response('', 503)]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('HTTP 503');

        $this->client()->query('k', 60, '{ __typename }');
    }

    public function test_returns_data_on_success(): void
    {
        Http::fake([
            'api.tarkov.dev/*' => Http::response(['data' => ['__typename' => 'Query']]),
        ]);

        $this->assertSame(
            ['__typename' => 'Query'],
            $this->client()->query('k', 60, '{ __typename }')
        );
    }
}
