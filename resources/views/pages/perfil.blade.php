@php
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Component;
use function Livewire\Volt\layout;

layout('layouts.public');
new class extends Component {
    use Livewire\WithFileUploads;

    public string $name = '';
    public string $bio = '';
    public $avatar = null;
    public string $novaSenha = '';

    public function mount(): void
    {
        $this->name = auth()->user()->name;
        $this->bio = (string) auth()->user()->bio;
    }

    public function salvar(): void
    {
        $data = $this->validate(['name' => ['required', 'string', 'max:255'], 'bio' => ['nullable', 'string', 'max:500'], 'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], 'novaSenha' => ['nullable', 'string', 'min:8']]);
        $userData = ['name' => $data['name'], 'bio' => $data['bio']];
        if ($this->avatar) { $userData['avatar_path'] = $this->avatar->store('avatares', 'public'); }
        if ($this->novaSenha !== '') { $userData['password'] = Hash::make($this->novaSenha); }
        auth()->user()->update($userData);
        $this->reset('novaSenha', 'avatar');
        session()->flash('sucesso', 'Perfil atualizado com sucesso.');
    }
};
@endphp

<div>
<section class="account-header"><div class="container"><span class="eyebrow">Seu cantinho</span><h1>Meu perfil</h1><p>Cuide dos seus dados e conte um pouco sobre você.</p></div></section><section class="account-section container"><form wire:submit="salvar" class="account-form"><div class="profile-preview"><div class="profile-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div><div><strong>{{ auth()->user()->name }}</strong><span>{{ auth()->user()->email }}</span></div></div>@if(session('sucesso'))<div class="success-message">{{ session('sucesso') }}</div>@endif<div class="form-field"><label for="name">Nome *</label><input id="name" type="text" wire:model="name">@error('name')<small class="form-error">{{ $message }}</small>@enderror</div><div class="form-field"><label for="bio">Bio</label><textarea id="bio" wire:model="bio" rows="4" placeholder="O que você gosta de cozinhar?"></textarea>@error('bio')<small class="form-error">{{ $message }}</small>@enderror</div><div class="form-field"><label for="avatar">Foto de perfil</label><input id="avatar" type="file" wire:model="avatar" accept="image/*">@error('avatar')<small class="form-error">{{ $message }}</small>@enderror</div><div class="form-field"><label for="novaSenha">Nova senha</label><input id="novaSenha" type="password" wire:model="novaSenha" placeholder="Deixe em branco para manter a atual">@error('novaSenha')<small class="form-error">{{ $message }}</small>@enderror</div><div class="account-actions"><a class="text-link" href="{{ route('minhas-receitas') }}">Minhas receitas →</a><button class="button" type="submit">Salvar alterações <span>→</span></button></div></form></section>
</div>
