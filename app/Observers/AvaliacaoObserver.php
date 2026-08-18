<?php

namespace App\Observers;

use App\Models\Avaliacao;
use App\Models\Receita;

class AvaliacaoObserver
{
    public function saved(Avaliacao $avaliacao): void { $this->recalcular($avaliacao->receita_id); }
    public function deleted(Avaliacao $avaliacao): void { $this->recalcular($avaliacao->receita_id); }

    private function recalcular(int $receitaId): void
    {
        $stats = Avaliacao::where('receita_id', $receitaId)->selectRaw('AVG(nota) as media, COUNT(*) as total')->first();
        Receita::whereKey($receitaId)->update(['nota_media' => round((float) ($stats->media ?? 0), 2), 'total_avaliacoes' => (int) ($stats->total ?? 0)]);
    }
}
