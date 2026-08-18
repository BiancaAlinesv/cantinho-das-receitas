<button wire:click="toggle" type="button" class="social-button {{ $favorited ? 'is-saved' : '' }}">
    <span>{{ $favorited ? '★' : '☆' }}</span> {{ $favorited ? 'Salvo' : 'Salvar receita' }}
</button>
