<button wire:click="toggle" type="button" class="social-button {{ $liked ? 'is-liked' : '' }}">
    <span>{{ $liked ? '♥' : '♡' }}</span> Gostei <small>({{ $total }})</small>
</button>
