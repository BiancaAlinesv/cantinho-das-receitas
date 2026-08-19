<?php

use App\Models\Categoria;
use App\Models\Ingrediente;
use App\Models\Receita;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public string $titulo = '';
    public string $descricao = '';
    public ?int $categoria_id = null;
    public string $tipo_fonte = 'outro';
    public string $nome_fonte = '';
    public string $url_fonte = '';
    public string $observacoes_pessoais = '';
    public int $tempo_preparo_min = 30;
    public int $tempo_cozimento_min = 0;
    public int $porcoes = 4;
    public string $custo = 'medio';
    public string $dificuldade = 'facil';
    public string $modo_preparo = '';
    public $foto = null;
    public array $ingredientes = [];

    public function mount(): void
    {
        $this->adicionarIngrediente();
    }

    public function adicionarIngrediente(): void
    {
        $this->ingredientes[] = ['chave' => (string) Str::uuid(), 'nome' => '', 'quantidade' => null, 'unidade' => 'unidade', 'observacao' => ''];
    }

    public function removerIngrediente(int $indice): void
    {
        if (count($this->ingredientes) > 1) unset($this->ingredientes[$indice]);
        $this->ingredientes = array_values($this->ingredientes);
    }

    public function guardar(): void
    {
        $dados = $this->validate([
            'titulo' => ['required', 'string', 'max:255'], 'descricao' => ['required', 'string', 'min:20'],
            'categoria_id' => ['required', 'exists:categorias,id'], 'tipo_fonte' => ['required', 'in:instagram,tiktok,youtube,site,livro,familia,minha_receita,outro'],
            'nome_fonte' => ['nullable', 'string', 'max:255'], 'url_fonte' => ['nullable', 'url:http,https', 'max:2048'],
            'observacoes_pessoais' => ['nullable', 'string', 'max:5000'], 'tempo_preparo_min' => ['required', 'integer', 'min:1', 'max:1440'],
            'tempo_cozimento_min' => ['required', 'integer', 'min:0', 'max:1440'], 'porcoes' => ['required', 'integer', 'min:1', 'max:100'],
            'custo' => ['required', 'in:baixo,medio,alto'], 'dificuldade' => ['required', 'in:facil,medio,dificil'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], 'ingredientes' => ['required', 'array', 'min:1'],
            'ingredientes.*.nome' => ['required', 'string', 'max:255'], 'ingredientes.*.quantidade' => ['nullable', 'numeric', 'min:0'],
            'ingredientes.*.unidade' => ['required', 'in:g,kg,ml,l,xicara,colher_sopa,colher_cha,unidade,a_gosto'], 'ingredientes.*.observacao' => ['nullable', 'string', 'max:255'],
            'modo_preparo' => ['required', 'string', 'min:20'],
        ]);

        $ingredientes = $dados['ingredientes'];
        unset($dados['ingredientes'], $dados['foto']);
        $dados['foto_principal_path'] = $this->foto?->store('receitas', 'public');
        $dados['user_id'] = auth()->id();
        $dados['status'] = 'rascunho';
        $dados['published_at'] = null;

        $receita = DB::transaction(function () use ($dados, $ingredientes): Receita {
            $receita = Receita::create($dados);
            foreach ($ingredientes as $ordem => $item) {
                $ingrediente = Ingrediente::firstOrCreate(['nome' => trim($item['nome'])]);
                $receita->ingredientes()->create(['ingrediente_id' => $ingrediente->id, 'quantidade' => $item['quantidade'] ?: null, 'unidade' => $item['unidade'], 'observacao' => $item['observacao'] ?: null, 'ordem' => $ordem]);
            }
            $receita->passos()->create(['ordem' => 0, 'descricao' => $this->modo_preparo]);
            $receita->fonte()->create(['user_id' => auth()->id(), 'tipo' => $this->tipo_fonte, 'nome_fonte' => $this->nome_fonte ?: null, 'url' => $this->url_fonte ?: null, 'observacoes' => $this->observacoes_pessoais ?: null]);
            return $receita;
        });

        session()->flash('sucesso', 'Receita guardada no seu Cantinho.');
        $this->redirect(route('minhas-receitas', ['aba' => 'rascunhos']), navigate: true);
    }

    public function render()
    {
        return $this->view(['categories' => Categoria::query()->orderBy('nome')->get()])->layout('layouts.public', ['title' => 'Guardar receita — Cantinho das Receitas']);
    }
};
?>

<div><section class="listing-header"><div class="container"><span class="eyebrow">Guarde um sabor</span><h1>Guardar receita</h1><p>Registre uma receita encontrada em outro lugar para não perder de vista.</p></div></section><section class="section container"><form wire:submit="guardar" enctype="multipart/form-data" class="recipe-form"><div class="form-intro"><div><span class="eyebrow">Sua coleção</span><h2>Detalhes da receita</h2></div><span class="form-required">* campos obrigatórios</span></div><div class="form-field form-field-wide"><label for="titulo">Título *</label><input id="titulo" type="text" wire:model="titulo" placeholder="Ex.: Bolo da minha tia">@error('titulo')<small class="form-error">{{ $message }}</small>@enderror</div><div class="form-field form-field-wide"><label for="descricao">Descrição *</label><textarea id="descricao" wire:model="descricao" rows="4" placeholder="Conte o que torna essa receita especial..."></textarea>@error('descricao')<small class="form-error">{{ $message }}</small>@enderror</div><div class="form-field"><label for="categoria_id">Categoria *</label><select id="categoria_id" wire:model="categoria_id"><option value="">Selecione</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->nome }}</option>@endforeach</select>@error('categoria_id')<small class="form-error">{{ $message }}</small>@enderror</div><div class="form-field recipe-upload-field"><label for="foto">Foto da receita</label><input id="foto" type="file" wire:model="foto" accept="image/*"><span wire:loading wire:target="foto" class="upload-status">Enviando imagem...</span>@if($foto)<img class="recipe-upload-preview" src="{{ $foto->temporaryUrl() }}" alt="Prévia da foto">@endif<small>JPG, PNG ou WEBP de até 5 MB.</small>@error('foto')<small class="form-error">{{ $message }}</small>@enderror</div><div class="form-field form-field-wide ingredients-field"><div class="field-heading"><label>Ingredientes *</label><button type="button" wire:click="adicionarIngrediente" class="text-link">+ Adicionar ingrediente</button></div>@foreach($ingredientes as $indice => $ingrediente)<div wire:key="ingrediente-{{ $ingrediente['chave'] ?? $indice }}" class="ingredient-item"><div class="ingredient-row"><input type="text" wire:model="ingredientes.{{ $indice }}.nome" placeholder="Ingrediente"><input type="number" step="0.01" min="0" wire:model="ingredientes.{{ $indice }}.quantidade" placeholder="Qtd."><select wire:model="ingredientes.{{ $indice }}.unidade"><option value="unidade">unidade</option><option value="g">g</option><option value="kg">kg</option><option value="ml">ml</option><option value="l">l</option><option value="xicara">xícara</option><option value="colher_sopa">colher de sopa</option><option value="colher_cha">colher de chá</option><option value="a_gosto">a gosto</option></select><input type="text" wire:model="ingredientes.{{ $indice }}.observacao" placeholder="Observação"><button type="button" wire:click="removerIngrediente({{ $indice }})" class="remove-ingredient" aria-label="Remover ingrediente">×</button></div>@error("ingredientes.$indice.nome")<small class="form-error">{{ $message }}</small>@enderror</div>@endforeach</div><div class="form-field form-field-wide"><label for="modo_preparo">Modo de preparo *</label><textarea id="modo_preparo" wire:model="modo_preparo" rows="7" placeholder="Descreva o preparo passo a passo..."></textarea>@error('modo_preparo')<small class="form-error">{{ $message }}</small>@enderror</div><div class="form-field"><label for="tipo_fonte">Onde você encontrou?</label><select id="tipo_fonte" wire:model="tipo_fonte"><option value="instagram">Instagram</option><option value="tiktok">TikTok</option><option value="youtube">YouTube</option><option value="site">Site</option><option value="livro">Livro</option><option value="familia">Família</option><option value="minha_receita">Minha receita</option><option value="outro">Outro</option></select></div><div class="form-field"><label for="nome_fonte">Nome da fonte</label><input id="nome_fonte" type="text" wire:model="nome_fonte" placeholder="Ex.: Caderno da vó"></div><div class="form-field form-field-wide"><label for="url_fonte">Link original</label><input id="url_fonte" type="url" wire:model="url_fonte" placeholder="https://..."></div><div class="form-field form-field-wide"><label for="observacoes_pessoais">Minhas observações</label><textarea id="observacoes_pessoais" wire:model="observacoes_pessoais" rows="4" placeholder="O que você quer lembrar quando fizer de novo?"></textarea></div><div class="form-field"><label for="tempo_preparo_min">Preparo (min) *</label><input id="tempo_preparo_min" type="number" wire:model="tempo_preparo_min" min="1"></div><div class="form-field"><label for="tempo_cozimento_min">Cozimento (min)</label><input id="tempo_cozimento_min" type="number" wire:model="tempo_cozimento_min" min="0"></div><div class="form-field"><label for="porcoes">Porções *</label><input id="porcoes" type="number" wire:model="porcoes" min="1"></div><div class="form-field"><label for="dificuldade">Dificuldade *</label><select id="dificuldade" wire:model="dificuldade"><option value="facil">Fácil</option><option value="medio">Médio</option><option value="dificil">Difícil</option></select></div><div class="form-field"><label for="custo">Custo *</label><select id="custo" wire:model="custo"><option value="baixo">Baixo</option><option value="medio">Médio</option><option value="alto">Alto</option></select></div><div class="form-actions"><a href="{{ route('minhas-receitas') }}" class="text-link">Cancelar</a><button type="submit" wire:loading.attr="disabled" wire:target="guardar" class="button"><span wire:loading.remove wire:target="guardar">Guardar no meu Cantinho <span>→</span></span><span wire:loading wire:target="guardar">Guardando...</span></button></div></form></section></div>
</div>
