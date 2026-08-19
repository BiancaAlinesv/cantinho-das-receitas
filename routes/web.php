<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Receita;
use App\Http\Controllers\SitemapController;
use App\Livewire\Receitas\CreateRecipe;
use App\Livewire\Receitas\EditRecipe;
use App\Livewire\Receitas\MyRecipes;

Route::view('/', 'home')->name('inicio');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/receitas', function (Request $request) {
    $filtros = $request->only(['categoria', 'dificuldade', 'tempo', 'ordenar']);

    return view('recipes.index', [
        'recipes' => \App\Support\RecipeCatalog::search($request->string('busca')->toString(), $filtros),
        'term' => $request->string('busca')->toString(),
        'filtros' => $filtros,
        'categorias' => \App\Support\RecipeCatalog::categories(),
    ]);
})->name('receitas.listar');

Route::middleware('auth')->group(function (): void {
    Route::livewire('/receitas/criar', CreateRecipe::class)->name('receitas.criar');
    Route::livewire('/receitas/guardar', 'receitas.save-recipe')->name('receitas.guardar');
    Route::livewire('/receitas/{receita:slug}/editar', EditRecipe::class)->name('receitas.editar');
    Route::livewire('/minhas-receitas', MyRecipes::class)->name('minhas-receitas');
    Route::get('/favoritos', function () {
        $favoritos = auth()->user()->favoritos()->with('receita.categoria')->latest()->get()->pluck('receita')->filter();

        return view('recipes.favorites', compact('favoritos'));
    })->name('favoritos');
    Route::livewire('/perfil', 'perfil')->name('perfil');
});

Route::get('/receitas/{receita:slug}', function (Receita $receita) {
    abort_unless($receita->status === 'publicada' && $receita->published_at !== null, 404);

    return view('pages.receitas.mostrar', compact('receita'));
})->name('receitas.mostrar');

Route::get('dashboard', function () {
    $usuario = auth()->user();

    return view('dashboard', [
        'resumo' => [
            'total' => $usuario->receitas()->count(),
            'publicadas' => $usuario->receitas()->where('status', 'publicada')->count(),
            'rascunhos' => $usuario->receitas()->where('status', 'rascunho')->count(),
            'favoritas' => $usuario->favoritos()->count(),
        ],
        'recentes' => $usuario->receitas()->with('categoria')->latest()->limit(3)->get(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
