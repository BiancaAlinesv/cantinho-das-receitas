<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Avaliacao extends Model
{
    protected $table = 'avaliacoes';
    protected $fillable = ['receita_id', 'user_id', 'nota'];
    protected function casts(): array { return ['nota' => 'integer']; }
    public function receita(): BelongsTo { return $this->belongsTo(Receita::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
