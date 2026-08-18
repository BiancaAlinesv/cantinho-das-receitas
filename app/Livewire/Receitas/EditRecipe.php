<?php

namespace App\Livewire\Receitas;

use App\Models\Categoria;
use App\Models\Ingrediente;
use App\Models\Receita;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditRecipe extends Component
{
    use WithFileUploads;

    public Receita $receita;
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

    public function mount(Receita $receita): void
    {
        abort_unless($receita->user_id === auth()->id(), 403);
        $this->receita = $receita;
        foreach (['titulo', 'descricao', 'categoria_id', 'tempo_preparo_min', 'tempo_cozimento_min', 'porcoes', 'custo', 'dificuldade'] as $campo) {
            $this->{$campo} = $receita->{$campo} ?? $this->{$campo};
        }
        $this->ingredientes = $receita->load('ingredientes.ingrediente')->ingredientes->map(fn ($item) => ['chave' => (string) Str::uuid(), 'nome' => $item->ingrediente->nome, 'quantidade' => $item->quantidade, 'unidade' => $item->unidade, 'observacao' => $item->observacao ?? ''])->all();
        $this->ingredientes = $this->ingredientes ?: [$this->novoIngrediente()];
        $this->modo_preparo = $receita->passos()->orderBy('ordem')->value('descricao') ?? '';
    }

    public function adicionarIngrediente(): void
    {
        $this->ingredientes[] = $this->novoIngrediente();
    }

    protected function novoIngrediente(): array
    {
        return ['chave' => (string) Str::uuid(), 'nome' => '', 'quantidade' => null, 'unidade' => 'unidade', 'observacao' => ''];
    }

    public function removerIngrediente(int $indice): void
    {
        if (count($this->ingredientes) > 1) {
            unset($this->ingredientes[$indice]);
        }

        $this->ingredientes = array_values($this->ingredientes);
    }

    public function salvar(): void
    {
        $dados = $this->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['required', 'string', 'min:20'],
            'categoria_id' => ['required', 'exists:categorias,id'],
            'tempo_preparo_min' => ['required', 'integer', 'min:1', 'max:1440'],
            'tempo_cozimento_min' => ['required', 'integer', 'min:0', 'max:1440'],
            'porcoes' => ['required', 'integer', 'min:1', 'max:100'],
            'custo' => ['required', 'in:baixo,medio,alto'],
            'dificuldade' => ['required', 'in:facil,medio,dificil'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'ingredientes' => ['required', 'array', 'min:1'], 'ingredientes.*.nome' => ['required', 'string', 'max:255'], 'ingredientes.*.quantidade' => ['nullable', 'numeric', 'min:0'], 'ingredientes.*.unidade' => ['required', 'in:g,kg,ml,l,xicara,colher_sopa,colher_cha,unidade,a_gosto'], 'ingredientes.*.observacao' => ['nullable', 'string', 'max:255'],
            'modo_preparo' => ['required', 'string', 'min:20'],
        ]);

        unset($dados['foto']);
        $ingredientes = $dados['ingredientes']; $modoPreparo = $dados['modo_preparo']; unset($dados['ingredientes'], $dados['modo_preparo']);
        if ($this->foto) {
            if ($this->receita->foto_principal_path && ! str_starts_with($this->receita->foto_principal_path, 'http')) {
                Storage::disk('public')->delete($this->receita->foto_principal_path);
            }
            $dados['foto_principal_path'] = $this->foto->store('receitas', 'public');
        }

        DB::transaction(function () use ($dados, $ingredientes, $modoPreparo): void {
            $this->receita->update($dados);
            $this->receita->ingredientes()->delete();
            $this->receita->passos()->delete();
            foreach ($ingredientes as $ordem => $item) {
                $ingrediente = Ingrediente::firstOrCreate(['nome' => trim($item['nome'])]);
                $this->receita->ingredientes()->create(['ingrediente_id' => $ingrediente->id, 'quantidade' => $item['quantidade'] ?: null, 'unidade' => $item['unidade'], 'observacao' => $item['observacao'] ?: null, 'ordem' => $ordem]);
            }
            $this->receita->passos()->create(['ordem' => 0, 'descricao' => $modoPreparo]);
        });
        session()->flash('sucesso', 'Receita atualizada.');
        $this->redirect(route('minhas-receitas'), navigate: true);
    }

    public function render()
    {
        return view('livewire.receitas.edit-recipe', ['categories' => Categoria::query()->orderBy('nome')->get()])
            ->layout('layouts.public', ['title' => 'Editar receita — Cantinho das Receitas']);
    }
}
