<?php

namespace App\Livewire\Interacoes;

use App\Models\Receita;
use App\Models\ReceitaUserStatus;
use Livewire\Component;

class RecipeStatusButton extends Component
{
    public Receita $receita;
    public ?string $statusAtual = null;

    public function mount(Receita $receita): void
    {
        $this->receita = $receita;
        $this->statusAtual = auth()->check()
            ? ReceitaUserStatus::where('user_id', auth()->id())->where('receita_id', $receita->id)->value('status')
            : null;
    }

    public function definir(string $status): void
    {
        abort_unless(in_array($status, ['quero_fazer', 'ja_fiz'], true), 422);

        if (! auth()->check()) {
            $this->redirect(route('login'), navigate: true);
            return;
        }

        if ($this->statusAtual === $status) {
            ReceitaUserStatus::where('user_id', auth()->id())->where('receita_id', $this->receita->id)->delete();
            $this->statusAtual = null;
            return;
        }

        ReceitaUserStatus::updateOrCreate(
            ['user_id' => auth()->id(), 'receita_id' => $this->receita->id],
            ['status' => $status],
        );
        $this->statusAtual = $status;
    }

    public function render()
    {
        return view('livewire.interacoes.recipe-status-button');
    }
}
