<?php

namespace App\Livewire\Receitas;

use App\Models\Receita;
use Livewire\Component;

class MyRecipes extends Component
{
    public string $aba = 'publicadas';

    public function mount(): void
    {
        $this->aba = request()->query('aba') === 'rascunhos' ? 'rascunhos' : 'publicadas';
    }

    /**
     * A consulta fica centralizada como propriedade computada para evitar
     * repetir a regra de dono em cada trecho da view.
     */
    public function getReceitasProperty()
    {
        return Receita::where('user_id', auth()->id())
            ->where('status', $this->aba === 'publicadas' ? 'publicada' : 'rascunho')
            ->with(['categoria', 'fonte'])
            ->latest()
            ->get();
    }

    public function excluir(int $id): void
    {
        Receita::where('user_id', auth()->id())->findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.receitas.my-recipes');
    }
}
