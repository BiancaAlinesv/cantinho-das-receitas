<?php

namespace App\Livewire\Interacoes;

use App\Models\Curtida;
use App\Models\Receita;
use Livewire\Component;

class LikeButton extends Component
{
    public Receita $receita;
    public bool $liked = false;
    public int $total = 0;

    public function mount(Receita $receita): void
    {
        $this->receita = $receita;
        $this->total = Curtida::where('receita_id', $receita->id)->count();
        $this->liked = auth()->check() && Curtida::where('receita_id', $receita->id)->where('user_id', auth()->id())->exists();
    }

    public function toggle(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'), navigate: true);
            return;
        }

        $like = Curtida::where('receita_id', $this->receita->id)->where('user_id', auth()->id())->first();
        if ($like) {
            $like->delete();
            $this->liked = false;
            $this->total--;
        } else {
            Curtida::create(['receita_id' => $this->receita->id, 'user_id' => auth()->id()]);
            $this->liked = true;
            $this->total++;
        }
    }

    public function render()
    {
        return view('livewire.interacoes.like-button');
    }
}
