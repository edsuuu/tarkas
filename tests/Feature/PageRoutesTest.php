<?php

namespace Tests\Feature;

use App\Services\TarkovDevService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PageRoutesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(TarkovDevService::class)->shouldIgnoreMissing([]);
    }

    public static function pages(): array
    {
        return [
            'dashboard' => ['/', 'Dashboard — Tarkas'],
            'itens' => ['/itens', 'Itens &amp; Flea Market — Tarkas'],
            'municao' => ['/municao', 'Munição — Tarkas'],
            'quests' => ['/quests', 'Quests — Tarkas'],
            'hideout' => ['/hideout', 'Hideout — Tarkas'],
            'traders' => ['/traders', 'Traders — Tarkas'],
            'trocas' => ['/trocas', 'Trocas (Barters) — Tarkas'],
            'crafts' => ['/crafts', 'Crafts — Tarkas'],
            'mapas' => ['/mapas', 'Mapas — Tarkas'],
        ];
    }

    /**
     * A blade intermediária de cada rota precisa montar o layout e o título;
     * se o Route::view apontar para a view errada, o título quebra aqui.
     */
    #[DataProvider('pages')]
    public function test_page_renders_with_its_title(string $uri, string $title): void
    {
        $this->get($uri)
            ->assertOk()
            ->assertSee("<title>{$title}</title>", false);
    }

    /** O {id} da rota tem de chegar na blade e ser repassado ao componente. */
    public function test_item_route_passes_id_to_the_component(): void
    {
        $this->get('/itens/5449016a4bdc2d6f028b456f')
            ->assertOk()
            ->assertSee('<title>Item — Tarkas</title>', false);
    }
}
