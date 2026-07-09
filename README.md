# Tarkas

Dashboard de **Escape from Tarkov** construído com **Laravel 13 + Livewire 4** (componentes com classe + view Blade), consumindo a API GraphQL pública do [tarkov.dev](https://tarkov.dev) — **sem autenticação nem chave de API**.

## Telas

| Rota | Tela |
|---|---|
| `/` | Dashboard — status dos servidores do jogo, avisos e reset de estoque dos traders |
| `/itens` | Busca de itens com preços da flea market (média 24h, variação 48h) e melhor venda a trader |
| `/itens/{id}` | Detalhe do item — histórico de preço (sparkline), onde comprar/vender, quests, crafts e trocas que o usam |
| `/municao` | Tabela de munição separada por tier de armadura (C1–C6), com filtro por tier/calibre, ordenável |
| `/quests` | Missões por trader com objetivos, requisitos e recompensas; filtro Kappa |
| `/hideout` | Estações em accordion (uma aberta por vez) com requisitos por nível e busca por item necessário |
| `/traders` | Traders com níveis de lealdade, reset de estoque e moeda |
| `/trocas` | Barters com lucro estimado (valor de flea recebido − custo dos itens exigidos) |
| `/crafts` | Produções do hideout com lucro e lucro por hora |
| `/mapas` | Mapas com chefes (chance de spawn), extrações por facção e chaves |

## Arquitetura

- `app/Services/TarkovDevService.php` — cliente GraphQL único com cache em duas camadas: nenhuma consulta bate na API mais de **uma vez por hora** (quests/hideout/mapas: 6 h), e a última resposta boa fica guardada como *fallback* — se a API cair com o cache expirado, a tela mostra os dados antigos em vez de erro. Todos os textos vêm traduzidos da API com `lang: pt`.
- `php artisan tarkas:warm` pré-aquece o cache de todas as telas (`--fresh` limpa antes e força busca nova). Há um agendamento `hourly()` registrado — para renová-lo sozinho, basta ativar o cron do scheduler: `* * * * * cd /var/www/projects/tarkas && php artisan schedule:run >> /dev/null 2>&1`.
- `app/Livewire/**` — um componente full-page por tela, com filtros reativos (`wire:model.live`), estado na URL (`#[Url]`) e paginação incremental ("carregar mais").
- `resources/views/livewire/**` — views Blade dos componentes; layout em `resources/views/layouts/app.blade.php` com navegação SPA (`wire:navigate`).
- Sem banco de dados: sessão e cache usam driver `file`. CSS via Tailwind CDN (dev).

## Rodando

Servido pelo nginx do WSL em **http://tarkas.localhost** (config em `/etc/nginx/sites-available/tarkas.conf`, PHP-FPM 8.4, pool rodando como usuário local). Browsers resolvem `*.localhost` para 127.0.0.1 sozinhos — não precisa mexer no hosts.

Alternativa sem nginx:

```bash
composer install
cp .env.example .env && php artisan key:generate   # se necessário
php artisan serve
```

Se a API estiver fora do ar, cada tela mostra um banner de erro com botão "Tentar novamente".
