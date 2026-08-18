@php
use App\Models\Categoria;
use App\Models\Receita;
use Livewire\Volt\Component;

new class extends Component {
    public Receita $receita;
    public string $titulo = '';
    public string $descricao = '';
    public ?int $categoria_id = null;
    public int $tempo_preparo_min = 30;
    public int $tempo_cozimento_min = 0;
    public int $porcoes = 4;
    public string $custo = 'medio';
    public string $dificuldade = 'facil';
    public string $foto_principal_path = '';

    public function mount(Receita $receita): void
    {
        abort_unless($receita->user_id === auth()->id(), 403);
        $this->receita = $receita;
        foreach (['titulo', 'descricao', 'categoria_id', 'tempo_preparo_min', 'tempo_cozimento_min', 'porcoes', 'custo', 'dificuldade', 'foto_principal_path'] as $field) { $this->{$field} = $receita->{$field} ?? $this->{$field}; }
    }

    public function salvar(): void
    {
        $data = $this->validate(['titulo' => ['required', 'string', 'max:255'], 'descricao' => ['required', 'string', 'min:20'], 'categoria_id' => ['required', 'exists:categorias,id'], 'tempo_preparo_min' => ['required', 'integer', 'min:1', 'max:1440'], 'tempo_cozimento_min' => ['required', 'integer', 'min:0', 'max:1440'], 'porcoes' => ['required', 'integer', 'min:1', 'max:100'], 'custo' => ['required', 'in:baixo,medio,alto'], 'dificuldade' => ['required', 'in:facil,medio,dificil'], 'foto_principal_path' => ['nullable', 'url', 'max:500']]);
        $this->receita->update($data);
        session()->flash('sucesso', 'Receita atualizada.');
    }
}
@endphp

<x-layouts.public><section class="account-header"><div class="container"><span class="eyebrow">Seu caderno</span><h1>Editar receita</h1><p>Ajuste os detalhes e mantenha sua receita sempre atualizada.</p></div></section><section class="section container"><form wire:submit="salvar" class="recipe-form"><div class="form-intro"><div><span class="eyebrow">Atualização</span><h2>{{ $receita->titulo }}</h2></div><span class="form-required">* campos obrigatórios</span></div>@if(session('sucesso'))<div class="success-message form-field-wide">{{ session('sucesso') }}</div>@endif<div class="form-field form-field-wide"><label for="titulo">Título *</label><input id="titulo" type="text" wire:model="titulo">@error('titulo')<small class="form-error">{{ $message }}</small>@enderror</div><div class="form-field form-field-wide"><label for="descricao">Descrição *</label><textarea id="descricao" wire:model="descricao" rows="4"></textarea>@error('descricao')<small class="form-error">{{ $message }}</small>@enderror</div><div class="form-field"><label for="categoria_id">Categoria *</label><select id="categoria_id" wire:model="categoria_id"><option value="">Selecione</option>@foreach(Categoria::orderBy('nome')->get() as $category)<option value="{{ $category->id }}">{{ $category->nome }}</option>@endforeach</select></div><div class="form-field"><label for="foto_principal_path">URL da foto</label><input id="foto_principal_path" type="url" wire:model="foto_principal_path"></div><div class="form-field"><label for="tempo_preparo_min">Preparo (min) *</label><input id="tempo_preparo_min" type="number" wire:model="tempo_preparo_min"></div><div class="form-field"><label for="tempo_cozimento_min">Cozimento (min)</label><input id="tempo_cozimento_min" type="number" wire:model="tempo_cozimento_min"></div><div class="form-field"><label for="porcoes">Porções *</label><input id="porcoes" type="number" wire:model="porcoes"></div><div class="form-field"><label for="dificuldade">Dificuldade *</label><select id="dificuldade" wire:model="dificuldade"><option value="facil">Fácil</option><option value="medio">Médio</option><option value="dificil">Difícil</option></select></div><div class="form-field"><label for="custo">Custo *</label><select id="custo" wire:model="custo"><option value="baixo">Baixo</option><option value="medio">Médio</option><option value="alto">Alto</option></select></div><div class="form-actions"><a class="text-link" href="{{ route('minhas-receitas') }}">Cancelar</a><button class="button" type="submit">Salvar alterações <span>→</span></button></div></form></section></x-layouts.public>
