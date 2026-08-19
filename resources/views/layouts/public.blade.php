<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#FBF8F3">
    <meta name="description" content="{{ $description ?? trim($__env->yieldContent('description', 'Receitas afetivas, simples e deliciosas para todos os dias.')) }}">
    <meta name="robots" content="index,follow">
    <meta property="og:site_name" content="Cantinho das Receitas">
    <meta property="og:url" content="{{ $canonical ?? url()->current() }}">
    <meta property="og:title" content="{{ $title ?? trim($__env->yieldContent('title', 'Cantinho das Receitas')) }}">
    <meta property="og:description" content="{{ $description ?? trim($__env->yieldContent('description', 'Receitas afetivas, simples e deliciosas para todos os dias.')) }}">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:locale" content="pt_BR">
    @if(!empty($image))<meta property="og:image" content="{{ $image }}">@endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? trim($__env->yieldContent('title', 'Cantinho das Receitas')) }}">
    <meta name="twitter:description" content="{{ $description ?? trim($__env->yieldContent('description', 'Receitas afetivas, simples e deliciosas para todos os dias.')) }}">
    @if(!empty($image))<meta name="twitter:image" content="{{ $image }}">@endif
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">
    <title>{{ $title ?? trim($__env->yieldContent('title', 'Cantinho das Receitas')) }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    {!! $head ?? trim($__env->yieldContent('head')) !!}
</head>
<body class="site-shell">
    <header class="site-header">
        <div class="container header-inner">
            <a href="{{ route('inicio') }}" class="brand" aria-label="Cantinho das Receitas - início">
                <span class="brand-mark">✦</span>
                <span>Cantinho <i>das</i> Receitas</span>
            </a>
            <nav class="main-nav" aria-label="Navegação principal">
                <a href="{{ route('inicio') }}" class="{{ request()->routeIs('inicio') ? 'active' : '' }}" @if(request()->routeIs('inicio')) aria-current="page" @endif>Início</a>
                <a href="{{ route('receitas.listar') }}" class="{{ request()->routeIs('receitas.*') ? 'active' : '' }}" @if(request()->routeIs('receitas.*')) aria-current="page" @endif>Receitas</a>
                <a href="{{ route('inicio') }}#categorias">Categorias</a>
                <a href="{{ route('inicio') }}#sobre">Sobre o Cantinho</a>
            </nav>
            <details class="mobile-nav">
                <summary aria-label="Abrir menu de navegação"><span aria-hidden="true">☰</span></summary>
                <nav aria-label="Navegação mobile">
                    <a href="{{ route('inicio') }}" class="{{ request()->routeIs('inicio') ? 'active' : '' }}" @if(request()->routeIs('inicio')) aria-current="page" @endif>Início</a>
                    <a href="{{ route('receitas.listar') }}" class="{{ request()->routeIs('receitas.*') ? 'active' : '' }}" @if(request()->routeIs('receitas.*')) aria-current="page" @endif>Receitas</a>
                    <a href="{{ route('inicio') }}#categorias">Categorias</a>
                    <a href="{{ route('inicio') }}#sobre">Sobre o Cantinho</a>
                    @auth
                        <a href="{{ route('favoritos') }}" class="{{ request()->routeIs('favoritos') ? 'active' : '' }}" @if(request()->routeIs('favoritos')) aria-current="page" @endif>Favoritos</a>
                        <a href="{{ route('receitas.guardar') }}" class="{{ request()->routeIs('receitas.guardar') ? 'active' : '' }}" @if(request()->routeIs('receitas.guardar')) aria-current="page" @endif>Guardar receita</a>
                    @endauth
                </nav>
            </details>
            <div class="header-actions">
                <a href="{{ route('receitas.listar') }}" class="icon-button {{ request()->routeIs('receitas.listar') ? 'active' : '' }}" aria-label="Buscar receitas">⌕</a>
                @auth
                    <a href="{{ route('favoritos') }}" class="header-save-link {{ request()->routeIs('favoritos') ? 'active' : '' }}" @if(request()->routeIs('favoritos')) aria-current="page" @endif>Favoritos</a>
                    <a href="{{ route('receitas.guardar') }}" class="header-save-link {{ request()->routeIs('receitas.guardar') ? 'active' : '' }}" @if(request()->routeIs('receitas.guardar')) aria-current="page" @endif>Guardar</a>
                    <a href="{{ route('minhas-receitas') }}" class="button button-small">Minha conta</a>
                @else
                    <a href="{{ route('login') }}" class="button button-small button-outline">Entrar</a>
                @endauth
            </div>
        </div>
    </header>

    <main>{{ $slot ?? '' }}@yield('content')</main>

    <footer class="site-footer" id="rodape">
        <div class="container footer-inner">
            <div><a href="{{ route('inicio') }}" class="brand"><span class="brand-mark">✦</span><span>Cantinho <i>das</i> Receitas</span></a><p>Comida gostosa, memória afetiva e um cantinho para chamar de seu.</p></div>
            <div class="footer-signature"><span>Feito com carinho por</span><a href="https://biancanegretti.com.br/" target="_blank" rel="noopener noreferrer">Bianca Negretti <span aria-hidden="true">↗</span></a><small>© {{ date('Y') }} Cantinho das Receitas</small></div>
        </div>
    </footer>
    @livewireScripts
</body>
</html>
