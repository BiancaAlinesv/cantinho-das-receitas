@props(['recipe'])
@php
    $card = is_array($recipe) ? $recipe : [
        'title' => $recipe->titulo,
        'slug' => $recipe->slug,
        'category' => $recipe->categoria?->nome ?? 'Receitas',
        'time' => $recipe->tempoTotalMin().' min',
        'difficulty' => ucfirst($recipe->dificuldade),
        'rating' => number_format((float) $recipe->nota_media, 1, ',', '.'),
        'reviews' => $recipe->total_avaliacoes,
        'image' => $recipe->foto_url ?: 'https://placehold.co/900x600/f3eadf/bd5b32?text=Receita',
    ];
@endphp

<article class="recipe-card">
    <a href="{{ isset($card['slug']) ? route('receitas.mostrar', $card['slug']) : route('receitas.listar', ['busca' => $card['title']]) }}" class="recipe-image-wrap">
        <img src="{{ $card['image'] }}" alt="{{ $card['title'] }}" loading="lazy" class="recipe-image">
        <span class="recipe-category">{{ $card['category'] }}</span>
        <span class="recipe-save" aria-hidden="true">♡</span>
    </a>
    <div class="recipe-card-body">
        <h3><a href="{{ isset($card['slug']) ? route('receitas.mostrar', $card['slug']) : route('receitas.listar', ['busca' => $card['title']]) }}" title="{{ $card['title'] }}">{{ $card['title'] }}</a></h3>
        <div class="recipe-meta"><span>◷ {{ $card['time'] }}</span><span>•</span><span>{{ $card['difficulty'] }}</span></div>
        <div class="recipe-rating">@if((int) $card['reviews'] > 0)<span aria-hidden="true">★</span><strong>{{ $card['rating'] }}</strong><small>({{ $card['reviews'] }})</small>@else<span class="rating-empty">Ainda sem avaliações</span>@endif</div>
    </div>
</article>
