<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceitaUserStatus extends Model
{
    protected $fillable = ['user_id', 'receita_id', 'status'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function receita(): BelongsTo { return $this->belongsTo(Receita::class); }
}
