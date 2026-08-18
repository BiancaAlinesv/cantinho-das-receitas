<?php

namespace App\Providers;

use App\Models\Avaliacao;
use App\Models\Categoria;
use App\Models\Receita;
use App\Observers\CategoriaObserver;
use App\Observers\AvaliacaoObserver;
use App\Observers\ReceitaObserver;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Avaliacao::observe(AvaliacaoObserver::class);
        Categoria::observe(CategoriaObserver::class);
        Receita::observe(ReceitaObserver::class);
    }
}
