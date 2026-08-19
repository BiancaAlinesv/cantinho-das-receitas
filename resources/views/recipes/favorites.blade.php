@extends('layouts.public')

@section('title', 'Minhas favoritas — Cantinho das Receitas')

@section('content')
<section class="account-header"><div class="container"><span class="eyebrow">Sua coleção</span><h1>Minhas favoritas</h1><p>Receitas que você quer encontrar de novo.</p></div></section>
<section class="section container">
    @if($favoritos->isNotEmpty())
        <div class="recipe-grid">@foreach($favoritos as $receita)<x-recipe-card :recipe="$receita" />@endforeach</div>
    @else
        <div class="empty-state"><span aria-hidden="true">♡</span><h2>Seu cantinho ainda está vazio</h2><p>Salve uma receita favorita para encontrá-la facilmente depois.</p><a class="button" href="{{ route('receitas.listar') }}">Explorar receitas</a></div>
    @endif
</section>
@endsection
