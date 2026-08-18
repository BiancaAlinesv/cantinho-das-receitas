@php
use App\Models\User;
use Livewire\Volt\Component;
use function Livewire\Volt\layout;

layout('layouts.admin');
new class extends Component {
    public function usuarios() { return User::withCount('receitas')->orderBy('name')->get(); }
    public function promover(int $id): void { User::whereKey($id)->update(['is_admin' => true]); }
    public function rebaixar(int $id): void { abort_if($id === auth()->id(), 422, 'Você não pode remover seu próprio acesso.'); User::whereKey($id)->update(['is_admin' => false]); }
    public function remover(int $id): void { abort_if($id === auth()->id(), 422, 'Você não pode remover sua própria conta.'); User::whereKey($id)->delete(); }
};
@endphp

@php($usuarios = User::withCount('receitas')->orderBy('name')->get())

<div><section class="admin-panel"><div class="admin-panel-heading"><div><span class="eyebrow">Comunidade</span><h2>Usuários</h2></div><span>{{ $usuarios->count() }} cadastrados</span></div><div class="admin-table">@foreach($usuarios as $user)<div class="admin-table-row"><div><strong>{{ $user->name }}</strong><span>{{ $user->email }} · {{ $user->receitas_count }} receitas</span></div><div class="admin-actions">@if($user->is_admin)<em class="admin-badge">Administrador</em><button wire:click="rebaixar({{ $user->id }})">Rebaixar</button>@else<button wire:click="promover({{ $user->id }})">Promover admin</button>@endif@if($user->id !== auth()->id())<button wire:click="remover({{ $user->id }})" wire:confirm="Remover este usuário?">Remover</button>@endif</div></div>@endforeach</div></section></div>
