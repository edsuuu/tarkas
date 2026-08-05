<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\TarkovDevService;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class AmmoFallbackTest extends TestCase
{
    private const array TARKOVDATA_ROW = [
        'id' => '573720e02459776143012541',
        'name' => '9x18mm PM RG028 gzh',
        'shortName' => 'RG028',
        'caliber' => 'Caliber9x18PM',
        'tracer' => false,
        'projectileCount' => 1,
        'ballistics' => [
            'damage' => 65,
            'armorDamage' => 26,
            'fragmentationChance' => 0.02,
            'penetrationPower' => 13,
            'accuracy' => 0,
            'recoil' => 0,
            'initialSpeed' => 330,
        ],
    ];

    /** Com o tarkov.dev fora, a munição vem do tarkovdata no mesmo formato. */
    public function test_falls_back_to_tarkovdata_when_the_api_is_down(): void
    {
        Http::fake([
            'api.tarkov.dev/*' => Http::response(['errors' => ['GraphQL server unavailable.']], 422),
            'raw.githubusercontent.com/*' => Http::response([self::TARKOVDATA_ROW]),
        ]);

        $ammo = app(TarkovDevService::class)->ammo();

        $this->assertCount(1, $ammo);
        $this->assertSame('9x18mm PM RG028 gzh', $ammo[0]['item']['name']);
        $this->assertSame('Caliber9x18PM', $ammo[0]['caliber']);
        $this->assertSame(13, $ammo[0]['penetrationPower']);
        $this->assertSame(26, $ammo[0]['armorDamage']);
        $this->assertSame(330, $ammo[0]['initialSpeed']);
    }

    /** Preço não tem substituto e vem nulo, mas presente — a tela mostra "—". */
    public function test_price_is_null_but_present(): void
    {
        Http::fake([
            'api.tarkov.dev/*' => Http::response(['errors' => ['down']], 422),
            'raw.githubusercontent.com/*' => Http::response([self::TARKOVDATA_ROW]),
        ]);

        $item = app(TarkovDevService::class)->ammo()[0]['item'];

        $this->assertArrayHasKey('avg24hPrice', $item);
        $this->assertNull($item['avg24hPrice']);
    }

    /** O ícone é remontado pelo id: o CDN de assets sobrevive à queda da API. */
    public function test_icon_is_rebuilt_from_the_item_id(): void
    {
        Http::fake([
            'api.tarkov.dev/*' => Http::response(['errors' => ['down']], 422),
            'raw.githubusercontent.com/*' => Http::response([self::TARKOVDATA_ROW]),
        ]);

        $item = app(TarkovDevService::class)->ammo()[0]['item'];

        $this->assertSame(
            'https://assets.tarkov.dev/573720e02459776143012541-icon.webp',
            $item['iconLink']
        );
    }

    /** Se o fallback também cair, o erro do tarkov.dev é que chega na tela. */
    public function test_rethrows_the_original_error_when_the_fallback_also_fails(): void
    {
        Http::fake([
            'api.tarkov.dev/*' => Http::response(['errors' => ['GraphQL server unavailable.']], 422),
            'raw.githubusercontent.com/*' => Http::response('', 500),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('GraphQL server unavailable.');

        app(TarkovDevService::class)->ammo();
    }

    /** Com a API de pé, o tarkovdata nem é tocado. */
    public function test_does_not_touch_tarkovdata_when_the_api_works(): void
    {
        Http::fake([
            'api.tarkov.dev/*' => Http::response(['data' => ['ammo' => [['caliber' => 'Caliber556x45NATO']]]]),
            'raw.githubusercontent.com/*' => Http::response([self::TARKOVDATA_ROW]),
        ]);

        $ammo = app(TarkovDevService::class)->ammo();

        $this->assertSame('Caliber556x45NATO', $ammo[0]['caliber']);
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'raw.githubusercontent.com'));
    }
}
