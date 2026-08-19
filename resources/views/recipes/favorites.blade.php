@extends('layouts.public')

@section('title', 'Minhas favoritas — Cantinho das Receitas')

@section('content')
<section class="account-header"><div class="container"><span class="eyebrow">Sua coleção</span><h1>Minhas favoritas</h1><p>Receitas que você quer encontrar de novo.</p></div></section>
<section class="section container">
    @if($favoritos->isNotEmpty())
        <div class="recipe-grid">@foreach($favoritos as $receita)<x-recipe-card :recipe="$receita" />@endforeach</div>
    @else
        <x-ui.empty-state icon="♡" title="Seu cantinho ainda está vazio" description="Salve uma receita favorita para encontrá-la facilmente depois."><x-ui.button as="a" :href="route('receitas.listar')">Explorar receitas</x-ui.button></x-ui.empty-state>
    @endif
</section>
@endsection
