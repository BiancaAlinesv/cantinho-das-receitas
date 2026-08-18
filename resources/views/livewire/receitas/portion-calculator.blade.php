<section class="recipe-ingredients portion-calculator">
    <div class="portion-calculator-heading">
        <div><span class="eyebrow">Calculadora de porções</span><h2>Ajuste a quantidade da receita</h2><p>As medidas dos ingredientes mudam automaticamente.</p></div>
        <span class="portion-calculator-badge">✦ Livewire</span>
    </div>
    <div class="portion-calculator-control">
        <div><span class="portion-label">Quantas porções?</span><small>Receita original: {{ $receita->porcoes }} porções</small></div>
        <div class="portion-stepper"><button wire:click="diminuir" type="button" aria-label="Diminuir porções">−</button><strong>{{ $porcoesAtuais }}</strong><button wire:click="aumentar" type="button" aria-label="Aumentar porções">+</button></div>
        <div class="portion-factor"><strong>{{ number_format($this->fator, 2, ',', '.') }}x</strong><span>quantidade aplicada</span></div>
    </div>
    <div class="portion-presets"><span>Atalhos:</span><button type="button" wire:click="$set('porcoesAtuais', {{ max(1, (int) round($receita->porcoes / 2)) }})">½ receita</button><button type="button" wire:click="$set('porcoesAtuais', {{ $receita->porcoes }})">Original</button><button type="button" wire:click="$set('porcoesAtuais', {{ min(100, $receita->porcoes * 2) }})">Dobro</button></div>
    <div class="portion-ingredients"><div class="detail-section-heading"><div><span class="eyebrow">Ingredientes recalculados</span><h2>O que você vai precisar</h2></div></div><ul class="ingredient-list">@foreach($this->ingredientesRecalculados() as $ingredient)<li><span class="ingredient-dot"></span><span>{{ $ingredient['quantidade'] !== null ? rtrim(rtrim(number_format($ingredient['quantidade'], 2, ',', '.'), '0'), ',').' '.($ingredient['unidade'] !== 'unidade' ? str_replace('_', ' ', $ingredient['unidade']) : '') : 'A gosto' }} <strong>{{ $ingredient['nome'] }}</strong>@if($ingredient['observacao']) <small>({{ $ingredient['observacao'] }})</small>@endif</span></li>@endforeach</ul></div>
</section>
