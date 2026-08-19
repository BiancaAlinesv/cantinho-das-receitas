<?php

namespace App\Livewire\Receitas;

use App\Models\Receita;
use Livewire\Component;
use Livewire\WithPagination;

class MyRecipes extends Component
{
    use WithPagination;

    public string $aba = 'publicadas';
    public string $busca = '';

    public function mount(): void
    {
        $this->aba = request()->query('aba') === 'rascunhos' ? 'rascunhos' : 'publicadas';
    }

    public function updatedBusca(): void
    {
        $this->resetPage();
    }

    public function updatedAba(): void
    {
        $this->resetPage();
    }

    /**
     * A consulta fica centralizada como propriedade computada para evitar
     * repetir a regra de dono em cada trecho da view.
     */
    public function getResumoProperty(): array
    {
        $resumo = Receita::where('user_id', auth()->id())
            ->selectRaw("COUNT(*) as total, SUM(CASE WHEN status = 'publicada' THEN 1 ELSE 0 END) as publicadas, SUM(CASE WHEN status = 'rascunho' THEN 1 ELSE 0 END) as rascunhos, COALESCE(SUM(visualizacoes_total), 0) as visualizacoes")
            ->first();

        return [
            'total' => (int) $resumo->total,
            'publicadas' => (int) $resumo->publicadas,
            'rascunhos' => (int) $resumo->rascunhos,
            'visualizacoes' => (int) $resumo->visualizacoes,
        ];
    }

    public function getReceitasProperty()
    {
        return Receita::where('user_id', auth()->id())
            ->where('status', $this->aba === 'publicadas' ? 'publicada' : 'rascunho')
            ->with(['categoria', 'fonte'])
            ->when(trim($this->busca) !== '', function ($consulta): void {
                $termo = '%'.mb_strtolower(trim($this->busca)).'%';
                $consulta->where(function ($filtro) use ($termo): void {
                    $filtro->whereRaw('LOWER(titulo) LIKE ?', [$termo])
                        ->orWhereHas('categoria', fn ($categoria) => $categoria->whereRaw('LOWER(nome) LIKE ?', [$termo]));
                });
            })
            ->latest()
            ->paginate(9);
    }

    public function excluir(int $id): void
    {
        Receita::where('user_id', auth()->id())->findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.receitas.my-recipes')->layout('layouts.public', ['title' => 'Minhas receitas — Cantinho das Receitas']);
    }
}
