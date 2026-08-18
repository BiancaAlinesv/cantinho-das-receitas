@php
use App\Models\Comentario;
use Livewire\Volt\Component;
use function Livewire\Volt\layout;

layout('layouts.admin');
new class extends Component {
    public function comentarios() { return Comentario::with(['user', 'receita'])->latest()->get(); }
    public function alternarStatus(int $id): void { $comment = Comentario::findOrFail($id); $comment->update(['status' => $comment->status === 'publicado' ? 'oculto' : 'publicado']); }
    public function excluir(int $id): void { Comentario::destroy($id); }
};
@endphp

@php($comentarios = Comentario::with(['user', 'receita'])->latest()->get())

<div><section class="admin-panel"><div class="admin-panel-heading"><div><span class="eyebrow">Moderação</span><h2>Comentários</h2></div><span>{{ $comentarios->count() }} comentários</span></div><div class="admin-comment-list">@forelse($comentarios as $comment)<article class="admin-comment {{ $comment->status === 'oculto' ? 'is-hidden' : '' }}"><div class="admin-comment-heading"><strong>{{ $comment->user->name }}</strong><span>em {{ $comment->receita->titulo }}</span><time>{{ $comment->created_at->diffForHumans() }}</time></div><p>{{ $comment->conteudo }}</p><div class="admin-actions"><button wire:click="alternarStatus({{ $comment->id }})">{{ $comment->status === 'publicado' ? 'Ocultar' : 'Publicar' }}</button><button wire:click="excluir({{ $comment->id }})" wire:confirm="Excluir este comentário?">Excluir</button></div></article>@empty<p class="comments-empty">Nenhum comentário para moderar.</p>@endforelse</div></section></div>
