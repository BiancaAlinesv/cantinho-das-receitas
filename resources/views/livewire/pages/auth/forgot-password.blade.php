<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    public function sendPasswordResetLink(): void
    {
        $this->validate(['email' => ['required', 'string', 'email']]);
        $status = Password::sendResetLink($this->only('email'));
        if ($status !== Password::RESET_LINK_SENT) { $this->addError('email', __($status)); return; }
        $this->reset('email');
        session()->flash('status', __($status));
    }
}; ?>

<div class="auth-card"><div class="auth-heading"><span class="eyebrow">Recupere o acesso</span><h2>Esqueceu sua senha?</h2><p>Informe seu e-mail e enviaremos um link para você criar uma nova senha.</p></div><x-auth-session-status class="auth-status" :status="session('status')" /><form wire:submit="sendPasswordResetLink" class="auth-form"><div class="auth-field"><label for="email">E-mail</label><input wire:model="email" id="email" type="email" name="email" required autofocus autocomplete="email" placeholder="voce@exemplo.com"><x-input-error :messages="$errors->get('email')" /></div><button class="button auth-submit" type="submit">Enviar link de recuperação <span>→</span></button></form><p class="auth-switch"><a href="{{ route('login') }}" wire:navigate>← Voltar para o login</a></p></div>
