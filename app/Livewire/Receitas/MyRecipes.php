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

    public function receitas()
    {
        return Receita::where('user_id', auth()->id())
            ->where('status', $this->aba === 'publicadas' ? 'publicada' : 'rascunho')
            ->with('categoria')
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
