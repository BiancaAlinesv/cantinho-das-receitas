<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::view('/', 'pages.admin.dashboard')->name('dashboard');
    Route::livewire('/categorias', 'admin.categorias')->name('categorias');
    Route::livewire('/usuarios', 'admin.usuarios')->name('usuarios');
    Route::livewire('/comentarios', 'admin.comentarios')->name('comentarios');
});
