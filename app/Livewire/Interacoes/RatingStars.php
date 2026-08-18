<?php

namespace App\Livewire\Interacoes;

use App\Models\Avaliacao;
use App\Models\Receita;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class RatingStars extends Component
{
    public Receita $receita;
    public int $minhaNota = 0;

    public function mount(Receita $receita): void
    {
        $this->receita = $receita;
        if (auth()->check()) {
            $this->minhaNota = (int) Avaliacao::where('receita_id', $receita->id)->where('user_id', auth()->id())->value('nota');
        }
    }

    public function avaliar(int $nota): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'), navigate: true);
            return;
        }

        abort_unless($nota >= 1 && $nota <= 5, 422);
        $key = 'rating:'.auth()->id();
        if (RateLimiter::tooManyAttempts($key, 10)) return;
        RateLimiter::hit($key, 60);
        Avaliacao::updateOrCreate(['receita_id' => $this->receita->id, 'user_id' => auth()->id()], ['nota' => $nota]);
        $this->minhaNota = $nota;
        $this->receita->refresh();
    }

    public function render()
    {
        return view('livewire.interacoes.rating-stars');
    }
}
