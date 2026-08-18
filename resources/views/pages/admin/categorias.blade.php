@php
use App\Models\Categoria;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use function Livewire\Volt\layout;

layout('layouts.admin');
new class extends Component {
    public string $nome = '';
    public ?int $editandoId = null;
    public function categorias() { return Categoria::withCount('receitas')->orderBy('nome')->get(); }
    public function salvar(): void { $this->validate(['nome' => ['required', 'string', 'max:255']]); $data = ['nome' => trim($this->nome), 'slug' => Str::slug($this->nome)]; $this->editandoId ? Categoria::findOrFail($this->editandoId)->update($data) : Categoria::create($data); $this->reset('nome', 'editandoId'); }
    public function editar(int $id): void { $category = Categoria::findOrFail($id); $this->editandoId = $id; $this->nome = $category->nome; }
    public function excluir(int $id): void { abort_if(Categoria::findOrFail($id)->receitas()->exists(), 422, 'Categoria possui receitas vinculadas.'); Categoria::destroy($id); }
};
@endphp

@php($categorias = Categoria::withCount('receitas')->orderBy('nome')->get())
@php($editandoId = null)

<div><section class="admin-panel"><div class="admin-panel-heading"><div><span class="eyebrow">Taxonomia</span><h2>Categorias</h2></div><span>{{ $categorias->count() }} categorias</span></div><form wire:submit="salvar" class="admin-inline-form"><input wire:model="nome" type="text" placeholder="Nome da nova categoria"><button type="submit" class="button">{{ $editandoId ? 'Atualizar' : 'Adicionar' }}</button>@if($editandoId)<button type="button" wire:click="$reset('nome', 'editandoId')" class="admin-cancel">Cancelar</button>@endif</form>@error('nome')<small class="form-error">{{ $message }}</small>@enderror<div class="admin-table">@foreach($categorias as $category)<div class="admin-table-row"><div><strong>{{ $category->nome }}</strong><span>{{ $category->receitas_count }} receitas</span></div><div class="admin-actions"><button wire:click="editar({{ $category->id }})">Editar</button><button wire:click="excluir({{ $category->id }})" wire:confirm="Excluir esta categoria?">Excluir</button></div></div>@endforeach</div></section></div>
