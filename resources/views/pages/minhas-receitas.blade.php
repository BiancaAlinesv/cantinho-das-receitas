@php
use App\Models\Receita;
use Livewire\Volt\Component;
use function Livewire\Volt\layout;

layout('layouts.public');
new class extends Component {
    public string $aba = 'publicadas';

    public function receitas()
    {
        return Receita::where('user_id', auth()->id())->where('status', $this->aba === 'publicadas' ? 'publicada' : 'rascunho')->with('categoria')->latest()->get();
    }

    public function excluir(int $id): void
    {
        Receita::where('user_id', auth()->id())->findOrFail($id)->delete();
    }
};
@endphp

@php($aba = 'publicadas')
@php($receitas = Receita::where('user_id', auth()->id())->where('status', 'publicada')->with('categoria')->latest()->get())

<div><section class="account-header"><div class="container"><span class="eyebrow">Sua cozinha</span><h1>Minhas receitas</h1><p>Organize o que você criou e continue compartilhando sabor.</p></div></section><section class="account-section container"><div class="account-toolbar"><div class="account-tabs"><button wire:click="$set('aba', 'publicadas')" class="{{ $aba === 'publicadas' ? 'active' : '' }}">Publicadas</button><button wire:click="$set('aba', 'rascunhos')" class="{{ $aba === 'rascunhos' ? 'active' : '' }}">Rascunhos</button></div><a class="button" href="{{ route('receitas.criar') }}">+ Nova receita</a></div><div class="user-recipe-list">@forelse($receitas as $recipe)<article class="user-recipe-row"><div class="user-recipe-thumb"><img src="{{ $recipe->foto_principal_path ?: 'https://placehold.co/200x150/f3eadf/bd5b32?text=Receita' }}" alt=""></div><div class="user-recipe-info"><span class="eyebrow">{{ $recipe->categoria->nome }}</span><h2>{{ $recipe->titulo }}</h2><p>{{ \Illuminate\Support\Str::limit($recipe->descricao, 100) }}</p></div><div class="user-recipe-meta"><span>{{ $recipe->visualizacoes_total }} visualizações</span><div><a href="{{ route('receitas.mostrar', $recipe) }}">Ver</a><a class="edit-link" href="{{ route('receitas.editar', $recipe) }}">Editar</a><button wire:click="excluir({{ $recipe->id }})" wire:confirm="Excluir esta receita?" type="button">Excluir</button></div></div></article>@empty<div class="empty-state"><span>✦</span><h2>Nenhuma receita por aqui</h2><p>Comece compartilhando uma receita especial.</p><a class="button" href="{{ route('receitas.criar') }}">Criar primeira receita</a></div>@endforelse</div></section></div>
