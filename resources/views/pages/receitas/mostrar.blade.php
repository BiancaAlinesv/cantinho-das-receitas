@php
use App\Models\Receita;
use App\Models\Visualizacao;
use App\Services\ReceitaRelacionadaService;
use Livewire\Volt\Component;

new class extends Component {
    public Receita $receita;

    public function relacionadas()
    {
        return app(ReceitaRelacionadaService::class)->buscar($this->receita);
    }

    public function mount(Receita $receita): void
    {
        abort_unless($receita->status === 'publicada', 404);
        $this->receita = $receita->load(['categoria', 'ingredientes.ingrediente', 'passos', 'user']);
        $hash = hash('sha256', request()->ip() ?: 'unknown');
        if (! Visualizacao::where('receita_id', $receita->id)->where('ip_hash', $hash)->whereDate('created_at', today())->exists()) {
            Visualizacao::create(['receita_id' => $receita->id, 'user_id' => auth()->id(), 'ip_hash' => $hash]);
            $receita->increment('visualizacoes_total');
        }
    }
}
@endphp

@php
    $receita = request()->route('receita');
    if (is_string($receita)) { $receita = Receita::where('slug', $receita)->firstOrFail(); }
    $receita = $receita->load(['categoria', 'ingredientes.ingrediente', 'passos', 'user']);
    $seoSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Recipe',
        'name' => $receita->titulo,
        'image' => $receita->foto_principal_path ? [$receita->foto_principal_path] : null,
        'description' => $receita->descricao,
        'author' => ['@type' => 'Person', 'name' => $receita->user->name],
        'prepTime' => 'PT'.$receita->tempo_preparo_min.'M',
        'cookTime' => $receita->tempo_cozimento_min ? 'PT'.$receita->tempo_cozimento_min.'M' : null,
        'recipeYield' => $receita->porcoes.' porções',
        'recipeCategory' => $receita->categoria->nome,
        'recipeIngredient' => $receita->ingredientes->map(fn ($item) => trim(($item->quantidade !== null ? $item->quantidade.' '.$item->unidade.' ' : '').$item->ingrediente->nome))->values()->all(),
        'aggregateRating' => $receita->total_avaliacoes > 0 ? ['@type' => 'AggregateRating', 'ratingValue' => (float) $receita->nota_media, 'ratingCount' => $receita->total_avaliacoes] : null,
    ];
@endphp

<x-layouts.public :title="$receita->titulo.' — Cantinho das Receitas'" :description="\Illuminate\Support\Str::limit($receita->descricao, 155)" :image="$receita->foto_principal_path ?: null" og-type="article">
    <x-slot name="head"><script type="application/ld+json">{!! json_encode($seoSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script></x-slot>
    <article class="recipe-detail container">
        <a class="back-link" href="{{ route('receitas.listar') }}">← Voltar para receitas</a>
        <div class="detail-hero"><img src="{{ $receita->foto_principal_path ?: 'https://placehold.co/1200x700/f3eadf/bd5b32?text=Receita' }}" alt="{{ $receita->titulo }}"><div class="detail-hero-overlay"><span class="recipe-category">{{ $receita->categoria->nome }}</span><h1>{{ $receita->titulo }}</h1><p>{{ $receita->descricao }}</p></div></div>
        <div class="detail-layout"><div class="detail-main"><div class="recipe-facts"><div><span>Preparo</span><strong>{{ $receita->tempo_preparo_min }} min</strong></div><div><span>Cozimento</span><strong>{{ $receita->tempo_cozimento_min ?: '—' }} min</strong></div><div><span>Total</span><strong>{{ $receita->tempoTotalMin() }} min</strong></div><div><span>Rendimento</span><strong>{{ $receita->porcoes }} porções</strong></div><div><span>Dificuldade</span><strong>{{ ucfirst($receita->dificuldade) }}</strong></div></div><div class="detail-social"><livewire:interacoes.like-button :receita="$receita" :key="'like-'.$receita->id" /><livewire:interacoes.favorite-button :receita="$receita" :key="'favorite-'.$receita->id" /></div><livewire:receitas.portion-calculator :receita="$receita" :key="'calculator-'.$receita->id" /><section class="preparation"><div class="detail-section-heading"><div><span class="eyebrow">Passo a passo</span><h2>Modo de preparo</h2></div></div><ol>@foreach($receita->passos as $step)<li><span>{{ $loop->iteration }}</span><p>{{ $step->descricao }}</p></li>@endforeach</ol></section><livewire:interacoes.rating-stars :receita="$receita" :key="'rating-'.$receita->id" /><livewire:interacoes.share-recipe :receita="$receita" :key="'share-'.$receita->id" /><livewire:interacoes.comments-list :receita="$receita" :key="'comments-'.$receita->id" /></div><aside class="detail-aside"><div class="aside-card"><span class="eyebrow">Essa receita é</span><div class="aside-rating"><strong>★ {{ number_format((float)$receita->nota_media, 1, ',', '.') }}</strong><span>{{ $receita->total_avaliacoes }} avaliações</span></div><div class="aside-line"><span>Custo</span><strong>{{ ucfirst($receita->custo) }}</strong></div><div class="aside-line"><span>Visualizações</span><strong>{{ number_format($receita->visualizacoes_total, 0, ',', '.') }}</strong></div></div><div class="aside-note"><span>✦</span><p>Uma receita feita para ser compartilhada. Se preparar, conte para a gente como ficou.</p></div></aside></div>
        <section class="related-section"><div class="section-heading"><div><span class="eyebrow">Mais ideias para você</span><h2>Você também pode gostar</h2></div><a class="text-link" href="{{ route('receitas.listar') }}">Ver todas <span>→</span></a></div><div class="recipe-grid related-grid">@foreach(app(ReceitaRelacionadaService::class)->buscar($receita) as $related)<x-recipe-card :recipe="$related" />@endforeach</div></section>
    </article>
</x-layouts.public>
