<?php

namespace App\Observers;

use App\Models\Receita;
use App\Support\RecipeCatalog;

class ReceitaObserver
{
    public function saved(Receita $receita): void { RecipeCatalog::clearCache(); }
    public function deleted(Receita $receita): void { RecipeCatalog::clearCache(); }
}
