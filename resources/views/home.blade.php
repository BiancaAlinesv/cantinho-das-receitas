@extends('layouts.public')

@section('title', 'Cantinho das Receitas — receitas que abraçam')

@section('content')
@php($recipes = \App\Support\RecipeCatalog::all())
@php($categories = \App\Support\RecipeCatalog::categories())
<section class="hero-section">
    <div class="container hero-grid">
        <div class="hero-copy"><span class="eyebrow">Receitas que abraçam</span><h1>O sabor de casa,<br><em>feito por você.</em></h1><p>Receitas simples, afetivas e deliciosas para transformar qualquer dia em uma ocasião especial.</p>
            <form action="{{ route('receitas.listar') }}" method="GET" class="search-form"><span>⌕</span><input name="busca" type="search" placeholder="O que você quer cozinhar hoje?" aria-label="Buscar receitas"><button type="submit">Buscar receita</button></form>
            <div class="hero-note"><span class="avatar-stack"><i>♥</i><i>✦</i><i>●</i></span><span>Mais de <strong>2.000 pessoas</strong> cozinham com a gente</span></div>
        </div>
        <div class="hero-art"><div class="hero-circle"></div><img src="https://images.unsplash.com/photo-1556911220-e15b29be8c8f?auto=format&fit=crop&w=1100&q=85" alt="Pessoa preparando uma receita em uma cozinha iluminada" class="hero-photo"><div class="hero-badge"><span>♥</span><strong>Feito com amor</strong><small>e uma pitada de carinho</small></div></div>
    </div>
</section>

<section class="section container"><div class="section-heading"><div><span class="eyebrow">Para começar</span><h2>Escolha uma categoria</h2></div><a class="text-link" href="{{ route('receitas.listar') }}">Ver todas <span>→</span></a></div><div class="category-grid" id="categorias">@foreach ($categories as $category)<a href="{{ route('receitas.listar', ['busca' => $category['name']]) }}" class="category-card"><span class="category-icon">{{ $category['icon'] }}</span><strong>{{ $category['name'] }}</strong><small>{{ $category['count'] }}</small></a>@endforeach</div></section>

<section class="section container"><div class="section-heading"><div><span class="eyebrow">Acabaram de sair do forno</span><h2>Receitas recentes</h2></div><a class="text-link" href="{{ route('receitas.listar') }}">Ver todas <span>→</span></a></div><div class="recipe-grid">@foreach (array_slice($recipes, 0, 4) as $recipe)<x-recipe-card :recipe="$recipe" />@endforeach</div></section>

<section class="feature-banner container"><div><span class="eyebrow">Receita da semana</span><h2>Um bolo quentinho<br><em>muda tudo.</em></h2><p>Descubra o segredo do bolo de cenoura mais fofinho da nossa cozinha.</p><a href="{{ route('receitas.listar', ['busca' => 'Bolo de cenoura']) }}" class="button">Ver receita <span>→</span></a></div><img src="https://images.unsplash.com/photo-1606890737304-57a1ca8a5b62?auto=format&fit=crop&w=900&q=85" alt="Bolo de chocolate decorado"></section>
@endsection
