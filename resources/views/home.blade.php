@extends('layouts.public')

@section('title', 'Cantinho das Receitas — receitas que abraçam')

@section('content')
@php($recipes = \App\Support\RecipeCatalog::all())
@php($categories = \App\Support\RecipeCatalog::categories())
<section class="hero-section">
    <div class="container hero-grid">
        <div class="hero-copy"><span class="eyebrow">Receitas que abraçam</span><h1>O sabor de casa,<br><em>feito por você.</em></h1><p>Receitas simples, afetivas e deliciosas para transformar qualquer dia em uma ocasião especial.</p>
            <form action="{{ route('receitas.listar') }}" method="GET" class="search-form"><span>⌕</span><input name="busca" type="search" placeholder="O que você quer cozinhar hoje?" aria-label="Buscar receitas"><button type="submit">Buscar receita</button></form>
            <div class="hero-note"><span class="avatar-stack" aria-hidden="true"><i>♥</i><i>✦</i><i>●</i></span><span>Suas receitas favoritas, todas em um só cantinho.</span></div>
        </div>
        <div class="hero-art"><div class="hero-circle"></div><img src="https://images.unsplash.com/photo-1556911220-e15b29be8c8f?auto=format&fit=crop&w=1100&q=85" alt="Pessoa preparando uma receita em uma cozinha iluminada" class="hero-photo"><div class="hero-badge"><span>♥</span><strong>Feito com amor</strong><small>e uma pitada de carinho</small></div></div>
    </div>
</section>

<section class="section container"><div class="section-heading"><div><span class="eyebrow">Para começar</span><h2>Escolha uma categoria</h2></div><a class="text-link" href="{{ route('receitas.listar') }}">Ver todas <span>→</span></a></div><div class="category-grid" id="categorias">@foreach ($categories as $category)<a href="{{ route('receitas.listar', ['busca' => $category['name']]) }}" class="category-card"><span class="category-icon" aria-hidden="true">{{ $category['icon'] }}</span><strong>{{ $category['name'] }}</strong><small>{{ $category['count'] }} {{ $category['count'] === 1 ? 'receita' : 'receitas' }}</small></a>@endforeach</div></section>

<section class="section container"><div class="section-heading"><div><span class="eyebrow">Acabaram de sair do forno</span><h2>Receitas recentes</h2></div><a class="text-link" href="{{ route('receitas.listar') }}">Ver todas <span>→</span></a></div><div class="recipe-grid">@foreach (array_slice($recipes, 0, 4) as $recipe)<x-recipe-card :recipe="$recipe" />@endforeach</div></section>

<section class="feature-banner container"><div><span class="eyebrow">Receita da semana</span><h2>Um bolo quentinho<br><em>muda tudo.</em></h2><p>Descubra o segredo do bolo de cenoura mais fofinho da nossa cozinha.</p><a href="{{ route('receitas.listar', ['busca' => 'Bolo de cenoura']) }}" class="button">Ver receita <span>→</span></a></div><img src="https://images.unsplash.com/photo-1606890737304-57a1ca8a5b62?auto=format&fit=crop&w=900&q=85" alt="Bolo de chocolate decorado"></section>

<section class="about-section container" id="sobre"><div class="about-card"><div class="about-intro"><span class="eyebrow">Sobre nós</span><h2>Um cantinho para<br><em>guardar sabores.</em></h2><span class="about-mark" aria-hidden="true">✦</span></div><div class="about-copy"><p>O <strong>Cantinho das Receitas</strong> nasceu de uma necessidade bem simples: ter um lugar para guardar todas aquelas receitas que a gente encontra, gosta e promete fazer de novo.</p><p>Antes, minhas receitas favoritas ficavam espalhadas por todos os lados: algumas em prints no celular, outras salvas no Instagram ou no TikTok, anotações no Keep e até receitas que, com o tempo, eu acabava perdendo.</p><p>A ideia inicial era criar apenas um e-book pessoal para organizar tudo isso. Mas, aos poucos, percebi que poderia transformar essa ideia em algo muito mais completo.</p><p>Assim nasceu o <strong>Cantinho das Receitas</strong>: um espaço para reunir receitas queridas, organizar descobertas e facilitar aquele momento em que surge a pergunta:</p><blockquote>“O que vamos fazer de gostoso hoje?”</blockquote><p>Mais do que um livro de receitas digital, quero que este seja um cantinho para guardar sabores, descobertas e aquelas receitas que merecem ser feitas muitas outras vezes. <span class="about-heart" aria-hidden="true">🍲 ❤️</span></p></div></div></section>
@endsection
