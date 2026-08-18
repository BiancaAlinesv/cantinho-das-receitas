@php
use App\Models\Categoria;
use App\Models\Receita;
use Livewire\Volt\Component;

new class extends Component {
    public string $titulo = '';
    public string $descricao = '';
    public ?int $categoria_id = null;
    public int $tempo_preparo_min = 30;
    public int $tempo_cozimento_min = 0;
    public int $porcoes = 4;
    public string $custo = 'medio';
    public string $dificuldade = 'facil';
    public string $foto_principal_path = '';

    public function salvar(string $status = 'rascunho'): void
    {
        $data = $this->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['required', 'string', 'min:20'],
            'categoria_id' => ['required', 'exists:categorias,id'],
            'tempo_preparo_min' => ['required', 'integer', 'min:1', 'max:1440'],
            'tempo_cozimento_min' => ['required', 'integer', 'min:0', 'max:1440'],
            'porcoes' => ['required', 'integer', 'min:1', 'max:100'],
            'custo' => ['required', 'in:baixo,medio,alto'],
            'dificuldade' => ['required', 'in:facil,medio,dificil'],
            'foto_principal_path' => ['nullable', 'url', 'max:500'],
        ]);

        $data['user_id'] = auth()->id();
        $data['status'] = $status;
        $data['published_at'] = $status === 'publicada' ? now() : null;
        $recipe = Receita::create($data);

        $this->redirect(route('receitas.listar', ['busca' => $recipe->titulo]), navigate: true);
    }
}
@endphp

<x-layouts.public>
    <section class="listing-header"><div class="container"><span class="eyebrow">Compartilhe seu sabor</span><h1>Nova receita</h1><p>Guarde uma receita especial no nosso caderno.</p></div></section>
    <section class="section container"><form wire:submit="salvar('publicada')" class="recipe-form">
        <div class="form-intro"><div><span class="eyebrow">Detalhes principais</span><h2>Conte como se faz</h2></div><span class="form-required">* campos obrigatórios</span></div>
        <div class="form-field form-field-wide"><label for="titulo">Título *</label><input id="titulo" type="text" wire:model="titulo" placeholder="Ex.: Bolo de fubá da vovó">@error('titulo')<small class="form-error">{{ $message }}</small>@enderror</div>
        <div class="form-field form-field-wide"><label for="descricao">Descrição *</label><textarea id="descricao" wire:model="descricao" rows="4" placeholder="Conte um pouco sobre essa receita..."></textarea>@error('descricao')<small class="form-error">{{ $message }}</small>@enderror</div>
        <div class="form-field"><label for="categoria_id">Categoria *</label><select id="categoria_id" wire:model="categoria_id"><option value="">Selecione</option>@foreach (Categoria::query()->orderBy('nome')->get() as $category)<option value="{{ $category->id }}">{{ $category->nome }}</option>@endforeach</select>@error('categoria_id')<small class="form-error">{{ $message }}</small>@enderror</div>
        <div class="form-field"><label for="foto_principal_path">URL da foto</label><input id="foto_principal_path" type="url" wire:model="foto_principal_path" placeholder="https://...">@error('foto_principal_path')<small class="form-error">{{ $message }}</small>@enderror</div>
        <div class="form-field"><label for="tempo_preparo_min">Preparo (min) *</label><input id="tempo_preparo_min" type="number" wire:model="tempo_preparo_min" min="1"></div>
        <div class="form-field"><label for="tempo_cozimento_min">Cozimento (min)</label><input id="tempo_cozimento_min" type="number" wire:model="tempo_cozimento_min" min="0"></div>
        <div class="form-field"><label for="porcoes">Porções *</label><input id="porcoes" type="number" wire:model="porcoes" min="1"></div>
        <div class="form-field"><label for="dificuldade">Dificuldade *</label><select id="dificuldade" wire:model="dificuldade"><option value="facil">Fácil</option><option value="medio">Médio</option><option value="dificil">Difícil</option></select></div>
        <div class="form-field"><label for="custo">Custo *</label><select id="custo" wire:model="custo"><option value="baixo">Baixo</option><option value="medio">Médio</option><option value="alto">Alto</option></select></div>
        <div class="form-actions"><a href="{{ route('inicio') }}" class="text-link">Cancelar</a><button type="button" wire:click="salvar('rascunho')" class="button button-outline">Salvar rascunho</button><button type="submit" class="button">Publicar receita <span>→</span></button></div>
    </form></section>
</x-layouts.public>
