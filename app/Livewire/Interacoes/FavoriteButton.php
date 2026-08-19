<?php

namespace App\Livewire\Interacoes;

use App\Models\Favorito;
use App\Models\Receita;
use Livewire\Component;

class FavoriteButton extends Component
{
    public Receita $receita;
    public bool $favorited = false;
    public bool $compact = false;

    public function mount(Receita $receita): void
    {
        $this->receita = $receita;
        $this->favorited = auth()->check() && Favorito::where('receita_id', $receita->id)->where('user_id', auth()->id())->exists();
    }

    public function toggle(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'), navigate: true);
            return;
        }

        $favorite = Favorito::where('receita_id', $this->receita->id)->where('user_id', auth()->id())->first();
        if ($favorite) {
            $favorite->delete();
            $this->favorited = false;
        } else {
            Favorito::create(['receita_id' => $this->receita->id, 'user_id' => auth()->id()]);
            $this->favorited = true;
        }
    }

    public function render()
    {
        return view('livewire.interacoes.favorite-button');
    }
}
