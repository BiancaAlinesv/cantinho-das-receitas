# Cantinho das Receitas

Aplicação de receitas construída com Laravel 13, Livewire 4, Volt e Tailwind CSS.

## Desenvolvimento local

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve --port=8001
```

Abra `http://localhost:8001`.

Para desenvolvimento com hot reload:

```bash
npm run dev
```

O projeto local usa SQLite em `database/database.sqlite`. O usuário de demonstração criado pelo seeder é `test@example.com`.

## Funcionalidades

- Catálogo, busca e categorias.
- Página detalhada com ingredientes e cálculo de porções.
- Curtidas, favoritos, avaliações e comentários.
- Compartilhamento e receitas relacionadas.
- Perfil, receitas próprias e edição protegida.
- Painel administrativo em `/admin`.
- SEO com JSON-LD, sitemap e robots.txt.

## Testes e build

```bash
php artisan test
npm run build
php artisan view:cache
```

## Deploy

O roteiro de produção está em [docs/deploy.md](docs/deploy.md).
