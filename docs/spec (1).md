# Cantinho das Receitas — Guia de Desenvolvimento Passo a Passo

> Este arquivo é o seu roteiro de código. Siga a ordem das fases. Para cada passo você vai ver:
> **📁 Caminho do arquivo** → **📄 Nome do arquivo** → **código completo**.
> Stack: **Laravel 13 + Livewire 4 (SFC) + PostgreSQL + Tailwind CSS 4**.

---

## Sumário

- [0. Antes de começar](#0-antes-de-começar)
- [Fase 0 — Setup do ambiente](#fase-0--setup-do-ambiente)
- [Fase 1 — Fundação (auth, layout, admin)](#fase-1--fundação-auth-layout-admin)
- [Fase 2 — Categorias e CRUD de receitas](#fase-2--categorias-e-crud-de-receitas)
- [Fase 3 — Home, listagem e busca](#fase-3--home-listagem-e-busca)
- [Fase 4 — Página da receita](#fase-4--página-da-receita)
- [Fase 5 — Calculadora de porções](#fase-5--calculadora-de-porções)
- [Fase 6 — Interações sociais](#fase-6--interações-sociais)
- [Fase 7 — Compartilhamento e relacionadas](#fase-7--compartilhamento-e-relacionadas)
- [Fase 8 — Área do usuário](#fase-8--área-do-usuário)
- [Fase 9 — Painel administrativo](#fase-9--painel-administrativo)
- [Fase 10 — SEO](#fase-10--seo)
- [Fase 11 — Performance e cache](#fase-11--performance-e-cache)
- [Fase 12 — Segurança](#fase-12--segurança)
- [Fase 13 — Deploy](#fase-13--deploy)
- [Fase 14 — Backlog (funcionalidades futuras)](#fase-14--backlog-funcionalidades-futuras)
- [Checklist geral](#checklist-geral)

---

## 0. Antes de começar

### Lembrete rápido de sintaxe do Livewire 4 SFC

Todo componente SFC segue este molde — memorize isso, você vai repetir centenas de vezes:

```php
{{-- topo do arquivo: bloco PHP com a "classe" do componente --}}
@php
new class extends Livewire\Component {
    // 1) propriedades públicas = o "estado" do componente
    public string $exemplo = '';

    // 2) mount() roda quando o componente é criado (equivalente a um "construtor")
    public function mount(): void
    {
        //
    }

    // 3) métodos públicos podem ser chamados do HTML com wire:click="metodo"
    public function metodo(): void
    {
        //
    }
}
@endphp

{{-- a partir daqui é Blade normal, com acesso direto às propriedades e métodos acima --}}
<div>
    {{ $exemplo }}
</div>
```

Regras práticas:
- **Nome do arquivo = nome do componente.** `⚡cartao-receita.blade.php` vira `<livewire:cartao-receita />`.
- **Componentes de página inteira** ficam em `resources/views/pages/` e são chamados via `Route::livewire()`.
- **Componentes reutilizáveis** (pedaços de página) ficam em `resources/views/livewire/`.
- O prefixo `⚡` é só uma convenção visual para diferenciar SFC de Blade comum — se seu sistema de arquivos/editor não aceitar o emoji no nome, pode usar o arquivo sem `⚡`, funciona igual.

---

## Fase 0 — Setup do ambiente

### Passo 0.1 — Criar o projeto

```bash
composer create-project laravel/laravel cantinho-receitas
cd cantinho-receitas
```

### Passo 0.2 — Criar o banco no PostgreSQL

```bash
psql -U postgres -c "CREATE DATABASE cantinho_receitas;"
```

### Passo 0.3 — Configurar variáveis de ambiente

📁 **Caminho:** raiz do projeto
📄 **Arquivo:** `.env`

```env
APP_NAME="Cantinho das Receitas"
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cantinho_receitas
DB_USERNAME=postgres
DB_PASSWORD=sua_senha
```

### Passo 0.4 — Instalar o Livewire 4

```bash
composer require livewire/livewire
```

### Passo 0.5 — Instalar Tailwind CSS 4

```bash
npm install tailwindcss @tailwindcss/vite
```

📁 **Caminho:** raiz do projeto
📄 **Arquivo:** `vite.config.js`

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
```

📁 **Caminho:** `resources/css/`
📄 **Arquivo:** `app.css`

```css
@import "tailwindcss";
```

### Passo 0.6 — Rodar o projeto

```bash
php artisan migrate
npm install
composer run dev
```

Abra `http://localhost:8000` — deve aparecer a página padrão do Laravel, sem erros no terminal.

**✅ Entregável da Fase 0:** projeto rodando localmente, conectado ao PostgreSQL.

---

## Fase 1 — Fundação (auth, layout, admin)

### Passo 1.1 — Migration: campos extras em `users`

```bash
php artisan make:migration add_campos_extras_to_users_table
```

📁 **Caminho:** `database/migrations/`
📄 **Arquivo:** `xxxx_xx_xx_xxxxxx_add_campos_extras_to_users_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_path')->nullable()->after('email');
            $table->text('bio')->nullable()->after('avatar_path');
            $table->boolean('is_admin')->default(false)->after('bio');
            $table->string('google_id')->nullable()->after('is_admin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar_path', 'bio', 'is_admin', 'google_id']);
        });
    }
};
```

```bash
php artisan migrate
```

### Passo 1.2 — Model `User` atualizado

📁 **Caminho:** `app/Models/`
📄 **Arquivo:** `User.php`

```php
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'avatar_path', 'bio', 'is_admin', 'google_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function receitas()
    {
        return $this->hasMany(Receita::class);
    }
}
```

### Passo 1.3 — Rotas de autenticação

📁 **Caminho:** `routes/`
📄 **Arquivo:** `auth.php` (crie este arquivo)

```php
<?php

use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::livewire('/cadastro', 'pages::auth.cadastro')->name('cadastro');
    Route::livewire('/login', 'pages::auth.login')->name('login');
});

Route::middleware('auth')->post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');
```

Registre esse arquivo em `routes/web.php` (veja passo 1.6).

### Passo 1.4 — Componente de Cadastro

```bash
php artisan make:livewire auth.cadastro
```

📁 **Caminho:** `resources/views/pages/auth/`
📄 **Arquivo:** `⚡cadastro.blade.php`

```php
@php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

new class extends Livewire\Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function cadastrar(): void
    {
        $dados = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $dados['name'],
            'email' => $dados['email'],
            'password' => Hash::make($dados['password']),
        ]);

        auth()->login($user);

        $this->redirect('/', navigate: true);
    }
}
@endphp

<div class="max-w-md mx-auto mt-16 p-8 bg-white rounded-xl shadow">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Criar conta</h1>

    <form wire:submit="cadastrar" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Nome</label>
            <input type="text" wire:model="name"
                   class="mt-1 w-full rounded-lg border-gray-300 focus:ring-orange-500 focus:border-orange-500">
            @error('name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">E-mail</label>
            <input type="email" wire:model="email"
                   class="mt-1 w-full rounded-lg border-gray-300 focus:ring-orange-500 focus:border-orange-500">
            @error('email') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Senha</label>
            <input type="password" wire:model="password"
                   class="mt-1 w-full rounded-lg border-gray-300 focus:ring-orange-500 focus:border-orange-500">
            @error('password') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Confirmar senha</label>
            <input type="password" wire:model="password_confirmation"
                   class="mt-1 w-full rounded-lg border-gray-300 focus:ring-orange-500 focus:border-orange-500">
        </div>

        <button type="submit"
                class="w-full bg-orange-600 text-white py-2 rounded-lg font-semibold hover:bg-orange-700">
            Cadastrar
        </button>
    </form>

    <p class="text-sm text-gray-600 mt-4">
        Já tem conta? <a href="{{ route('login') }}" class="text-orange-600 font-medium">Entrar</a>
    </p>
</div>
```

### Passo 1.5 — Componente de Login

```bash
php artisan make:livewire auth.login
```

📁 **Caminho:** `resources/views/pages/auth/`
📄 **Arquivo:** `⚡login.blade.php`

```php
@php
new class extends Livewire\Component {
    public string $email = '';
    public string $password = '';
    public string $erro = '';

    public function entrar(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! auth()->attempt(['email' => $this->email, 'password' => $this->password])) {
            $this->erro = 'E-mail ou senha inválidos.';
            return;
        }

        request()->session()->regenerate();
        $this->redirect('/', navigate: true);
    }
}
@endphp

<div class="max-w-md mx-auto mt-16 p-8 bg-white rounded-xl shadow">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Entrar</h1>

    @if ($erro)
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm">{{ $erro }}</div>
    @endif

    <form wire:submit="entrar" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">E-mail</label>
            <input type="email" wire:model="email"
                   class="mt-1 w-full rounded-lg border-gray-300 focus:ring-orange-500 focus:border-orange-500">
            @error('email') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Senha</label>
            <input type="password" wire:model="password"
                   class="mt-1 w-full rounded-lg border-gray-300 focus:ring-orange-500 focus:border-orange-500">
        </div>

        <button type="submit"
                class="w-full bg-orange-600 text-white py-2 rounded-lg font-semibold hover:bg-orange-700">
            Entrar
        </button>
    </form>

    <p class="text-sm text-gray-600 mt-4">
        Não tem conta? <a href="{{ route('cadastro') }}" class="text-orange-600 font-medium">Cadastre-se</a>
    </p>
</div>
```

### Passo 1.6 — Rotas principais

📁 **Caminho:** `routes/`
📄 **Arquivo:** `web.php`

```php
<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

Route::livewire('/', 'pages::inicio')->name('inicio');
```

### Passo 1.7 — Middleware de administrador

```bash
php artisan make:middleware EnsureUserIsAdmin
```

📁 **Caminho:** `app/Http/Middleware/`
📄 **Arquivo:** `EnsureUserIsAdmin.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->is_admin) {
            abort(403, 'Acesso restrito a administradores.');
        }

        return $next($request);
    }
}
```

📁 **Caminho:** `bootstrap/`
📄 **Arquivo:** `app.php` (adicione dentro do `->withMiddleware()`)

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
    ]);
})
```

### Passo 1.8 — Layout público

📁 **Caminho:** `resources/views/layouts/`
📄 **Arquivo:** `app.blade.php`

```php
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Cantinho das Receitas')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 text-gray-800">

    <header class="bg-white shadow-sm">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('inicio') }}" class="text-xl font-bold text-orange-600">
                🍲 Cantinho das Receitas
            </a>

            <nav class="flex items-center gap-4 text-sm">
                <a href="{{ route('receitas.listar') }}" class="hover:text-orange-600">Receitas</a>

                @auth
                    <a href="{{ route('minhas-receitas') }}" class="hover:text-orange-600">Minhas receitas</a>
                    <a href="{{ route('perfil') }}" class="hover:text-orange-600">Perfil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="hover:text-orange-600">Sair</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="hover:text-orange-600">Entrar</a>
                    <a href="{{ route('cadastro') }}"
                       class="bg-orange-600 text-white px-3 py-1.5 rounded-lg hover:bg-orange-700">
                        Cadastrar
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8">
        {{ $slot }}
    </main>

    <footer class="mt-16 py-6 text-center text-sm text-gray-500 border-t">
        &copy; {{ date('Y') }} Cantinho das Receitas
    </footer>

    @livewireScripts
</body>
</html>
```

> Componentes de página (`pages::`) usam esse layout automaticamente via `<x-layouts.app>` ou definindo o layout no próprio componente — em cada componente de página deste guia, envolva o HTML final com `<x-layouts.app>...conteúdo...</x-layouts.app>` quando quiser esse cabeçalho/rodapé. Nos exemplos de auth acima (tela cheia, sem menu) isso foi omitido de propósito.

### Passo 1.9 — Layout admin

📁 **Caminho:** `resources/views/layouts/`
📄 **Arquivo:** `admin.blade.php`

```php
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Admin — Cantinho das Receitas</title>
    @vite(['resources/css/app.css'])
    @livewireStyles
</head>
<body class="bg-gray-100 flex min-h-screen">

    <aside class="w-56 bg-gray-900 text-gray-200 p-4 space-y-2">
        <p class="text-white font-bold mb-4">Painel Admin</p>
        <a href="{{ route('admin.dashboard') }}" class="block py-1 hover:text-orange-400">Dashboard</a>
        <a href="{{ route('admin.receitas') }}" class="block py-1 hover:text-orange-400">Receitas</a>
        <a href="{{ route('admin.categorias') }}" class="block py-1 hover:text-orange-400">Categorias</a>
        <a href="{{ route('admin.usuarios') }}" class="block py-1 hover:text-orange-400">Usuários</a>
        <a href="{{ route('admin.comentarios') }}" class="block py-1 hover:text-orange-400">Comentários</a>
    </aside>

    <main class="flex-1 p-8">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
```

**✅ Entregável da Fase 1:** cadastro e login funcionando, header muda conforme usuário logado, base do admin pronta.

---

## Fase 2 — Categorias e CRUD de receitas

### Passo 2.1 — Migrations

```bash
php artisan make:migration create_categorias_table
php artisan make:migration create_receitas_table
php artisan make:migration create_ingredientes_table
php artisan make:migration create_receita_ingredientes_table
php artisan make:migration create_modo_preparo_passos_table
```

📁 **Caminho:** `database/migrations/`
📄 **Arquivo:** `xxxx_create_categorias_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug')->unique();
            $table->string('icone')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
```

📄 **Arquivo:** `xxxx_create_receitas_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('categoria_id')->constrained('categorias')->cascadeOnDelete();
            $table->string('titulo');
            $table->string('slug')->unique();
            $table->text('descricao');
            $table->string('foto_principal_path')->nullable();
            $table->unsignedInteger('tempo_preparo_min');
            $table->unsignedInteger('tempo_cozimento_min')->nullable();
            $table->unsignedInteger('porcoes');
            $table->enum('custo', ['baixo', 'medio', 'alto']);
            $table->enum('dificuldade', ['facil', 'medio', 'dificil']);
            $table->string('rendimento')->nullable();
            $table->text('dicas')->nullable();
            $table->text('variacoes')->nullable();
            $table->text('observacoes')->nullable();
            $table->string('video_url')->nullable();
            $table->enum('status', ['rascunho', 'publicada'])->default('rascunho');
            $table->unsignedBigInteger('visualizacoes_total')->default(0);
            $table->decimal('nota_media', 3, 2)->default(0);
            $table->unsignedInteger('total_avaliacoes')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receitas');
    }
};
```

📄 **Arquivo:** `xxxx_create_ingredientes_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredientes', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredientes');
    }
};
```

📄 **Arquivo:** `xxxx_create_receita_ingredientes_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receita_ingredientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receita_id')->constrained('receitas')->cascadeOnDelete();
            $table->foreignId('ingrediente_id')->constrained('ingredientes')->cascadeOnDelete();
            $table->decimal('quantidade', 10, 2);
            $table->enum('unidade', ['g', 'kg', 'ml', 'l', 'xicara', 'colher_sopa', 'colher_cha', 'unidade', 'a_gosto']);
            $table->string('observacao')->nullable();
            $table->unsignedSmallInteger('ordem')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receita_ingredientes');
    }
};
```

📄 **Arquivo:** `xxxx_create_modo_preparo_passos_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modo_preparo_passos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receita_id')->constrained('receitas')->cascadeOnDelete();
            $table->unsignedSmallInteger('ordem');
            $table->text('descricao');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modo_preparo_passos');
    }
};
```

```bash
php artisan migrate
```

### Passo 2.2 — Models

📁 **Caminho:** `app/Models/`
📄 **Arquivo:** `Categoria.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $fillable = ['nome', 'slug', 'icone'];

    public function receitas()
    {
        return $this->hasMany(Receita::class);
    }
}
```

📄 **Arquivo:** `Ingrediente.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingrediente extends Model
{
    protected $fillable = ['nome'];
}
```

📄 **Arquivo:** `ReceitaIngrediente.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceitaIngrediente extends Model
{
    public $timestamps = false;

    protected $fillable = ['receita_id', 'ingrediente_id', 'quantidade', 'unidade', 'observacao', 'ordem'];

    public function ingrediente()
    {
        return $this->belongsTo(Ingrediente::class);
    }
}
```

📄 **Arquivo:** `ModoPreparoPasso.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModoPreparoPasso extends Model
{
    public $timestamps = false;

    protected $fillable = ['receita_id', 'ordem', 'descricao'];
}
```

📄 **Arquivo:** `Receita.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Receita extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'categoria_id', 'titulo', 'slug', 'descricao', 'foto_principal_path',
        'tempo_preparo_min', 'tempo_cozimento_min', 'porcoes', 'custo', 'dificuldade',
        'rendimento', 'dicas', 'variacoes', 'observacoes', 'video_url', 'status', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Receita $receita) {
            $receita->slug = static::gerarSlugUnico($receita->titulo);
        });
    }

    public static function gerarSlugUnico(string $titulo): string
    {
        $slugBase = Str::slug($titulo);
        $slug = $slugBase;
        $contador = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$slugBase}-{$contador}";
            $contador++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function ingredientes()
    {
        return $this->hasMany(ReceitaIngrediente::class)->orderBy('ordem');
    }

    public function passos()
    {
        return $this->hasMany(ModoPreparoPasso::class)->orderBy('ordem');
    }

    public function tempoTotalMin(): int
    {
        return $this->tempo_preparo_min + (int) $this->tempo_cozimento_min;
    }
}
```

### Passo 2.3 — Seeder de categorias

```bash
php artisan make:seeder CategoriaSeeder
```

📁 **Caminho:** `database/seeders/`
📄 **Arquivo:** `CategoriaSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            'Bolos', 'Doces', 'Massas', 'Carnes', 'Saladas',
            'Bebidas', 'Sobremesas', 'Fitness', 'Veganas', 'Café da manhã',
        ];

        foreach ($categorias as $nome) {
            Categoria::create([
                'nome' => $nome,
                'slug' => Str::slug($nome),
            ]);
        }
    }
}
```

📁 **Caminho:** `database/seeders/`
📄 **Arquivo:** `DatabaseSeeder.php` (edite o método `run`)

```php
public function run(): void
{
    $this->call([
        CategoriaSeeder::class,
    ]);
}
```

```bash
php artisan db:seed
```

### Passo 2.4 — Formulário de receita (criar/editar)

```bash
php artisan make:livewire receitas.formulario-receita
```

📁 **Caminho:** `resources/views/livewire/receitas/`
📄 **Arquivo:** `⚡formulario-receita.blade.php`

```php
@php
use App\Models\Categoria;
use App\Models\Receita;
use Illuminate\Support\Facades\Storage;

new class extends Livewire\Component {
    use \Livewire\WithFileUploads;

    public ?Receita $receita = null;

    public string $titulo = '';
    public string $descricao = '';
    public $foto_principal = null;
    public ?int $categoria_id = null;
    public int $tempo_preparo_min = 30;
    public ?int $tempo_cozimento_min = null;
    public int $porcoes = 4;
    public string $custo = 'medio';
    public string $dificuldade = 'facil';

    public function mount(?Receita $receita = null): void
    {
        $this->receita = $receita;

        if ($receita?->exists) {
            $this->titulo = $receita->titulo;
            $this->descricao = $receita->descricao;
            $this->categoria_id = $receita->categoria_id;
            $this->tempo_preparo_min = $receita->tempo_preparo_min;
            $this->tempo_cozimento_min = $receita->tempo_cozimento_min;
            $this->porcoes = $receita->porcoes;
            $this->custo = $receita->custo;
            $this->dificuldade = $receita->dificuldade;
        }
    }

    public function categorias()
    {
        return Categoria::orderBy('nome')->get();
    }

    protected function regras(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['required', 'string'],
            'foto_principal' => ['nullable', 'image', 'max:4096'],
            'categoria_id' => ['required', 'exists:categorias,id'],
            'tempo_preparo_min' => ['required', 'integer', 'min:1'],
            'tempo_cozimento_min' => ['nullable', 'integer', 'min:0'],
            'porcoes' => ['required', 'integer', 'min:1'],
            'custo' => ['required', 'in:baixo,medio,alto'],
            'dificuldade' => ['required', 'in:facil,medio,dificil'],
        ];
    }

    public function salvar(string $status): void
    {
        $dados = $this->validate($this->regras());
        $dados['status'] = $status;
        $dados['user_id'] = auth()->id();

        if ($status === 'publicada') {
            $dados['published_at'] = now();
        }

        if ($this->foto_principal) {
            $dados['foto_principal_path'] = $this->foto_principal->store('receitas', 'public');
        }

        if ($this->receita?->exists) {
            $this->receita->update($dados);
            $receitaSalva = $this->receita;
        } else {
            $receitaSalva = Receita::create($dados);
        }

        $this->redirect(route('receitas.mostrar', $receitaSalva), navigate: true);
    }
}
@endphp

<div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow space-y-4">
    <h1 class="text-xl font-bold">{{ $receita?->exists ? 'Editar receita' : 'Nova receita' }}</h1>

    <div>
        <label class="block text-sm font-medium">Título</label>
        <input type="text" wire:model="titulo" class="mt-1 w-full rounded-lg border-gray-300">
        @error('titulo') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium">Descrição</label>
        <textarea wire:model="descricao" rows="3" class="mt-1 w-full rounded-lg border-gray-300"></textarea>
        @error('descricao') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium">Foto principal</label>
        <input type="file" wire:model="foto_principal" class="mt-1 w-full">
        @error('foto_principal') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium">Categoria</label>
        <select wire:model="categoria_id" class="mt-1 w-full rounded-lg border-gray-300">
            <option value="">Selecione...</option>
            @foreach ($this->categorias() as $categoria)
                <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
            @endforeach
        </select>
        @error('categoria_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium">Tempo de preparo (min)</label>
            <input type="number" wire:model="tempo_preparo_min" class="mt-1 w-full rounded-lg border-gray-300">
        </div>
        <div>
            <label class="block text-sm font-medium">Tempo de cozimento (min)</label>
            <input type="number" wire:model="tempo_cozimento_min" class="mt-1 w-full rounded-lg border-gray-300">
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium">Porções</label>
            <input type="number" wire:model="porcoes" class="mt-1 w-full rounded-lg border-gray-300">
        </div>
        <div>
            <label class="block text-sm font-medium">Custo</label>
            <select wire:model="custo" class="mt-1 w-full rounded-lg border-gray-300">
                <option value="baixo">Baixo</option>
                <option value="medio">Médio</option>
                <option value="alto">Alto</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium">Dificuldade</label>
            <select wire:model="dificuldade" class="mt-1 w-full rounded-lg border-gray-300">
                <option value="facil">Fácil</option>
                <option value="medio">Médio</option>
                <option value="dificil">Difícil</option>
            </select>
        </div>
    </div>

    <div class="flex gap-3 pt-4">
        <button wire:click="salvar('rascunho')" type="button"
                class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">
            Salvar rascunho
        </button>
        <button wire:click="salvar('publicada')" type="button"
                class="px-4 py-2 rounded-lg bg-orange-600 text-white hover:bg-orange-700">
            Publicar
        </button>
    </div>
</div>
```

> ⚠️ Este formulário cobre os campos básicos. Ingredientes e modo de preparo (listas dinâmicas) são adicionados na **Fase 4**, quando construímos a página completa da receita — para não sobrecarregar o formulário nesta fase inicial.

### Passo 2.5 — Página de criar receita

```bash
php artisan make:livewire receitas.criar
```

📁 **Caminho:** `resources/views/pages/receitas/`
📄 **Arquivo:** `⚡criar.blade.php`

```php
<div>
    <livewire:receitas.formulario-receita />
</div>
```

📁 **Caminho:** `routes/`
📄 **Arquivo:** `web.php` (adicione)

```php
Route::middleware('auth')->group(function () {
    Route::livewire('/receitas/criar', 'pages::receitas.criar')->name('receitas.criar');
});
```

**✅ Entregável da Fase 2:** usuário logado acessa `/receitas/criar`, preenche o formulário básico e salva uma receita (rascunho ou publicada) no banco.


---

## Fase 3 — Home, listagem e busca

### Passo 3.1 — Componente cartão de receita

```bash
php artisan make:livewire receitas.cartao-receita
```

📁 **Caminho:** `resources/views/livewire/receitas/`
📄 **Arquivo:** `⚡cartao-receita.blade.php`

```php
@php
use App\Models\Receita;

new class extends Livewire\Component {
    public Receita $receita;
}
@endphp

<a href="{{ route('receitas.mostrar', $receita) }}" wire:navigate
   class="block bg-white rounded-xl shadow hover:shadow-md transition overflow-hidden">
    <img src="{{ $receita->foto_principal_path ? Storage::url($receita->foto_principal_path) : 'https://placehold.co/400x250?text=Receita' }}"
         alt="{{ $receita->titulo }}" loading="lazy" class="w-full h-40 object-cover">

    <div class="p-4">
        <h3 class="font-semibold text-gray-800 truncate">{{ $receita->titulo }}</h3>

        <div class="flex items-center gap-3 text-xs text-gray-500 mt-2">
            <span>⏱ {{ $receita->tempoTotalMin() }} min</span>
            <span>📊 {{ ucfirst($receita->dificuldade) }}</span>
        </div>

        <div class="flex items-center gap-1 text-sm mt-2">
            <span class="text-yellow-500">★</span>
            <span class="font-medium">{{ number_format($receita->nota_media, 1) }}</span>
            <span class="text-gray-400">({{ $receita->total_avaliacoes }})</span>
        </div>
    </div>
</a>
```

### Passo 3.2 — Barra de pesquisa

```bash
php artisan make:livewire busca.barra-pesquisa
```

📁 **Caminho:** `resources/views/livewire/busca/`
📄 **Arquivo:** `⚡barra-pesquisa.blade.php`

```php
@php
new class extends Livewire\Component {
    public string $termo = '';

    public function pesquisar(): void
    {
        $this->redirect(route('receitas.listar', ['busca' => $this->termo]), navigate: true);
    }
}
@endphp

<form wire:submit="pesquisar" class="flex gap-2">
    <input type="text" wire:model="termo" placeholder="Buscar por nome ou ingrediente..."
           class="flex-1 rounded-lg border-gray-300 focus:ring-orange-500 focus:border-orange-500">
    <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700">
        Buscar
    </button>
</form>
```

### Passo 3.3 — Página inicial

```bash
php artisan make:livewire inicio
```

📁 **Caminho:** `resources/views/pages/`
📄 **Arquivo:** `⚡inicio.blade.php`

```php
@php
use App\Models\Categoria;
use App\Models\Receita;

new class extends Livewire\Component {
    public function ultimas()
    {
        return Receita::where('status', 'publicada')->latest('published_at')->take(4)->get();
    }

    public function maisAcessadas()
    {
        return Receita::where('status', 'publicada')->orderByDesc('visualizacoes_total')->take(4)->get();
    }

    public function aleatorias()
    {
        return Receita::where('status', 'publicada')->inRandomOrder()->take(4)->get();
    }

    public function categorias()
    {
        return Categoria::orderBy('nome')->get();
    }
}
@endphp

<x-layouts.app>
    <section class="mb-10">
        <livewire:busca.barra-pesquisa />
    </section>

    <section class="mb-10">
        <h2 class="text-lg font-bold mb-4">Últimas receitas</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach ($this->ultimas() as $receita)
                <livewire:receitas.cartao-receita :receita="$receita" :key="'ultima-'.$receita->id" />
            @endforeach
        </div>
    </section>

    <section class="mb-10">
        <h2 class="text-lg font-bold mb-4">Mais acessadas</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach ($this->maisAcessadas() as $receita)
                <livewire:receitas.cartao-receita :receita="$receita" :key="'acessada-'.$receita->id" />
            @endforeach
        </div>
    </section>

    <section class="mb-10">
        <h2 class="text-lg font-bold mb-4">Categorias</h2>
        <div class="flex flex-wrap gap-2">
            @foreach ($this->categorias() as $categoria)
                <a href="{{ route('receitas.listar', ['categoria' => $categoria->slug]) }}" wire:navigate
                   class="px-3 py-1.5 bg-white rounded-full border text-sm hover:border-orange-500">
                    {{ $categoria->nome }}
                </a>
            @endforeach
        </div>
    </section>

    <section>
        <h2 class="text-lg font-bold mb-4">Descubra algo novo</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach ($this->aleatorias() as $receita)
                <livewire:receitas.cartao-receita :receita="$receita" :key="'aleatoria-'.$receita->id" />
            @endforeach
        </div>
    </section>
</x-layouts.app>
```

> 📌 `<x-layouts.app>` funciona porque o Laravel reconhece qualquer arquivo em `resources/views/layouts/*.blade.php` como componente Blade automaticamente. Dentro dele, `{{ $slot }}` (Passo 1.8) recebe todo esse conteúdo.

### Passo 3.4 — Filtros de listagem

```bash
php artisan make:livewire busca.filtros-receitas
```

📁 **Caminho:** `resources/views/livewire/busca/`
📄 **Arquivo:** `⚡filtros-receitas.blade.php`

```php
@php
use App\Models\Categoria;

new class extends Livewire\Component {
    #[\Livewire\Attributes\Modelable]
    public array $filtros = [];

    public function categorias()
    {
        return Categoria::orderBy('nome')->get();
    }
}
@endphp

<div class="bg-white p-4 rounded-xl shadow space-y-4">
    <div>
        <label class="block text-sm font-medium">Categoria</label>
        <select wire:model.live="filtros.categoria" class="mt-1 w-full rounded-lg border-gray-300">
            <option value="">Todas</option>
            @foreach ($this->categorias() as $categoria)
                <option value="{{ $categoria->slug }}">{{ $categoria->nome }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium">Dificuldade</label>
        <select wire:model.live="filtros.dificuldade" class="mt-1 w-full rounded-lg border-gray-300">
            <option value="">Todas</option>
            <option value="facil">Fácil</option>
            <option value="medio">Médio</option>
            <option value="dificil">Difícil</option>
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium">Custo</label>
        <select wire:model.live="filtros.custo" class="mt-1 w-full rounded-lg border-gray-300">
            <option value="">Todos</option>
            <option value="baixo">Baixo</option>
            <option value="medio">Médio</option>
            <option value="alto">Alto</option>
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium">Ordenar por</label>
        <select wire:model.live="filtros.ordem" class="mt-1 w-full rounded-lg border-gray-300">
            <option value="recentes">Mais recentes</option>
            <option value="populares">Mais populares</option>
            <option value="alfabetica">Ordem alfabética</option>
        </select>
    </div>
</div>
```

### Passo 3.5 — Página de listagem com busca

```bash
php artisan make:livewire receitas.listar
```

📁 **Caminho:** `resources/views/pages/receitas/`
📄 **Arquivo:** `⚡listar.blade.php`

```php
@php
use App\Models\Receita;
use Livewire\Attributes\Url;

new class extends Livewire\Component {
    #[Url]
    public string $busca = '';

    #[Url]
    public array $filtros = ['categoria' => '', 'dificuldade' => '', 'custo' => '', 'ordem' => 'recentes'];

    public function receitas()
    {
        $query = Receita::where('status', 'publicada')->with('categoria');

        if ($this->busca !== '') {
            $termo = '%'.$this->busca.'%';
            $query->where(function ($q) use ($termo) {
                $q->where('titulo', 'ilike', $termo)
                  ->orWhereHas('ingredientes.ingrediente', function ($q2) use ($termo) {
                      $q2->where('nome', 'ilike', $termo);
                  });
            });
        }

        if (! empty($this->filtros['categoria'])) {
            $query->whereHas('categoria', fn ($q) => $q->where('slug', $this->filtros['categoria']));
        }

        if (! empty($this->filtros['dificuldade'])) {
            $query->where('dificuldade', $this->filtros['dificuldade']);
        }

        if (! empty($this->filtros['custo'])) {
            $query->where('custo', $this->filtros['custo']);
        }

        match ($this->filtros['ordem']) {
            'populares' => $query->orderByDesc('visualizacoes_total'),
            'alfabetica' => $query->orderBy('titulo'),
            default => $query->latest('published_at'),
        };

        return $query->paginate(12);
    }
}
@endphp

<x-layouts.app>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="md:col-span-1 space-y-4">
            <input type="text" wire:model.live.debounce.400ms="busca" placeholder="Buscar..."
                   class="w-full rounded-lg border-gray-300">
            <livewire:busca.filtros-receitas wire:model="filtros" />
        </div>

        <div class="md:col-span-3">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse ($this->receitas() as $receita)
                    <livewire:receitas.cartao-receita :receita="$receita" :key="$receita->id" />
                @empty
                    <p class="col-span-full text-gray-500">Nenhuma receita encontrada.</p>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $this->receitas()->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
```

📁 **Caminho:** `routes/`
📄 **Arquivo:** `web.php` (adicione)

```php
Route::livewire('/receitas', 'pages::receitas.listar')->name('receitas.listar');
```

**✅ Entregável da Fase 3:** home mostra as seções pedidas; `/receitas` permite buscar e filtrar; buscar "bolo" retorna receitas com "bolo" no título ou nos ingredientes.

---

## Fase 4 — Página da receita

### Passo 4.1 — Página completa da receita

```bash
php artisan make:livewire receitas.mostrar
```

📁 **Caminho:** `resources/views/pages/receitas/`
📄 **Arquivo:** `⚡mostrar.blade.php`

```php
@php
use App\Models\Receita;
use App\Models\Visualizacao;

new class extends Livewire\Component {
    public Receita $receita;

    public function mount(Receita $receita): void
    {
        $this->receita = $receita->load(['categoria', 'ingredientes.ingrediente', 'passos', 'user']);

        $this->registrarVisualizacao();
    }

    protected function registrarVisualizacao(): void
    {
        $ipHash = hash('sha256', request()->ip());
        $jaVisualizouHoje = Visualizacao::where('receita_id', $this->receita->id)
            ->where('ip_hash', $ipHash)
            ->whereDate('created_at', today())
            ->exists();

        if ($jaVisualizouHoje) {
            return;
        }

        Visualizacao::create([
            'receita_id' => $this->receita->id,
            'user_id' => auth()->id(),
            'ip_hash' => $ipHash,
        ]);

        $this->receita->increment('visualizacoes_total');
    }

    public function idYoutube(): ?string
    {
        if (! $this->receita->video_url) {
            return null;
        }

        preg_match('/(?:youtu\.be\/|v=)([\w-]{11})/', $this->receita->video_url, $matches);

        return $matches[1] ?? null;
    }
}
@endphp

<x-layouts.app>
    <article class="max-w-3xl mx-auto bg-white rounded-xl shadow overflow-hidden">
        <img src="{{ $receita->foto_principal_path ? Storage::url($receita->foto_principal_path) : 'https://placehold.co/800x400?text=Receita' }}"
             alt="{{ $receita->titulo }}" class="w-full h-72 object-cover">

        <div class="p-6 space-y-8">
            <header>
                <p class="text-sm text-orange-600 font-medium">{{ $receita->categoria->nome }}</p>
                <h1 class="text-3xl font-bold text-gray-800">{{ $receita->titulo }}</h1>
                <p class="text-gray-600 mt-2">{{ $receita->descricao }}</p>
            </header>

            <div class="flex gap-3">
                <livewire:interacoes.botao-curtir :receita="$receita" :key="'curtir-'.$receita->id" />
                <livewire:interacoes.botao-favoritar :receita="$receita" :key="'favoritar-'.$receita->id" />
            </div>

            <section class="grid grid-cols-2 md:grid-cols-5 gap-4 text-center">
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">Preparo</p>
                    <p class="font-semibold">{{ $receita->tempo_preparo_min }} min</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">Cozimento</p>
                    <p class="font-semibold">{{ $receita->tempo_cozimento_min ?? '-' }} min</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">Total</p>
                    <p class="font-semibold">{{ $receita->tempoTotalMin() }} min</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">Custo</p>
                    <p class="font-semibold">{{ ucfirst($receita->custo) }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">Dificuldade</p>
                    <p class="font-semibold">{{ ucfirst($receita->dificuldade) }}</p>
                </div>
            </section>

            <livewire:receitas.calculadora-porcoes :receita="$receita" :key="'calc-'.$receita->id" />

            <section>
                <h2 class="text-lg font-bold mb-3">Modo de preparo</h2>
                <ol class="space-y-3 list-decimal list-inside">
                    @foreach ($receita->passos as $passo)
                        <li class="text-gray-700">{{ $passo->descricao }}</li>
                    @endforeach
                </ol>
            </section>

            @if ($receita->dicas || $receita->variacoes || $receita->observacoes || $receita->rendimento)
                <section class="bg-orange-50 rounded-lg p-4 space-y-2 text-sm">
                    @if ($receita->rendimento)
                        <p><strong>Rendimento:</strong> {{ $receita->rendimento }}</p>
                    @endif
                    @if ($receita->dicas)
                        <p><strong>Dicas:</strong> {{ $receita->dicas }}</p>
                    @endif
                    @if ($receita->variacoes)
                        <p><strong>Variações:</strong> {{ $receita->variacoes }}</p>
                    @endif
                    @if ($receita->observacoes)
                        <p><strong>Observações:</strong> {{ $receita->observacoes }}</p>
                    @endif
                </section>
            @endif

            @if ($this->idYoutube())
                <section>
                    <h2 class="text-lg font-bold mb-3">Vídeo</h2>
                    <div class="aspect-video">
                        <iframe class="w-full h-full rounded-lg"
                                src="https://www.youtube.com/embed/{{ $this->idYoutube() }}"
                                allowfullscreen></iframe>
                    </div>
                </section>
            @endif

            <livewire:interacoes.avaliacao-estrelas :receita="$receita" :key="'avaliacao-'.$receita->id" />

            <livewire:interacoes.compartilhar :receita="$receita" :key="'compartilhar-'.$receita->id" />

            <livewire:interacoes.lista-comentarios :receita="$receita" :key="'comentarios-'.$receita->id" />
        </div>
    </article>
</x-layouts.app>
```

📁 **Caminho:** `routes/`
📄 **Arquivo:** `web.php` (adicione)

```php
Route::livewire('/receitas/{receita:slug}', 'pages::receitas.mostrar')->name('receitas.mostrar');
```

> Os componentes `botao-curtir`, `botao-favoritar`, `avaliacao-estrelas`, `compartilhar` e `lista-comentarios` são criados na **Fase 6 e 7**. A lista de ingredientes aparece dentro da `calculadora-porcoes` (Fase 5), para já nascer com o recálculo funcionando.

**✅ Entregável da Fase 4:** abrir `/receitas/bolo-de-chocolate` (ou o slug gerado) mostra a receita completa e conta 1 visualização.

---

## Fase 5 — Calculadora de porções

```bash
php artisan make:livewire receitas.calculadora-porcoes
```

📁 **Caminho:** `resources/views/livewire/receitas/`
📄 **Arquivo:** `⚡calculadora-porcoes.blade.php`

```php
@php
use App\Models\Receita;

new class extends Livewire\Component {
    public Receita $receita;
    public int $porcoesAtuais;

    public function mount(Receita $receita): void
    {
        $this->receita = $receita;
        $this->porcoesAtuais = $receita->porcoes;
    }

    public function aumentar(): void
    {
        $this->porcoesAtuais++;
    }

    public function diminuir(): void
    {
        if ($this->porcoesAtuais > 1) {
            $this->porcoesAtuais--;
        }
    }

    public function ingredientesRecalculados()
    {
        $fator = $this->porcoesAtuais / $this->receita->porcoes;

        return $this->receita->ingredientes->map(function ($item) use ($fator) {
            return [
                'nome' => $item->ingrediente->nome,
                'quantidade' => round($item->quantidade * $fator, 2),
                'unidade' => $item->unidade,
                'observacao' => $item->observacao,
            ];
        });
    }
}
@endphp

<section class="bg-gray-50 rounded-lg p-4">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold">Ingredientes</h2>

        <div class="flex items-center gap-3">
            <button wire:click="diminuir" type="button"
                    class="w-8 h-8 rounded-full bg-white border hover:bg-gray-100">−</button>
            <span class="font-medium">{{ $porcoesAtuais }} porções</span>
            <button wire:click="aumentar" type="button"
                    class="w-8 h-8 rounded-full bg-white border hover:bg-gray-100">+</button>
        </div>
    </div>

    <ul class="space-y-1">
        @foreach ($this->ingredientesRecalculados() as $ingrediente)
            <li class="text-gray-700">
                {{ $ingrediente['quantidade'] }} {{ $ingrediente['unidade'] !== 'unidade' ? $ingrediente['unidade'] : '' }}
                de {{ $ingrediente['nome'] }}
                @if ($ingrediente['observacao'])
                    <span class="text-gray-400 text-sm">({{ $ingrediente['observacao'] }})</span>
                @endif
            </li>
        @endforeach
    </ul>
</section>
```

**✅ Entregável da Fase 5:** clicar em `+`/`−` recalcula a lista de ingredientes instantaneamente, sem recarregar a página (ex.: 4 → 8 porções dobra as quantidades).


---

## Fase 6 — Interações sociais

### Passo 6.1 — Migrations

```bash
php artisan make:migration create_comentarios_table
php artisan make:migration create_avaliacoes_table
php artisan make:migration create_favoritos_table
php artisan make:migration create_curtidas_table
php artisan make:migration create_visualizacoes_table
```

📁 **Caminho:** `database/migrations/`
📄 **Arquivo:** `xxxx_create_comentarios_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comentarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receita_id')->constrained('receitas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('comentario_pai_id')->nullable()->constrained('comentarios')->cascadeOnDelete();
            $table->text('conteudo');
            $table->enum('status', ['publicado', 'oculto'])->default('publicado');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comentarios');
    }
};
```

📄 **Arquivo:** `xxxx_create_avaliacoes_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avaliacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receita_id')->constrained('receitas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('nota');
            $table->timestamps();

            $table->unique(['receita_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avaliacoes');
    }
};
```

📄 **Arquivo:** `xxxx_create_favoritos_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favoritos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receita_id')->constrained('receitas')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'receita_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favoritos');
    }
};
```

📄 **Arquivo:** `xxxx_create_curtidas_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curtidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receita_id')->constrained('receitas')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'receita_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curtidas');
    }
};
```

📄 **Arquivo:** `xxxx_create_visualizacoes_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visualizacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receita_id')->constrained('receitas')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_hash')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visualizacoes');
    }
};
```

```bash
php artisan migrate
```

### Passo 6.2 — Models

📁 **Caminho:** `app/Models/`
📄 **Arquivo:** `Comentario.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    protected $fillable = ['receita_id', 'user_id', 'comentario_pai_id', 'conteudo', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function respostas()
    {
        return $this->hasMany(Comentario::class, 'comentario_pai_id');
    }
}
```

📄 **Arquivo:** `Avaliacao.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Avaliacao extends Model
{
    protected $fillable = ['receita_id', 'user_id', 'nota'];
}
```

📄 **Arquivo:** `Favorito.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorito extends Model
{
    protected $fillable = ['user_id', 'receita_id'];
}
```

📄 **Arquivo:** `Curtida.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curtida extends Model
{
    protected $fillable = ['user_id', 'receita_id'];
}
```

📄 **Arquivo:** `Visualizacao.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visualizacao extends Model
{
    public $timestamps = false;

    protected $fillable = ['receita_id', 'user_id', 'ip_hash', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }
}
```

### Passo 6.3 — Observer para recalcular nota média

```bash
php artisan make:observer AvaliacaoObserver --model=Avaliacao
```

📁 **Caminho:** `app/Observers/`
📄 **Arquivo:** `AvaliacaoObserver.php`

```php
<?php

namespace App\Observers;

use App\Models\Avaliacao;
use App\Models\Receita;

class AvaliacaoObserver
{
    public function saved(Avaliacao $avaliacao): void
    {
        $this->recalcular($avaliacao->receita_id);
    }

    public function deleted(Avaliacao $avaliacao): void
    {
        $this->recalcular($avaliacao->receita_id);
    }

    protected function recalcular(int $receitaId): void
    {
        $stats = Avaliacao::where('receita_id', $receitaId)
            ->selectRaw('AVG(nota) as media, COUNT(*) as total')
            ->first();

        Receita::where('id', $receitaId)->update([
            'nota_media' => round($stats->media ?? 0, 2),
            'total_avaliacoes' => $stats->total ?? 0,
        ]);
    }
}
```

📁 **Caminho:** `app/Providers/`
📄 **Arquivo:** `AppServiceProvider.php` (edite o método `boot`)

```php
use App\Models\Avaliacao;
use App\Observers\AvaliacaoObserver;

public function boot(): void
{
    Avaliacao::observe(AvaliacaoObserver::class);
}
```

### Passo 6.4 — Botão de curtir

```bash
php artisan make:livewire interacoes.botao-curtir
```

📁 **Caminho:** `resources/views/livewire/interacoes/`
📄 **Arquivo:** `⚡botao-curtir.blade.php`

```php
@php
use App\Models\Curtida;
use App\Models\Receita;

new class extends Livewire\Component {
    public Receita $receita;
    public bool $curtido = false;
    public int $total = 0;

    public function mount(Receita $receita): void
    {
        $this->receita = $receita;
        $this->total = Curtida::where('receita_id', $receita->id)->count();

        if (auth()->check()) {
            $this->curtido = Curtida::where('receita_id', $receita->id)
                ->where('user_id', auth()->id())
                ->exists();
        }
    }

    public function alternar(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'), navigate: true);
            return;
        }

        $curtida = Curtida::where('receita_id', $this->receita->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($curtida) {
            $curtida->delete();
            $this->curtido = false;
            $this->total--;
        } else {
            Curtida::create(['receita_id' => $this->receita->id, 'user_id' => auth()->id()]);
            $this->curtido = true;
            $this->total++;
        }
    }
}
@endphp

<button wire:click="alternar" type="button"
        class="flex items-center gap-2 px-4 py-2 rounded-lg border {{ $curtido ? 'bg-red-50 border-red-300 text-red-600' : 'border-gray-300 text-gray-600' }}">
    {{ $curtido ? '❤️' : '🤍' }} Gostei
    <span class="text-sm text-gray-500">({{ $total }})</span>
</button>
```

### Passo 6.5 — Botão de favoritar

```bash
php artisan make:livewire interacoes.botao-favoritar
```

📁 **Caminho:** `resources/views/livewire/interacoes/`
📄 **Arquivo:** `⚡botao-favoritar.blade.php`

```php
@php
use App\Models\Favorito;
use App\Models\Receita;

new class extends Livewire\Component {
    public Receita $receita;
    public bool $favoritado = false;

    public function mount(Receita $receita): void
    {
        $this->receita = $receita;

        if (auth()->check()) {
            $this->favoritado = Favorito::where('receita_id', $receita->id)
                ->where('user_id', auth()->id())
                ->exists();
        }
    }

    public function alternar(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'), navigate: true);
            return;
        }

        $favorito = Favorito::where('receita_id', $this->receita->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($favorito) {
            $favorito->delete();
            $this->favoritado = false;
        } else {
            Favorito::create(['receita_id' => $this->receita->id, 'user_id' => auth()->id()]);
            $this->favoritado = true;
        }
    }
}
@endphp

<button wire:click="alternar" type="button"
        class="flex items-center gap-2 px-4 py-2 rounded-lg border {{ $favoritado ? 'bg-orange-50 border-orange-300 text-orange-600' : 'border-gray-300 text-gray-600' }}">
    {{ $favoritado ? '⭐ Salvo' : '☆ Salvar receita' }}
</button>
```

### Passo 6.6 — Avaliação em estrelas

```bash
php artisan make:livewire interacoes.avaliacao-estrelas
```

📁 **Caminho:** `resources/views/livewire/interacoes/`
📄 **Arquivo:** `⚡avaliacao-estrelas.blade.php`

```php
@php
use App\Models\Avaliacao;
use App\Models\Receita;

new class extends Livewire\Component {
    public Receita $receita;
    public int $minhaNota = 0;

    public function mount(Receita $receita): void
    {
        $this->receita = $receita;

        if (auth()->check()) {
            $this->minhaNota = Avaliacao::where('receita_id', $receita->id)
                ->where('user_id', auth()->id())
                ->value('nota') ?? 0;
        }
    }

    public function avaliar(int $nota): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'), navigate: true);
            return;
        }

        Avaliacao::updateOrCreate(
            ['receita_id' => $this->receita->id, 'user_id' => auth()->id()],
            ['nota' => $nota]
        );

        $this->minhaNota = $nota;
        $this->receita->refresh();
    }
}
@endphp

<section class="border-t pt-6">
    <h2 class="text-lg font-bold mb-2">Avaliações</h2>

    <div class="flex items-center gap-2 mb-4">
        <span class="text-2xl font-bold">{{ number_format($receita->nota_media, 1) }}</span>
        <span class="text-gray-500 text-sm">({{ $receita->total_avaliacoes }} avaliações)</span>
    </div>

    <p class="text-sm text-gray-600 mb-1">Sua avaliação:</p>
    <div class="flex gap-1">
        @for ($i = 1; $i <= 5; $i++)
            <button wire:click="avaliar({{ $i }})" type="button"
                    class="text-2xl {{ $i <= $minhaNota ? 'text-yellow-500' : 'text-gray-300' }}">
                ★
            </button>
        @endfor
    </div>
</section>
```

### Passo 6.7 — Policy de comentários

```bash
php artisan make:policy ComentarioPolicy --model=Comentario
```

📁 **Caminho:** `app/Policies/`
📄 **Arquivo:** `ComentarioPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\Comentario;
use App\Models\User;

class ComentarioPolicy
{
    public function update(User $user, Comentario $comentario): bool
    {
        return $user->id === $comentario->user_id;
    }

    public function delete(User $user, Comentario $comentario): bool
    {
        return $user->id === $comentario->user_id || $user->is_admin;
    }
}
```

### Passo 6.8 — Lista de comentários

```bash
php artisan make:livewire interacoes.lista-comentarios
```

📁 **Caminho:** `resources/views/livewire/interacoes/`
📄 **Arquivo:** `⚡lista-comentarios.blade.php`

```php
@php
use App\Models\Comentario;
use App\Models\Receita;

new class extends Livewire\Component {
    public Receita $receita;
    public string $novoComentario = '';
    public ?int $editandoId = null;
    public string $textoEdicao = '';

    public function mount(Receita $receita): void
    {
        $this->receita = $receita;
    }

    public function comentarios()
    {
        return Comentario::where('receita_id', $this->receita->id)
            ->whereNull('comentario_pai_id')
            ->where('status', 'publicado')
            ->with('user')
            ->latest()
            ->get();
    }

    public function comentar(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'), navigate: true);
            return;
        }

        $this->validate(['novoComentario' => ['required', 'string', 'max:1000']]);

        Comentario::create([
            'receita_id' => $this->receita->id,
            'user_id' => auth()->id(),
            'conteudo' => $this->novoComentario,
        ]);

        $this->novoComentario = '';
    }

    public function iniciarEdicao(int $id): void
    {
        $comentario = Comentario::findOrFail($id);
        $this->authorize('update', $comentario);

        $this->editandoId = $id;
        $this->textoEdicao = $comentario->conteudo;
    }

    public function salvarEdicao(): void
    {
        $comentario = Comentario::findOrFail($this->editandoId);
        $this->authorize('update', $comentario);

        $comentario->update(['conteudo' => $this->textoEdicao]);
        $this->editandoId = null;
    }

    public function excluir(int $id): void
    {
        $comentario = Comentario::findOrFail($id);
        $this->authorize('delete', $comentario);

        $comentario->delete();
    }
}
@endphp

<section class="border-t pt-6">
    <h2 class="text-lg font-bold mb-4">Comentários</h2>

    @auth
        <form wire:submit="comentar" class="mb-6">
            <textarea wire:model="novoComentario" rows="2" placeholder="Escreva um comentário..."
                      class="w-full rounded-lg border-gray-300"></textarea>
            @error('novoComentario') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            <button type="submit" class="mt-2 bg-orange-600 text-white px-4 py-1.5 rounded-lg hover:bg-orange-700">
                Comentar
            </button>
        </form>
    @endauth

    <div class="space-y-4">
        @foreach ($this->comentarios() as $comentario)
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-sm font-medium">{{ $comentario->user->name }}</p>

                @if ($editandoId === $comentario->id)
                    <textarea wire:model="textoEdicao" rows="2" class="w-full rounded-lg border-gray-300 mt-1"></textarea>
                    <div class="flex gap-2 mt-1">
                        <button wire:click="salvarEdicao" class="text-sm text-orange-600">Salvar</button>
                        <button wire:click="$set('editandoId', null)" class="text-sm text-gray-500">Cancelar</button>
                    </div>
                @else
                    <p class="text-gray-700 text-sm mt-1">{{ $comentario->conteudo }}</p>

                    @auth
                        @if (auth()->id() === $comentario->user_id)
                            <div class="flex gap-2 mt-1">
                                <button wire:click="iniciarEdicao({{ $comentario->id }})" class="text-xs text-orange-600">Editar</button>
                                <button wire:click="excluir({{ $comentario->id }})"
                                        wire:confirm="Excluir este comentário?" class="text-xs text-red-600">Excluir</button>
                            </div>
                        @endif
                    @endauth
                @endif
            </div>
        @endforeach
    </div>
</section>
```

**✅ Entregável da Fase 6:** curtir, favoritar, avaliar (1 a 5 estrelas) e comentar (criar/editar/excluir o próprio) funcionando, com a nota média recalculada automaticamente.

---

## Fase 7 — Compartilhamento e relacionadas

### Passo 7.1 — Migration de compartilhamentos

```bash
php artisan make:migration create_compartilhamentos_table
```

📁 **Caminho:** `database/migrations/`
📄 **Arquivo:** `xxxx_create_compartilhamentos_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compartilhamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receita_id')->constrained('receitas')->cascadeOnDelete();
            $table->enum('canal', ['whatsapp', 'facebook', 'telegram', 'x', 'pinterest', 'link']);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compartilhamentos');
    }
};
```

```bash
php artisan migrate
```

📁 **Caminho:** `app/Models/`
📄 **Arquivo:** `Compartilhamento.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compartilhamento extends Model
{
    public $timestamps = false;

    protected $fillable = ['receita_id', 'canal', 'created_at'];
}
```

### Passo 7.2 — Componente de compartilhar

```bash
php artisan make:livewire interacoes.compartilhar
```

📁 **Caminho:** `resources/views/livewire/interacoes/`
📄 **Arquivo:** `⚡compartilhar.blade.php`

```php
@php
use App\Models\Compartilhamento;
use App\Models\Receita;

new class extends Livewire\Component {
    public Receita $receita;
    public bool $linkCopiado = false;

    public function registrar(string $canal): void
    {
        Compartilhamento::create(['receita_id' => $this->receita->id, 'canal' => $canal]);

        if ($canal === 'link') {
            $this->linkCopiado = true;
        }
    }

    public function urlReceita(): string
    {
        return route('receitas.mostrar', $this->receita);
    }
}
@endphp

<section class="border-t pt-6">
    <h2 class="text-lg font-bold mb-3">Compartilhar</h2>

    <div class="flex flex-wrap gap-2">
        <a wire:click="registrar('whatsapp')" target="_blank"
           href="https://wa.me/?text={{ urlencode($receita->titulo.' - '.$this->urlReceita()) }}"
           class="px-3 py-1.5 rounded-lg bg-green-100 text-green-700 text-sm">WhatsApp</a>

        <a wire:click="registrar('facebook')" target="_blank"
           href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($this->urlReceita()) }}"
           class="px-3 py-1.5 rounded-lg bg-blue-100 text-blue-700 text-sm">Facebook</a>

        <a wire:click="registrar('telegram')" target="_blank"
           href="https://t.me/share/url?url={{ urlencode($this->urlReceita()) }}&text={{ urlencode($receita->titulo) }}"
           class="px-3 py-1.5 rounded-lg bg-sky-100 text-sky-700 text-sm">Telegram</a>

        <a wire:click="registrar('x')" target="_blank"
           href="https://x.com/intent/tweet?url={{ urlencode($this->urlReceita()) }}&text={{ urlencode($receita->titulo) }}"
           class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-700 text-sm">X</a>

        <a wire:click="registrar('pinterest')" target="_blank"
           href="https://pinterest.com/pin/create/button/?url={{ urlencode($this->urlReceita()) }}&description={{ urlencode($receita->titulo) }}"
           class="px-3 py-1.5 rounded-lg bg-red-100 text-red-700 text-sm">Pinterest</a>

        <button wire:click="registrar('link')" type="button"
                x-data x-on:click="navigator.clipboard.writeText('{{ $this->urlReceita() }}')"
                class="px-3 py-1.5 rounded-lg bg-orange-100 text-orange-700 text-sm">
            {{ $linkCopiado ? 'Link copiado!' : 'Copiar link' }}
        </button>
    </div>
</section>
```

> `x-data`/`x-on:click` usam o **Alpine.js**, que já vem incluso no Livewire — não precisa instalar nada extra.

### Passo 7.3 — Serviço de receitas relacionadas

📁 **Caminho:** `app/Services/`
📄 **Arquivo:** `ReceitaRelacionadaService.php`

```php
<?php

namespace App\Services;

use App\Models\Receita;
use Illuminate\Support\Collection;

class ReceitaRelacionadaService
{
    public function buscar(Receita $receita, int $limite = 6): Collection
    {
        $mesmaCategoria = Receita::where('status', 'publicada')
            ->where('id', '!=', $receita->id)
            ->where('categoria_id', $receita->categoria_id)
            ->take($limite)
            ->get();

        $idsIngredientes = $receita->ingredientes->pluck('ingrediente_id');

        $mesmosIngredientes = Receita::where('status', 'publicada')
            ->where('id', '!=', $receita->id)
            ->whereHas('ingredientes', function ($q) use ($idsIngredientes) {
                $q->whereIn('ingrediente_id', $idsIngredientes);
            })
            ->take($limite)
            ->get();

        $populares = Receita::where('status', 'publicada')
            ->where('id', '!=', $receita->id)
            ->orderByDesc('visualizacoes_total')
            ->take($limite)
            ->get();

        return $mesmaCategoria
            ->merge($mesmosIngredientes)
            ->merge($populares)
            ->unique('id')
            ->take($limite);
    }
}
```

Adicione na página da receita (`⚡mostrar.blade.php`, dentro do `@php`/`@endphp`):

```php
use App\Services\ReceitaRelacionadaService;

public function relacionadas()
{
    return app(ReceitaRelacionadaService::class)->buscar($this->receita);
}
```

E no HTML, antes de fechar `</article>`:

```php
<section class="border-t pt-6">
    <h2 class="text-lg font-bold mb-4">Você também pode gostar</h2>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        @foreach ($this->relacionadas() as $relacionada)
            <livewire:receitas.cartao-receita :receita="$relacionada" :key="'rel-'.$relacionada->id" />
        @endforeach
    </div>
</section>
```

**✅ Entregável da Fase 7:** botões de compartilhamento funcionam e registram o canal; seção "Você também pode gostar" aparece com receitas coerentes.

---

## Fase 8 — Área do usuário

### Passo 8.1 — Página de perfil

```bash
php artisan make:livewire perfil
```

📁 **Caminho:** `resources/views/pages/`
📄 **Arquivo:** `⚡perfil.blade.php`

```php
@php
use Illuminate\Support\Facades\Hash;

new class extends Livewire\Component {
    use \Livewire\WithFileUploads;

    public string $name = '';
    public string $bio = '';
    public $avatar = null;
    public string $novaSenha = '';

    public function mount(): void
    {
        $this->name = auth()->user()->name;
        $this->bio = (string) auth()->user()->bio;
    }

    public function salvar(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'novaSenha' => ['nullable', 'string', 'min:8'],
        ]);

        $dados = ['name' => $this->name, 'bio' => $this->bio];

        if ($this->avatar) {
            $dados['avatar_path'] = $this->avatar->store('avatares', 'public');
        }

        if ($this->novaSenha) {
            $dados['password'] = Hash::make($this->novaSenha);
        }

        auth()->user()->update($dados);
        $this->novaSenha = '';
        session()->flash('sucesso', 'Perfil atualizado!');
    }
}
@endphp

<x-layouts.app>
    <div class="max-w-lg mx-auto bg-white p-6 rounded-xl shadow space-y-4">
        <h1 class="text-xl font-bold">Meu perfil</h1>

        @if (session('sucesso'))
            <div class="p-3 bg-green-100 text-green-700 rounded-lg text-sm">{{ session('sucesso') }}</div>
        @endif

        <div>
            <label class="block text-sm font-medium">Nome</label>
            <input type="text" wire:model="name" class="mt-1 w-full rounded-lg border-gray-300">
        </div>

        <div>
            <label class="block text-sm font-medium">Biografia</label>
            <textarea wire:model="bio" rows="3" class="mt-1 w-full rounded-lg border-gray-300"></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium">Foto</label>
            <input type="file" wire:model="avatar" class="mt-1 w-full">
        </div>

        <div>
            <label class="block text-sm font-medium">Nova senha (opcional)</label>
            <input type="password" wire:model="novaSenha" class="mt-1 w-full rounded-lg border-gray-300">
        </div>

        <button wire:click="salvar" type="button"
                class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700">
            Salvar
        </button>
    </div>
</x-layouts.app>
```

### Passo 8.2 — Minhas receitas

```bash
php artisan make:livewire minhas-receitas
```

📁 **Caminho:** `resources/views/pages/`
📄 **Arquivo:** `⚡minhas-receitas.blade.php`

```php
@php
use App\Models\Receita;

new class extends Livewire\Component {
    public string $aba = 'publicadas';

    public function receitas()
    {
        return Receita::where('user_id', auth()->id())
            ->where('status', $this->aba === 'publicadas' ? 'publicada' : 'rascunho')
            ->latest()
            ->get();
    }

    public function excluir(int $id): void
    {
        $receita = Receita::where('user_id', auth()->id())->findOrFail($id);
        $receita->delete();
    }
}
@endphp

<x-layouts.app>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold">Minhas receitas</h1>
        <a href="{{ route('receitas.criar') }}" wire:navigate
           class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700">Nova receita</a>
    </div>

    <div class="flex gap-4 mb-4 border-b">
        <button wire:click="$set('aba', 'publicadas')"
                class="pb-2 {{ $aba === 'publicadas' ? 'border-b-2 border-orange-600 font-medium' : 'text-gray-500' }}">
            Publicadas
        </button>
        <button wire:click="$set('aba', 'rascunhos')"
                class="pb-2 {{ $aba === 'rascunhos' ? 'border-b-2 border-orange-600 font-medium' : 'text-gray-500' }}">
            Rascunhos
        </button>
    </div>

    <div class="space-y-2">
        @forelse ($this->receitas() as $receita)
            <div class="flex items-center justify-between bg-white p-4 rounded-lg shadow">
                <span>{{ $receita->titulo }}</span>
                <div class="flex gap-3 text-sm">
                    <a href="{{ route('receitas.mostrar', $receita) }}" wire:navigate class="text-gray-600">Ver</a>
                    <a href="{{ route('receitas.editar', $receita) }}" wire:navigate class="text-orange-600">Editar</a>
                    <button wire:click="excluir({{ $receita->id }})" wire:confirm="Excluir esta receita?"
                            class="text-red-600">Excluir</button>
                </div>
            </div>
        @empty
            <p class="text-gray-500">Nenhuma receita aqui ainda.</p>
        @endforelse
    </div>
</x-layouts.app>
```

### Passo 8.3 — Página de editar receita

📁 **Caminho:** `resources/views/pages/receitas/`
📄 **Arquivo:** `⚡editar.blade.php`

```php
@php
use App\Models\Receita;

new class extends Livewire\Component {
    public Receita $receita;

    public function mount(Receita $receita): void
    {
        abort_unless($receita->user_id === auth()->id(), 403);
        $this->receita = $receita;
    }
}
@endphp

<x-layouts.app>
    <livewire:receitas.formulario-receita :receita="$receita" />
</x-layouts.app>
```

### Passo 8.4 — Rotas da Fase 8

📁 **Caminho:** `routes/`
📄 **Arquivo:** `web.php` (adicione dentro do grupo `auth`)

```php
Route::middleware('auth')->group(function () {
    Route::livewire('/receitas/criar', 'pages::receitas.criar')->name('receitas.criar');
    Route::livewire('/receitas/{receita:slug}/editar', 'pages::receitas.editar')->name('receitas.editar');
    Route::livewire('/minhas-receitas', 'pages::minhas-receitas')->name('minhas-receitas');
    Route::livewire('/perfil', 'pages::perfil')->name('perfil');
});
```

**✅ Entregável da Fase 8:** usuário edita o próprio perfil, vê suas receitas publicadas/rascunhos, edita e exclui — sem acessar receitas de outros usuários.

---

## Fase 9 — Painel administrativo

### Passo 9.1 — Dashboard

```bash
php artisan make:livewire admin.dashboard
```

📁 **Caminho:** `resources/views/pages/admin/`
📄 **Arquivo:** `⚡dashboard.blade.php`

```php
@php
use App\Models\Comentario;
use App\Models\Avaliacao;
use App\Models\Receita;
use App\Models\User;

new class extends Livewire\Component {
    public function totais(): array
    {
        return [
            'usuarios' => User::count(),
            'receitas' => Receita::count(),
            'comentarios' => Comentario::count(),
            'avaliacoes' => Avaliacao::count(),
        ];
    }

    public function populares()
    {
        return Receita::orderByDesc('visualizacoes_total')->take(5)->get();
    }
}
@endphp

<x-layouts.admin>
    <h1 class="text-xl font-bold mb-6">Dashboard</h1>

    <div class="grid grid-cols-4 gap-4 mb-8">
        @foreach ($this->totais() as $label => $valor)
            <div class="bg-white p-4 rounded-xl shadow">
                <p class="text-sm text-gray-500 capitalize">{{ $label }}</p>
                <p class="text-2xl font-bold">{{ $valor }}</p>
            </div>
        @endforeach
    </div>

    <h2 class="font-bold mb-3">Receitas mais populares</h2>
    <ul class="bg-white rounded-xl shadow divide-y">
        @foreach ($this->populares() as $receita)
            <li class="p-3 flex justify-between">
                <span>{{ $receita->titulo }}</span>
                <span class="text-gray-500 text-sm">{{ $receita->visualizacoes_total }} views</span>
            </li>
        @endforeach
    </ul>
</x-layouts.admin>
```

### Passo 9.2 — CRUD de categorias (admin)

```bash
php artisan make:livewire admin.categorias
```

📁 **Caminho:** `resources/views/pages/admin/`
📄 **Arquivo:** `⚡categorias.blade.php`

```php
@php
use App\Models\Categoria;
use Illuminate\Support\Str;

new class extends Livewire\Component {
    public string $nome = '';
    public ?int $editandoId = null;

    public function categorias()
    {
        return Categoria::orderBy('nome')->get();
    }

    public function salvar(): void
    {
        $this->validate(['nome' => ['required', 'string', 'max:255']]);

        if ($this->editandoId) {
            Categoria::find($this->editandoId)->update([
                'nome' => $this->nome,
                'slug' => Str::slug($this->nome),
            ]);
        } else {
            Categoria::create(['nome' => $this->nome, 'slug' => Str::slug($this->nome)]);
        }

        $this->reset(['nome', 'editandoId']);
    }

    public function editar(int $id): void
    {
        $categoria = Categoria::findOrFail($id);
        $this->editandoId = $id;
        $this->nome = $categoria->nome;
    }

    public function excluir(int $id): void
    {
        Categoria::findOrFail($id)->delete();
    }
}
@endphp

<x-layouts.admin>
    <h1 class="text-xl font-bold mb-6">Categorias</h1>

    <form wire:submit="salvar" class="flex gap-2 mb-6">
        <input type="text" wire:model="nome" placeholder="Nome da categoria"
               class="flex-1 rounded-lg border-gray-300">
        <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded-lg">
            {{ $editandoId ? 'Atualizar' : 'Adicionar' }}
        </button>
    </form>

    <ul class="bg-white rounded-xl shadow divide-y">
        @foreach ($this->categorias() as $categoria)
            <li class="p-3 flex justify-between items-center">
                <span>{{ $categoria->nome }}</span>
                <div class="flex gap-3 text-sm">
                    <button wire:click="editar({{ $categoria->id }})" class="text-orange-600">Editar</button>
                    <button wire:click="excluir({{ $categoria->id }})" wire:confirm="Excluir categoria?"
                            class="text-red-600">Excluir</button>
                </div>
            </li>
        @endforeach
    </ul>
</x-layouts.admin>
```

### Passo 9.3 — Usuários (admin)

```bash
php artisan make:livewire admin.usuarios
```

📁 **Caminho:** `resources/views/pages/admin/`
📄 **Arquivo:** `⚡usuarios.blade.php`

```php
@php
use App\Models\User;

new class extends Livewire\Component {
    public function usuarios()
    {
        return User::orderBy('name')->paginate(15);
    }

    public function promover(int $id): void
    {
        User::findOrFail($id)->update(['is_admin' => true]);
    }

    public function remover(int $id): void
    {
        User::findOrFail($id)->delete();
    }
}
@endphp

<x-layouts.admin>
    <h1 class="text-xl font-bold mb-6">Usuários</h1>

    <table class="w-full bg-white rounded-xl shadow">
        <thead>
            <tr class="text-left text-sm text-gray-500 border-b">
                <th class="p-3">Nome</th>
                <th class="p-3">E-mail</th>
                <th class="p-3">Admin</th>
                <th class="p-3">Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($this->usuarios() as $usuario)
                <tr class="border-b">
                    <td class="p-3">{{ $usuario->name }}</td>
                    <td class="p-3">{{ $usuario->email }}</td>
                    <td class="p-3">{{ $usuario->is_admin ? 'Sim' : 'Não' }}</td>
                    <td class="p-3 flex gap-3 text-sm">
                        @unless ($usuario->is_admin)
                            <button wire:click="promover({{ $usuario->id }})" class="text-orange-600">Promover</button>
                        @endunless
                        <button wire:click="remover({{ $usuario->id }})" wire:confirm="Remover usuário?"
                                class="text-red-600">Remover</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">{{ $this->usuarios()->links() }}</div>
</x-layouts.admin>
```

### Passo 9.4 — Moderação de comentários (admin)

```bash
php artisan make:livewire admin.comentarios
```

📁 **Caminho:** `resources/views/pages/admin/`
📄 **Arquivo:** `⚡comentarios.blade.php`

```php
@php
use App\Models\Comentario;

new class extends Livewire\Component {
    public function comentarios()
    {
        return Comentario::with(['user', 'receita'])->latest()->paginate(20);
    }

    public function ocultar(int $id): void
    {
        Comentario::findOrFail($id)->update(['status' => 'oculto']);
    }

    public function excluir(int $id): void
    {
        Comentario::findOrFail($id)->delete();
    }
}
@endphp

<x-layouts.admin>
    <h1 class="text-xl font-bold mb-6">Comentários</h1>

    <ul class="bg-white rounded-xl shadow divide-y">
        @foreach ($this->comentarios() as $comentario)
            <li class="p-3">
                <p class="text-sm text-gray-500">
                    {{ $comentario->user->name }} em <strong>{{ $comentario->receita->titulo }}</strong>
                    — {{ $comentario->status }}
                </p>
                <p class="text-gray-700">{{ $comentario->conteudo }}</p>
                <div class="flex gap-3 text-sm mt-1">
                    <button wire:click="ocultar({{ $comentario->id }})" class="text-orange-600">Ocultar</button>
                    <button wire:click="excluir({{ $comentario->id }})" wire:confirm="Excluir comentário?"
                            class="text-red-600">Excluir</button>
                </div>
            </li>
        @endforeach
    </ul>

    <div class="mt-4">{{ $this->comentarios()->links() }}</div>
</x-layouts.admin>
```

> **Receitas (admin)** segue o mesmo padrão CRUD do passo 9.2/9.3 combinado com o formulário da Fase 2 — reaproveite `⚡formulario-receita.blade.php` dentro de `resources/views/pages/admin/⚡receitas.blade.php`.

### Passo 9.5 — Rotas do admin

📁 **Caminho:** `routes/`
📄 **Arquivo:** `admin.php` (crie este arquivo)

```php
<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::livewire('/', 'pages::admin.dashboard')->name('dashboard');
    Route::livewire('/categorias', 'pages::admin.categorias')->name('categorias');
    Route::livewire('/usuarios', 'pages::admin.usuarios')->name('usuarios');
    Route::livewire('/comentarios', 'pages::admin.comentarios')->name('comentarios');
});
```

Registre em `routes/web.php`:

```php
require __DIR__.'/admin.php';
```

**✅ Entregável da Fase 9:** um usuário com `is_admin = true` acessa `/admin`, vê os totais do dashboard e consegue gerenciar categorias, usuários e comentários.


---

## Fase 10 — SEO

### Passo 10.1 — Meta tags e Open Graph dinâmicos

📁 **Caminho:** `resources/views/layouts/`
📄 **Arquivo:** `app.blade.php` (edite o `<head>`)

```php
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tituloPagina ?? 'Cantinho das Receitas' }}</title>
    <meta name="description" content="{{ $descricaoPagina ?? 'Encontre e compartilhe as melhores receitas.' }}">

    <meta property="og:title" content="{{ $tituloPagina ?? 'Cantinho das Receitas' }}">
    <meta property="og:description" content="{{ $descricaoPagina ?? '' }}">
    <meta property="og:image" content="{{ $imagemPagina ?? asset('images/og-default.jpg') }}">
    <meta property="og:type" content="website">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    {{ $head ?? '' }}
</head>
```

Na página da receita (`⚡mostrar.blade.php`), passe essas variáveis para o layout:

```php
<x-layouts.app
    :titulo-pagina="$receita->titulo.' — Cantinho das Receitas'"
    :descricao-pagina="Str::limit($receita->descricao, 155)"
    :imagem-pagina="$receita->foto_principal_path ? Storage::url($receita->foto_principal_path) : null"
>
```

### Passo 10.2 — JSON-LD Schema Recipe

Adicione dentro de `⚡mostrar.blade.php`, no slot `$head` (ou diretamente antes de `</article>` como um `<script>`):

```php
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org/',
    '@type' => 'Recipe',
    'name' => $receita->titulo,
    'image' => $receita->foto_principal_path ? Storage::url($receita->foto_principal_path) : null,
    'description' => $receita->descricao,
    'prepTime' => 'PT'.$receita->tempo_preparo_min.'M',
    'cookTime' => $receita->tempo_cozimento_min ? 'PT'.$receita->tempo_cozimento_min.'M' : null,
    'recipeYield' => $receita->porcoes.' porções',
    'recipeIngredient' => $receita->ingredientes->map(fn ($i) => "{$i->quantidade} {$i->unidade} {$i->ingrediente->nome}")->toArray(),
    'aggregateRating' => $receita->total_avaliacoes > 0 ? [
        '@type' => 'AggregateRating',
        'ratingValue' => $receita->nota_media,
        'ratingCount' => $receita->total_avaliacoes,
    ] : null,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
```

### Passo 10.3 — Sitemap

```bash
php artisan make:controller SitemapController
```

📁 **Caminho:** `app/Http/Controllers/`
📄 **Arquivo:** `SitemapController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Receita;
use Illuminate\Support\Facades\Response;

class SitemapController extends Controller
{
    public function index()
    {
        $receitas = Receita::where('status', 'publicada')->get();

        $xml = view('sitemap', compact('receitas'))->render();

        return Response::make($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
```

📁 **Caminho:** `resources/views/`
📄 **Arquivo:** `sitemap.blade.php`

```php
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ url('/') }}</loc>
    </url>
    <url>
        <loc>{{ route('receitas.listar') }}</loc>
    </url>
    @foreach ($receitas as $receita)
    <url>
        <loc>{{ route('receitas.mostrar', $receita) }}</loc>
        <lastmod>{{ $receita->updated_at->toAtomString() }}</lastmod>
    </url>
    @endforeach
</urlset>
```

📁 **Caminho:** `routes/`
📄 **Arquivo:** `web.php` (adicione)

```php
use App\Http\Controllers\SitemapController;

Route::get('/sitemap.xml', [SitemapController::class, 'index']);
```

### Passo 10.4 — robots.txt

📁 **Caminho:** `public/`
📄 **Arquivo:** `robots.txt`

```
User-agent: *
Disallow: /admin
Disallow: /minhas-receitas
Disallow: /perfil
Allow: /

Sitemap: /sitemap.xml
```

**✅ Entregável da Fase 10:** validar a receita no [Google Rich Results Test](https://search.google.com/test/rich-results) sem erros; `/sitemap.xml` lista as receitas publicadas.

---

## Fase 11 — Performance e cache

### Passo 11.1 — Cache na home

Em `⚡inicio.blade.php`, envolva as consultas pesadas:

```php
public function maisAcessadas()
{
    return \Illuminate\Support\Facades\Cache::remember('home:mais-acessadas', 600, function () {
        return Receita::where('status', 'publicada')->orderByDesc('visualizacoes_total')->take(4)->get();
    });
}
```

### Passo 11.2 — Invalidar cache quando a receita muda

📁 **Caminho:** `app/Observers/`
📄 **Arquivo:** `ReceitaObserver.php`

```php
<?php

namespace App\Observers;

use App\Models\Receita;
use Illuminate\Support\Facades\Cache;

class ReceitaObserver
{
    public function saved(Receita $receita): void
    {
        Cache::forget('home:mais-acessadas');
    }

    public function deleted(Receita $receita): void
    {
        Cache::forget('home:mais-acessadas');
    }
}
```

Registre em `AppServiceProvider.php` (junto do `AvaliacaoObserver`):

```php
use App\Models\Receita;
use App\Observers\ReceitaObserver;

Receita::observe(ReceitaObserver::class);
```

### Passo 11.3 — Compressão de imagem no upload

```bash
composer require intervention/image
```

📁 **Caminho:** `app/Services/`
📄 **Arquivo:** `UploadImagemService.php`

```php
<?php

namespace App\Services;

use Intervention\Image\Laravel\Facades\Image;

class UploadImagemService
{
    public function salvarComCompressao(\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $arquivo, string $pasta): string
    {
        $imagem = Image::read($arquivo->getRealPath())
            ->scaleDown(width: 1200)
            ->toWebp(quality: 80);

        $nomeArquivo = $pasta.'/'.uniqid().'.webp';
        \Illuminate\Support\Facades\Storage::disk('public')->put($nomeArquivo, (string) $imagem);

        return $nomeArquivo;
    }
}
```

Use no lugar de `$this->foto_principal->store(...)` dentro do `⚡formulario-receita.blade.php`:

```php
$dados['foto_principal_path'] = app(\App\Services\UploadImagemService::class)
    ->salvarComCompressao($this->foto_principal, 'receitas');
```

**✅ Entregável da Fase 11:** imagens enviadas ficam menores (webp, largura máxima 1200px) e a home não bate no banco a cada carregamento dentro da janela de 10 minutos de cache.

---

## Fase 12 — Segurança

### Passo 12.1 — Rate limit em ações sensíveis

📁 **Caminho:** `routes/`
📄 **Arquivo:** `auth.php` (edite)

```php
Route::middleware(['guest', 'throttle:5,1'])->group(function () {
    Route::livewire('/cadastro', 'pages::auth.cadastro')->name('cadastro');
    Route::livewire('/login', 'pages::auth.login')->name('login');
});
```

Para comentários e avaliações (dentro dos métodos dos componentes), use o `RateLimiter` diretamente:

```php
use Illuminate\Support\Facades\RateLimiter;

public function comentar(): void
{
    $chave = 'comentar:'.auth()->id();

    if (RateLimiter::tooManyAttempts($chave, 5)) {
        $this->addError('novoComentario', 'Muitos comentários em pouco tempo. Aguarde um instante.');
        return;
    }

    RateLimiter::hit($chave, 60);

    // ... resto do método comentar()
}
```

### Passo 12.2 — Regras de validação de upload

Já cobertas em cada formulário (`['image', 'max:4096']` / `max:2048`), mas reforce o tipo aceito:

```php
'foto_principal' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
```

### Passo 12.3 — Nunca usar `{!! !!}` com dado de usuário

Confira nos componentes de comentário/perfil que **nenhum** campo digitado por usuário (bio, comentário, título) é impresso com `{!! !!}` — sempre `{{ }}`. O único `{!! !!}` deste projeto é o JSON-LD (Fase 10), que é gerado pelo backend, não digitado pelo usuário.

### Passo 12.4 — CSRF

Já é automático em todo formulário Livewire e em formulários Blade com `@csrf` (como o de logout, Passo 1.8). Nenhuma ação extra necessária — apenas não substitua por `fetch()` manual sem token.

**✅ Entregável da Fase 12:** tentar logar 6 vezes seguidas erradas bloqueia temporariamente; upload de um arquivo `.php` disfarçado de imagem é rejeitado.

---

## Fase 13 — Deploy

### Passo 13.1 — Variáveis de produção

📁 **Caminho:** raiz do projeto (no servidor)
📄 **Arquivo:** `.env`

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://cantinhodasreceitas.com.br

DB_CONNECTION=pgsql
DB_HOST=seu-host-postgres
DB_PORT=5432
DB_DATABASE=cantinho_receitas
DB_USERNAME=usuario_producao
DB_PASSWORD=senha_forte

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

### Passo 13.2 — Comandos de deploy

```bash
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

### Passo 13.3 — Worker de fila

```bash
php artisan queue:work --daemon
```

(em produção, rode isso sob um supervisor de processos, como Supervisor ou o daemon do seu provedor de hospedagem).

**✅ Entregável da Fase 13:** site acessível publicamente via HTTPS, com fila processando em segundo plano e migrations aplicadas.

---

## Fase 14 — Backlog (funcionalidades futuras)

Sem código nesta fase — implemente somente depois que 0–13 estiverem estáveis e testadas manualmente:

- Receitas em vídeo (upload próprio)
- Ranking de cozinheiros
- Receitas Premium (paywall)
- Lista de compras (agregando ingredientes de várias receitas favoritas)
- Planejamento semanal de cardápio
- Modo escuro
- IA para sugerir receitas a partir de ingredientes disponíveis
- IA para converter medidas culinárias
- Impressão da receita em PDF
- Receitas por sazonalidade
- API pública (Laravel Sanctum + documentação OpenAPI)

---

## Checklist geral

- [ ] Fase 0 — Ambiente rodando
- [ ] Fase 1 — Auth + layout base
- [ ] Fase 2 — CRUD básico de receitas
- [ ] Fase 3 — Home + busca + filtros
- [ ] Fase 4 — Página da receita completa
- [ ] Fase 5 — Calculadora de porções
- [ ] Fase 6 — Curtidas, favoritos, avaliações, comentários
- [ ] Fase 7 — Compartilhamento + relacionadas
- [ ] Fase 8 — Área do usuário
- [ ] Fase 9 — Painel administrativo
- [ ] Fase 10 — SEO
- [ ] Fase 11 — Performance e cache
- [ ] Fase 12 — Segurança
- [ ] Fase 13 — Deploy
- [ ] Fase 14 — Backlog de funcionalidades futuras

---

**Dica final:** copie cada bloco de código exatamente no caminho indicado, rode o comando `php artisan` sugerido quando houver, e teste manualmente o "✅ Entregável" antes de seguir para o próximo passo. Se travar em algum trecho, volte e confira se o nome do arquivo/pasta bate exatamente com o que está aqui — no Livewire SFC, o nome do arquivo *é* o nome do componente.
