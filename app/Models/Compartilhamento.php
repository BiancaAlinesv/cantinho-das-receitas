<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Compartilhamento extends Model
{
    public $timestamps = false;
    protected $fillable = ['receita_id', 'canal', 'created_at'];
    protected function casts(): array { return ['created_at' => 'datetime']; }
    public function receita(): BelongsTo { return $this->belongsTo(Receita::class); }
}
