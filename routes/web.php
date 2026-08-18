<?php

use Illuminate\Support\Facades\Route;
use App\Models\Receita;
use App\Http\Controllers\SitemapController;

Route::view('/', 'home')->name('inicio');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/receitas', function (\Illuminate\Http\Request $request) {
    return view('recipes.index', ['recipes' => \App\Support\RecipeCatalog::search($request->string('busca')->toString()), 'term' => $request->string('busca')->toString()]);
})->name('receitas.listar');

Route::middleware('auth')->group(function (): void {
    Route::livewire('/receitas/criar', 'receitas.criar')->name('receitas.criar');
    Route::livewire('/receitas/{receita:slug}/editar', 'receitas.editar')->name('receitas.editar');
    Route::livewire('/minhas-receitas', 'minhas-receitas')->name('minhas-receitas');
    Route::livewire('/perfil', 'perfil')->name('perfil');
});

Route::get('/receitas/{receita:slug}', function (Receita $receita) {
    return view('pages.receitas.mostrar', compact('receita'));
})->name('receitas.mostrar');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
