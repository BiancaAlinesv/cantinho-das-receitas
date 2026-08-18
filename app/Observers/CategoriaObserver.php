<?php

namespace App\Observers;

use App\Models\Categoria;
use App\Support\RecipeCatalog;

class CategoriaObserver
{
    public function saved(Categoria $categoria): void { RecipeCatalog::clearCache(); }
    public function deleted(Categoria $categoria): void { RecipeCatalog::clearCache(); }
}
