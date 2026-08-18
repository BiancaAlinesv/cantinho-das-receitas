<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    protected $fillable = ['nome', 'slug', 'icone'];

    public function receitas(): HasMany
    {
        return $this->hasMany(Receita::class);
    }
}
