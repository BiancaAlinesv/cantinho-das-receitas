<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();
        $this->form->authenticate();
        Session::regenerate();
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="auth-card">
    <div class="auth-heading"><span class="eyebrow">Bem-vinda de volta</span><h2>Entre na sua conta</h2><p>Continue de onde parou e encontre suas receitas favoritas.</p></div>
    <x-auth-session-status class="auth-status" :status="session('status')" />
    <form wire:submit="login" class="auth-form">
        <div class="auth-field"><label for="email">E-mail</label><input wire:model="form.email" id="email" type="email" name="email" required autofocus autocomplete="username" placeholder="voce@exemplo.com"><x-input-error :messages="$errors->get('form.email')" /></div>
        <div class="auth-field"><div class="auth-label-row"><label for="password">Senha</label><a href="{{ route('password.request') }}" wire:navigate>Esqueci minha senha</a></div><input wire:model="form.password" id="password" type="password" name="password" required autocomplete="current-password" placeholder="Digite sua senha"><x-input-error :messages="$errors->get('form.password')" /></div>
        <label class="auth-check"><input wire:model="form.remember" type="checkbox" name="remember"><span>Manter conectado</span></label>
        <button class="button auth-submit" type="submit">Entrar <span>→</span></button>
    </form>
    <p class="auth-switch">Ainda não tem uma conta? <a href="{{ route('register') }}" wire:navigate>Crie sua conta</a></p>
</div>
