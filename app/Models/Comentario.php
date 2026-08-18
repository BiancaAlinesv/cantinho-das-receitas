<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comentario extends Model
{
    protected $fillable = ['receita_id', 'user_id', 'comentario_pai_id', 'conteudo', 'status'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function receita(): BelongsTo { return $this->belongsTo(Receita::class); }
    public function respostas(): HasMany { return $this->hasMany(self::class, 'comentario_pai_id'); }
}
