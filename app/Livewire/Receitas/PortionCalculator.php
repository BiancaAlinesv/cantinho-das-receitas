<?php

namespace App\Livewire\Receitas;

use App\Models\Receita;
use Livewire\Component;

class PortionCalculator extends Component
{
    public Receita $receita;
    public int $porcoesAtuais;

    public function mount(Receita $receita): void
    {
        $this->receita = $receita->load('ingredientes.ingrediente');
        $this->porcoesAtuais = $receita->porcoes;
    }

    public function aumentar(): void { $this->porcoesAtuais = min(100, $this->porcoesAtuais + 1); }
    public function diminuir(): void { $this->porcoesAtuais = max(1, $this->porcoesAtuais - 1); }

    public function getFatorProperty(): float
    {
        return $this->porcoesAtuais / max(1, $this->receita->porcoes);
    }

    public function ingredientesRecalculados()
    {
        $fator = $this->fator;

        return $this->receita->ingredientes->map(fn ($item) => [
            'nome' => $item->ingrediente->nome,
            'quantidade' => $item->quantidade === null ? null : round((float) $item->quantidade * $fator, 2),
            'unidade' => $item->unidade,
            'observacao' => $item->observacao,
        ]);
    }

    public function render()
    {
        return view('livewire.receitas.portion-calculator');
    }
}
