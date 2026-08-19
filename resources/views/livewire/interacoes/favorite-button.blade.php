<button wire:click="toggle" wire:loading.attr="disabled" wire:target="toggle" type="button" class="{{ $compact ? 'recipe-save' : 'social-button' }} {{ $favorited ? 'is-saved' : '' }}" aria-pressed="{{ $favorited ? 'true' : 'false' }}" aria-label="{{ $favorited ? 'Remover dos favoritos' : 'Adicionar aos favoritos' }}">
    <span aria-hidden="true">{{ $favorited ? '★' : '☆' }}</span>@if(! $compact)<span wire:loading.remove wire:target="toggle">{{ $favorited ? 'Salvo' : 'Salvar receita' }}</span><span wire:loading wire:target="toggle">Salvando...</span>@endif
</button>
