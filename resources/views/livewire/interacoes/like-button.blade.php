<button wire:click="toggle" wire:loading.attr="disabled" wire:target="toggle" type="button" class="social-button {{ $liked ? 'is-liked' : '' }}" aria-pressed="{{ $liked ? 'true' : 'false' }}" aria-label="{{ $liked ? 'Remover curtida' : 'Curtir receita' }}">
    <span aria-hidden="true">{{ $liked ? '♥' : '♡' }}</span> <span wire:loading.remove wire:target="toggle">Gostei <small>({{ $total }})</small></span><span wire:loading wire:target="toggle">Salvando...</span>
</button>
