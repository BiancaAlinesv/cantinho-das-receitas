@extends('layouts.public')

@section('title', 'Receitas — Cantinho das Receitas')

@section('content')
<section class="listing-header"><div class="container"><span class="eyebrow">Nosso caderno</span><h1>Todas as receitas</h1><p>Escolha uma receita e deixe a cozinha cuidar do resto.</p><form action="{{ route('receitas.listar') }}" method="GET" class="search-form listing-search"><span>⌕</span><input name="busca" value="{{ $term }}" type="search" placeholder="Buscar por nome ou categoria..." aria-label="Buscar receitas"><button type="submit">Buscar</button></form></div></section>
<section class="section container"><div class="section-heading"><div><span class="eyebrow">{{ count($recipes) }} resultados</span><h2>{{ $term ? 'Resultados para “'.$term.'”' : 'Inspire-se hoje' }}</h2></div><a class="text-link" href="{{ route('inicio') }}">← Voltar ao início</a></div>@if (count($recipes))<div class="recipe-grid">@foreach ($recipes as $recipe)<x-recipe-card :recipe="$recipe" />@endforeach</div>@else<div class="empty-state"><span>⌁</span><h2>Nada encontrado por aqui</h2><p>Tente buscar outro ingrediente ou veja todas as nossas receitas.</p><a class="button" href="{{ route('receitas.listar') }}">Ver todas</a></div>@endif</section>
@endsection
