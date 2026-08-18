<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['Bolos', '✦'], ['Doces', '✦'], ['Almoço', '◒'], ['Lanches', '⌁'],
            ['Saudáveis', '❋'], ['Massas e grãos', '◌'], ['Carnes', '◉'], ['Bebidas', '◒'],
        ] as [$nome, $icone]) {
            Categoria::updateOrCreate(['slug' => Str::slug($nome)], ['nome' => $nome, 'icone' => $icone]);
        }
    }
}
