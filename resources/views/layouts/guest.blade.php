<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('imagens/fiveicom.png') }}">
    <title>{{ $title ?? 'Acessar — Cantinho das Receitas' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-shell">
    <main class="auth-layout">
        <section class="auth-showcase" aria-label="Cantinho das Receitas">
            <a href="{{ route('inicio') }}" class="brand auth-brand" wire:navigate>
                <span class="brand-mark"><img src="{{ asset('imagens/logotrasparente.png') }}" alt=""></span>
                <span>Cantinho <i>das</i> Receitas</span>
            </a>
            <div class="auth-showcase-copy">
                <span class="eyebrow">Um lugar para guardar sabores</span>
                <h1>Receitas que têm gosto de <em>casa.</em></h1>
                <p>Entre para salvar suas receitas favoritas, compartilhar suas criações e fazer parte da nossa cozinha.</p>
            </div>
            <div class="auth-showcase-note"><span>✦</span><span>Receitas afetivas para todos os dias.</span></div>
        </section>
        <section class="auth-content">
            <div class="auth-mobile-brand"><a href="{{ route('inicio') }}" class="brand" wire:navigate><span class="brand-mark"><img src="{{ asset('imagens/logotrasparente.png') }}" alt=""></span><span>Cantinho <i>das</i> Receitas</span></a></div>
            {{ $slot }}
        </section>
    </main>
</body>
</html>
