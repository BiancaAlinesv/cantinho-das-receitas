<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $description ?? trim($__env->yieldContent('description', 'Receitas afetivas, simples e deliciosas para todos os dias.')) }}">
    <meta name="robots" content="index,follow">
    <meta property="og:site_name" content="Cantinho das Receitas">
    <meta property="og:title" content="{{ $title ?? trim($__env->yieldContent('title', 'Cantinho das Receitas')) }}">
    <meta property="og:description" content="{{ $description ?? trim($__env->yieldContent('description', 'Receitas afetivas, simples e deliciosas para todos os dias.')) }}">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    @if(!empty($image))<meta property="og:image" content="{{ $image }}">@endif
    <meta name="twitter:card" content="summary_large_image">
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
                <a href="{{ route('inicio') }}" class="active">Início</a>
                <a href="{{ route('receitas.listar') }}">Receitas</a>
                <a href="#categorias">Categorias</a>
                <a href="#sobre">Sobre nós</a>
            </nav>
            <div class="header-actions">
                <a href="{{ route('receitas.listar') }}" class="icon-button" aria-label="Buscar">⌕</a>
                @auth
                    <a href="{{ route('minhas-receitas') }}" class="button button-small">Minha conta</a>
                @else
                    <a href="{{ route('login') }}" class="button button-small button-outline">Entrar</a>
                @endauth
            </div>
        </div>
    </header>

    <main>{{ $slot ?? '' }}@yield('content')</main>

    <footer class="site-footer" id="sobre">
        <div class="container footer-inner">
            <div><a href="{{ route('inicio') }}" class="brand"><span class="brand-mark">✦</span><span>Cantinho <i>das</i> Receitas</span></a><p>Comida gostosa, memória afetiva e um cantinho para chamar de seu.</p></div>
            <p class="footer-copy">© {{ date('Y') }} Cantinho das Receitas</p>
        </div>
    </footer>
    @livewireScripts
</body>
</html>
