<div>
    <section class="account-header personal-library-hero">
        <div class="container">
            <span class="eyebrow">Sua cozinha</span>
            <h1>Minhas receitas</h1>
            <p>Suas receitas, suas histórias, tudo guardado em um só lugar.</p>
        </div>
    </section>

    <section class="account-section container personal-library">
        <div class="collection-summary" aria-label="Resumo da sua coleção">
            <div><strong>{{ $this->resumo['total'] }}</strong><span>Receitas</span></div>
            <div><strong>{{ $this->resumo['publicadas'] }}</strong><span>Publicadas</span></div>
            <div><strong>{{ $this->resumo['rascunhos'] }}</strong><span>Rascunhos</span></div>
            <div><strong>{{ number_format($this->resumo['visualizacoes'], 0, ',', '.') }}</strong><span>Visualizações</span></div>
        </div>

        <div class="account-toolbar personal-library-toolbar">
            <div>
                <div class="account-tabs" role="tablist" aria-label="Filtrar minhas receitas">
                    <button wire:click="$set('aba', 'publicadas')" class="{{ $aba === 'publicadas' ? 'active' : '' }}" role="tab" aria-selected="{{ $aba === 'publicadas' ? 'true' : 'false' }}">Publicadas <small>{{ $this->resumo['publicadas'] }}</small></button>
                    <button wire:click="$set('aba', 'rascunhos')" class="{{ $aba === 'rascunhos' ? 'active' : '' }}" role="tab" aria-selected="{{ $aba === 'rascunhos' ? 'true' : 'false' }}">Rascunhos <small>{{ $this->resumo['rascunhos'] }}</small></button>
                </div>
            </div>
            <div class="account-toolbar-actions">
                <a class="button button-outline" href="{{ route('receitas.guardar') }}">♡ Guardar receita</a>
                <a class="button" href="{{ route('receitas.criar') }}"><span aria-hidden="true">＋</span> Nova receita</a>
            </div>
        </div>

        <div class="personal-library-search">
            <label for="busca-minhas-receitas">Buscar nas minhas receitas</label>
            <div class="search-form">
                <span aria-hidden="true">⌕</span>
                <input id="busca-minhas-receitas" type="search" wire:model.live.debounce.400ms="busca" placeholder="Buscar por título ou categoria..." aria-label="Buscar nas minhas receitas">
                @if($busca)<button type="button" wire:click="$set('busca', '')" class="search-clear" aria-label="Limpar busca">×</button>@endif
            </div>
        </div>

        <div wire:loading.flex wire:target="busca, aba" class="library-loading" role="status">Atualizando seu caderno...</div>

        @if($this->receitas->count())
            <div class="personal-recipe-grid">
                @foreach($this->receitas as $recipe)
                    <article class="personal-recipe-card" wire:key="minha-receita-{{ $recipe->id }}">
                        <div class="personal-recipe-image">
                            <img src="{{ $recipe->foto_url ?: 'https://placehold.co/600x420/f3eadf/bd5b32?text=Receita' }}" alt="Imagem de {{ $recipe->titulo }}" loading="lazy">
                            <span class="recipe-status-badge {{ $recipe->status === 'publicada' ? 'published' : 'draft' }}">{{ $recipe->status === 'publicada' ? 'Publicada' : 'Rascunho' }}</span>
                        </div>
                        <div class="personal-recipe-content">
                            <span class="eyebrow">{{ $recipe->categoria?->nome ?? 'Receita' }} @if($recipe->fonte) · guardada de {{ ucfirst($recipe->fonte->tipo) }} @endif</span>
                            <h2>{{ $recipe->titulo }}</h2>
                            <p>{{ \Illuminate\Support\Str::limit($recipe->descricao, 105) }}</p>
                            <div class="personal-recipe-meta"><span>{{ $recipe->status === 'publicada' ? number_format($recipe->visualizacoes_total, 0, ',', '.').' visualizações' : 'Rascunho salvo' }}</span><span>{{ optional($recipe->updated_at)->format('d/m/Y') }}</span></div>
                            <div class="personal-recipe-actions">
                                @if($recipe->status === 'publicada')<a class="button button-small" href="{{ route('receitas.mostrar', $recipe) }}">Ver receita</a>@endif
                                <details class="recipe-actions-menu"><summary aria-label="Mais ações">⋯</summary><div><a href="{{ route('receitas.editar', $recipe) }}">Editar</a><button type="button" wire:click="excluir({{ $recipe->id }})" wire:confirm="Excluir esta receita?\n\n{{ $recipe->titulo }} será removida do seu Cantinho. Esta ação não poderá ser desfeita.">Excluir</button></div></details>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="library-pagination">{{ $this->receitas->links() }}</div>
        @else
            <div class="empty-state personal-empty-state">
                <span aria-hidden="true">{{ $busca ? '⌕' : ($aba === 'rascunhos' ? '✎' : '✦') }}</span>
                @if($busca)
                    <h2>Não encontramos nenhuma receita com esse nome.</h2><p>Tente outro título ou categoria.</p><button type="button" wire:click="$set('busca', '')" class="button">Limpar busca</button>
                @elseif($aba === 'rascunhos')
                    <h2>Nenhum rascunho por aqui.</h2><p>Suas ideias em andamento aparecerão neste espaço.</p><a class="button" href="{{ route('receitas.criar') }}">Criar receita</a>
                @else
                    <h2>Seu caderno ainda está esperando a primeira receita.</h2><p>Guarde aquela receita especial que você não quer perder.</p><a class="button" href="{{ route('receitas.criar') }}">Criar minha primeira receita</a>
                @endif
            </div>
        @endif
    </section>
</div>
