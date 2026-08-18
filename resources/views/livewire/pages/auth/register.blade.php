<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register(): void
    {
        $validated = $this->validate(['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class], 'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()]]);
        $validated['password'] = Hash::make($validated['password']);
        event(new Registered($user = User::create($validated)));
        Auth::login($user);
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="auth-card"><div class="auth-heading"><span class="eyebrow">Faça parte da comunidade</span><h2>Crie sua conta</h2><p>Guarde suas receitas favoritas e compartilhe as suas com a comunidade.</p></div><form wire:submit="register" class="auth-form"><div class="auth-field"><label for="name">Nome</label><input wire:model="name" id="name" type="text" name="name" required autofocus autocomplete="name" placeholder="Como podemos chamar você?"><x-input-error :messages="$errors->get('name')" /></div><div class="auth-field"><label for="email">E-mail</label><input wire:model="email" id="email" type="email" name="email" required autocomplete="username" placeholder="voce@exemplo.com"><x-input-error :messages="$errors->get('email')" /></div><div class="auth-field"><label for="password">Senha</label><input wire:model="password" id="password" type="password" name="password" required autocomplete="new-password" placeholder="Mínimo de 8 caracteres"><x-input-error :messages="$errors->get('password')" /></div><div class="auth-field"><label for="password_confirmation">Confirmar senha</label><input wire:model="password_confirmation" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Digite a senha novamente"><x-input-error :messages="$errors->get('password_confirmation')" /></div><button class="button auth-submit" type="submit">Criar minha conta <span>→</span></button></form><p class="auth-switch">Já tem uma conta? <a href="{{ route('login') }}" wire:navigate>Entrar</a></p></div>
