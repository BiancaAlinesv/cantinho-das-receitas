<section class="share-section" x-data="{
    url: @js($this->urlReceita()),
    copiando: false,
    async copiarLink() {
        this.copiando = true;
        try {
            if (navigator.clipboard) {
                await navigator.clipboard.writeText(this.url);
            } else {
                const campo = document.createElement('textarea');
                campo.value = this.url;
                campo.setAttribute('readonly', '');
                campo.style.position = 'fixed';
                campo.style.opacity = '0';
                document.body.appendChild(campo);
                campo.select();
                if (!document.execCommand('copy')) throw new Error('A cópia foi recusada pelo navegador.');
                campo.remove();
            }
            $wire.registrar('link');
        } catch (erro) {
            console.error('Não foi possível copiar o link.', erro);
            window.alert('Não foi possível copiar o link. Tente novamente.');
        } finally {
            this.copiando = false;
        }
    }
}">
    <div class="detail-section-heading"><div><span class="eyebrow">Espalhe o carinho</span><h2>Compartilhar</h2></div><span class="share-total">{{ $total }} compartilhamentos</span></div>
    <div class="share-buttons">
        <a wire:click="registrar('whatsapp')" target="_blank" rel="noopener" href="https://wa.me/?text={{ urlencode($receita->titulo.' - '.$this->urlReceita()) }}" class="share-button whatsapp"><span>◉</span> WhatsApp</a>
        <a wire:click="registrar('facebook')" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($this->urlReceita()) }}" class="share-button facebook"><span>f</span> Facebook</a>
        <a wire:click="registrar('telegram')" target="_blank" rel="noopener" href="https://t.me/share/url?url={{ urlencode($this->urlReceita()) }}&text={{ urlencode($receita->titulo) }}" class="share-button telegram"><span>➤</span> Telegram</a>
        <button x-on:click.prevent="copiarLink()" type="button" class="share-button link" x-bind:disabled="copiando"><span>↗</span> {{ $linkCopiado ? 'Link copiado!' : 'Copiar link' }}</button>
    </div>
</section>
