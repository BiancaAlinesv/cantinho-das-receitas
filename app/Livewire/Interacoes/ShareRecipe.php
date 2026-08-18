<?php

namespace App\Livewire\Interacoes;

use App\Models\Compartilhamento;
use App\Models\Receita;
use Livewire\Component;

class ShareRecipe extends Component
{
    public Receita $receita;
    public bool $linkCopiado = false;
    public int $total = 0;

    public function mount(Receita $receita): void
    {
        $this->receita = $receita;
        $this->total = Compartilhamento::where('receita_id', $receita->id)->count();
    }

    public function registrar(string $canal): void
    {
        abort_unless(in_array($canal, ['whatsapp', 'facebook', 'telegram', 'x', 'pinterest', 'link'], true), 422);
        Compartilhamento::create(['receita_id' => $this->receita->id, 'canal' => $canal, 'created_at' => now()]);
        $this->total++;
        $this->linkCopiado = $canal === 'link';
    }

    public function urlReceita(): string { return route('receitas.mostrar', $this->receita); }

    public function render()
    {
        return view('livewire.interacoes.share-recipe');
    }
}
