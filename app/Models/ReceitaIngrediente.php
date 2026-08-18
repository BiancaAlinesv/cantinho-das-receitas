<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceitaIngrediente extends Model
{
    public $timestamps = false;

    protected $fillable = ['receita_id', 'ingrediente_id', 'quantidade', 'unidade', 'observacao', 'ordem'];

    protected function casts(): array
    {
        return ['quantidade' => 'decimal:2'];
    }

    public function ingrediente(): BelongsTo
    {
        return $this->belongsTo(Ingrediente::class);
    }
}
