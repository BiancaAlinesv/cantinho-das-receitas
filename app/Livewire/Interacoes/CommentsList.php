<?php

namespace App\Livewire\Interacoes;

use App\Models\Comentario;
use App\Models\Receita;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class CommentsList extends Component
{
    public Receita $receita;
    public string $novoComentario = '';
    public ?int $editandoId = null;
    public string $textoEdicao = '';

    public function mount(Receita $receita): void { $this->receita = $receita; }

    public function comentarios()
    {
        return Comentario::where('receita_id', $this->receita->id)->whereNull('comentario_pai_id')->where('status', 'publicado')->with('user')->latest()->get();
    }

    public function comentar(): void
    {
        if (! auth()->check()) { $this->redirect(route('login'), navigate: true); return; }
        $key = 'comment:'.auth()->id();
        if (RateLimiter::tooManyAttempts($key, 5)) { $this->addError('novoComentario', 'Muitos comentários em pouco tempo. Aguarde um instante.'); return; }
        RateLimiter::hit($key, 60);
        $this->validate(['novoComentario' => ['required', 'string', 'min:2', 'max:1000']]);
        Comentario::create(['receita_id' => $this->receita->id, 'user_id' => auth()->id(), 'conteudo' => trim($this->novoComentario), 'status' => 'publicado']);
        $this->reset('novoComentario');
    }

    public function iniciarEdicao(int $id): void
    {
        $comment = Comentario::findOrFail($id);
        $this->authorize('update', $comment);
        $this->editandoId = $id;
        $this->textoEdicao = $comment->conteudo;
    }

    public function salvarEdicao(): void
    {
        $this->validate(['textoEdicao' => ['required', 'string', 'min:2', 'max:1000']]);
        $comment = Comentario::findOrFail($this->editandoId);
        $this->authorize('update', $comment);
        $comment->update(['conteudo' => trim($this->textoEdicao)]);
        $this->reset('editandoId', 'textoEdicao');
    }

    public function excluir(int $id): void
    {
        $comment = Comentario::findOrFail($id);
        $this->authorize('delete', $comment);
        $comment->delete();
    }

    public function render()
    {
        return view('livewire.interacoes.comments-list');
    }
}
