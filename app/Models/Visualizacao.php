<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visualizacao extends Model
{
    protected $fillable = ['receita_id', 'user_id', 'ip_hash'];

    public function receita(): BelongsTo { return $this->belongsTo(Receita::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
