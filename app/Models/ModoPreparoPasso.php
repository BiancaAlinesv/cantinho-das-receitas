<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModoPreparoPasso extends Model
{
    public $timestamps = false;

    protected $fillable = ['receita_id', 'ordem', 'descricao'];
}
