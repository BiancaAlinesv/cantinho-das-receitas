<?php

namespace App\Livewire\Receitas;

use App\Models\Categoria;
use App\Models\Ingrediente;
use App\Models\Receita;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateRecipe extends Component
{
    use WithFileUploads;

    public string $titulo = '';
    public string $descricao = '';
    public ?int $categoria_id = null;
    public int $tempo_preparo_min = 30;
    public int $tempo_cozimento_min = 0;
    public int $porcoes = 4;
    public string $custo = 'medio';
    public string $dificuldade = 'facil';
    public $foto = null;
    public array $ingredientes = [];
    public string $modo_preparo = '';

    public function adicionarIngrediente(): void
    {
        $this->ingredientes[] = $this->novoIngrediente();
    }

    protected function novoIngrediente(): array
    {
        return ['chave' => (string) Str::uuid(), 'nome' => '', 'quantidade' => null, 'unidade' => 'unidade', 'observacao' => ''];
    }

    public function mount(): void
    {
        $this->ingredientes[] = $this->novoIngrediente();
    }

    public function removerIngrediente(int $indice): void
    {
        if (count($this->ingredientes) > 1) unset($this->ingredientes[$indice]);
        $this->ingredientes = array_values($this->ingredientes);
    }

    public function salvar(string $status = 'rascunho'): void
    {
        abort_unless(in_array($status, ['rascunho', 'publicada'], true), 422);

        $data = $this->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['required', 'string', 'min:20'],
            'categoria_id' => ['required', 'exists:categorias,id'],
            'tempo_preparo_min' => ['required', 'integer', 'min:1', 'max:1440'],
            'tempo_cozimento_min' => ['required', 'integer', 'min:0', 'max:1440'],
            'porcoes' => ['required', 'integer', 'min:1', 'max:100'],
            'custo' => ['required', 'in:baixo,medio,alto'],
            'dificuldade' => ['required', 'in:facil,medio,dificil'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'ingredientes' => ['required', 'array', 'min:1'],
            'ingredientes.*.nome' => ['required', 'string', 'max:255'],
            'ingredientes.*.quantidade' => ['nullable', 'numeric', 'min:0'],
            'ingredientes.*.unidade' => ['required', 'in:g,kg,ml,l,xicara,colher_sopa,colher_cha,unidade,a_gosto'],
            'ingredientes.*.observacao' => ['nullable', 'string', 'max:255'],
            'modo_preparo' => ['required', 'string', 'min:20'],
        ]);

        $data['foto_principal_path'] = $this->foto?->store('receitas', 'public');
        unset($data['foto']);

        $ingredientes = $data['ingredientes'];
        $modoPreparo = $data['modo_preparo'];
        unset($data['foto'], $data['ingredientes'], $data['modo_preparo']);
        $data['user_id'] = auth()->id();
        $data['status'] = $status;
        $data['published_at'] = $status === 'publicada' ? now() : null;

        $recipe = DB::transaction(function () use ($data, $ingredientes, $modoPreparo): Receita {
            $recipe = Receita::create($data);
            foreach ($ingredientes as $ordem => $item) {
                $ingrediente = Ingrediente::firstOrCreate(['nome' => trim($item['nome'])]);
                $recipe->ingredientes()->create([
                    'ingrediente_id' => $ingrediente->id,
                    'quantidade' => $item['quantidade'] ?: null,
                    'unidade' => $item['unidade'],
                    'observacao' => $item['observacao'] ?: null,
                    'ordem' => $ordem,
                ]);
            }
            $recipe->passos()->create(['ordem' => 0, 'descricao' => $modoPreparo]);
            return $recipe;
        });

        session()->flash('sucesso', $status === 'rascunho' ? 'Rascunho salvo com sucesso.' : 'Receita publicada com sucesso.');
        $this->redirect($status === 'rascunho' ? route('minhas-receitas', ['aba' => 'rascunhos']) : route('receitas.mostrar', $recipe), navigate: true);
    }

    public function render()
    {
        return view('livewire.receitas.create-recipe', ['categories' => Categoria::query()->orderBy('nome')->get()])
            ->layout('layouts.public', ['title' => 'Nova receita — Cantinho das Receitas']);
    }
}
