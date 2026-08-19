<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Receita extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'categoria_id', 'titulo', 'slug', 'descricao', 'foto_principal_path',
        'tempo_preparo_min', 'tempo_cozimento_min', 'porcoes', 'custo', 'dificuldade',
        'rendimento', 'dicas', 'variacoes', 'observacoes', 'video_url', 'status', 'published_at',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'nota_media' => 'decimal:2'];
    }

    protected static function booted(): void
    {
        static::creating(function (Receita $receita): void {
            $receita->slug ??= static::gerarSlugUnico($receita->titulo);
        });
    }

    public static function gerarSlugUnico(string $titulo): string
    {
        $base = Str::slug($titulo);
        $slug = $base;
        $counter = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }
        return $slug;
    }

    public function getRouteKeyName(): string { return 'slug'; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function categoria(): BelongsTo { return $this->belongsTo(Categoria::class); }
    public function ingredientes(): HasMany { return $this->hasMany(ReceitaIngrediente::class)->orderBy('ordem'); }
    public function passos(): HasMany { return $this->hasMany(ModoPreparoPasso::class)->orderBy('ordem'); }
    public function avaliacoes(): HasMany { return $this->hasMany(Avaliacao::class); }
    public function comentarios(): HasMany { return $this->hasMany(Comentario::class); }
    public function fonte(): HasOne { return $this->hasOne(ReceitaFonte::class); }
    public function estadosUsuarios(): HasMany { return $this->hasMany(ReceitaUserStatus::class); }
    public function tempoTotalMin(): int { return $this->tempo_preparo_min + (int) $this->tempo_cozimento_min; }

    public function getFotoUrlAttribute(): ?string
    {
        if (! $this->foto_principal_path) return null;

        return Str::startsWith($this->foto_principal_path, ['http://', 'https://'])
            ? $this->foto_principal_path
            : Storage::disk('public')->url($this->foto_principal_path);
    }

    public function scopePublicadas(Builder $query): Builder
    {
        return $query->where('status', 'publicada')->whereNotNull('published_at');
    }
}
