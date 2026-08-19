<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceitaFonte extends Model
{
    protected $fillable = ['receita_id', 'user_id', 'tipo', 'nome_fonte', 'url', 'observacoes'];

    public function receita(): BelongsTo
    {
        return $this->belongsTo(Receita::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
