<?php

namespace App\Services;

use App\Models\Receita;
use Illuminate\Support\Collection;

class ReceitaRelacionadaService
{
    public function buscar(Receita $receita, int $limite = 4): Collection
    {
        $sameCategory = Receita::query()->publicadas()->whereKeyNot($receita->id)->where('categoria_id', $receita->categoria_id)->with('categoria')->latest('published_at')->take($limite)->get();
        $ingredientIds = $receita->ingredientes->pluck('ingrediente_id');
        $sameIngredients = $ingredientIds->isEmpty() ? collect() : Receita::query()->publicadas()->whereKeyNot($receita->id)->whereHas('ingredientes', fn ($query) => $query->whereIn('ingrediente_id', $ingredientIds))->with('categoria')->latest('published_at')->take($limite)->get();
        $popular = Receita::query()->publicadas()->whereKeyNot($receita->id)->with('categoria')->orderByDesc('visualizacoes_total')->take($limite)->get();

        return $sameCategory->concat($sameIngredients)->concat($popular)->unique('id')->take($limite)->values();
    }
}
