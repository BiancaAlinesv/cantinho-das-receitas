<div class="recipe-status" aria-label="Status pessoal da receita">
    <span class="eyebrow">Minha relação com esta receita</span>
    <div class="recipe-status-actions">
        <button type="button" wire:click="definir('quero_fazer')" wire:loading.attr="disabled" wire:target="definir" class="recipe-status-button {{ $statusAtual === 'quero_fazer' ? 'active' : '' }}" aria-pressed="{{ $statusAtual === 'quero_fazer' ? 'true' : 'false' }}">Quero fazer</button>
        <button type="button" wire:click="definir('ja_fiz')" wire:loading.attr="disabled" wire:target="definir" class="recipe-status-button {{ $statusAtual === 'ja_fiz' ? 'active' : '' }}" aria-pressed="{{ $statusAtual === 'ja_fiz' ? 'true' : 'false' }}">Já fiz</button>
    </div>
</div>
