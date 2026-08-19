<?php

namespace App\Support;

use App\Models\Categoria;
use App\Models\Receita;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

final class RecipeCatalog
{
    /** @return list<array<string, mixed>> */
    public static function all(): array
    {
        if (self::databaseIsReady()) {
            $recipes = Cache::remember('catalog:home:recipes', now()->addMinutes(10), fn (): array => Receita::query()->publicadas()->with('categoria')->latest('published_at')->get()->map(fn (Receita $recipe): array => self::toCard($recipe))->all());
            if ($recipes !== []) {
                return $recipes;
            }
        }

        return [
            ['title' => 'Bolo de cenoura com cobertura de chocolate', 'category' => 'Doces', 'time' => '45 min', 'difficulty' => 'Fácil', 'rating' => '—', 'reviews' => 0, 'image' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=900&q=85', 'accent' => 'from-orange-950/10'],
            ['title' => 'Risoto cremoso de cogumelos', 'category' => 'Massas e grãos', 'time' => '35 min', 'difficulty' => 'Médio', 'rating' => '—', 'reviews' => 0, 'image' => 'https://images.unsplash.com/photo-1476124369491-e7addf5db371?auto=format&fit=crop&w=900&q=85', 'accent' => 'from-stone-950/10'],
            ['title' => 'Pão de queijo mineiro', 'category' => 'Lanches', 'time' => '30 min', 'difficulty' => 'Fácil', 'rating' => '—', 'reviews' => 0, 'image' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=900&q=85', 'accent' => 'from-yellow-950/10'],
            ['title' => 'Salada de grão-de-bico', 'category' => 'Saudáveis', 'time' => '20 min', 'difficulty' => 'Fácil', 'rating' => '—', 'reviews' => 0, 'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=900&q=85', 'accent' => 'from-emerald-950/10'],
            ['title' => 'Frango assado com ervas', 'category' => 'Almoço', 'time' => '1h 10 min', 'difficulty' => 'Fácil', 'rating' => '—', 'reviews' => 0, 'image' => 'https://images.unsplash.com/photo-1532550907401-a500c9a57435?auto=format&fit=crop&w=900&q=85', 'accent' => 'from-red-950/10'],
            ['title' => 'Torta de limão da família', 'category' => 'Doces', 'time' => '50 min', 'difficulty' => 'Médio', 'rating' => '—', 'reviews' => 0, 'image' => 'https://images.unsplash.com/photo-1565958011703-44f9829ba187?auto=format&fit=crop&w=900&q=85', 'accent' => 'from-lime-950/10'],
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function search(string $term = '', array $filters = []): array
    {
        if (self::databaseIsReady()) {
            $query = Receita::query()->publicadas()->with('categoria');
            if (trim($term) !== '') {
                $like = '%'.trim($term).'%';
                $query->where(fn ($inner) => $inner
                    ->whereRaw('LOWER(titulo) LIKE LOWER(?)', [$like])
                    ->orWhereRaw('LOWER(descricao) LIKE LOWER(?)', [$like])
                    ->orWhereHas('categoria', fn ($category) => $category->whereRaw('LOWER(nome) LIKE LOWER(?)', [$like]))
                    ->orWhereHas('ingredientes.ingrediente', fn ($ingredient) => $ingredient->whereRaw('LOWER(nome) LIKE LOWER(?)', [$like]))
                );
            }
            if (filled($filters['categoria'] ?? null)) {
                $query->whereHas('categoria', fn ($category) => $category->where('nome', $filters['categoria']));
            }
            if (in_array($filters['dificuldade'] ?? null, ['facil', 'medio', 'dificil'], true)) {
                $query->where('dificuldade', $filters['dificuldade']);
            }
            if (($filters['tempo'] ?? null) === '30') {
                $query->whereRaw('(tempo_preparo_min + COALESCE(tempo_cozimento_min, 0)) <= 30');
            } elseif (($filters['tempo'] ?? null) === '60') {
                $query->whereRaw('(tempo_preparo_min + COALESCE(tempo_cozimento_min, 0)) <= 60');
            }

            match ($filters['ordenar'] ?? 'recentes') {
                'antigas' => $query->oldest('published_at'),
                'avaliadas' => $query->orderByDesc('nota_media')->orderByDesc('total_avaliacoes'),
                'populares' => $query->orderByDesc('visualizacoes_total'),
                'alfabetica' => $query->orderBy('titulo'),
                default => $query->latest('published_at'),
            };

            $recipes = $query->get()->map(fn (Receita $recipe): array => self::toCard($recipe))->all();
            if ($recipes !== [] || trim($term) !== '') {
                return $recipes;
            }
        }

        if (trim($term) === '') {
            return self::all();
        }

        $recipes = array_values(array_filter(self::all(), function (array $recipe) use ($term, $filters): bool {
            $matchesTerm = trim($term) === '' || str_contains(mb_strtolower($recipe['title'].' '.$recipe['category']), mb_strtolower(trim($term)));
            $matchesCategory = blank($filters['categoria'] ?? null) || $recipe['category'] === $filters['categoria'];
            $matchesDifficulty = blank($filters['dificuldade'] ?? null) || mb_strtolower($recipe['difficulty']) === match ($filters['dificuldade']) {
                'facil' => 'fácil',
                'medio' => 'médio',
                'dificil' => 'difícil',
                default => mb_strtolower($recipe['difficulty']),
            };
            return $matchesTerm && $matchesCategory && $matchesDifficulty;
        }));

        return match ($filters['ordenar'] ?? 'recentes') {
            'alfabetica' => collect($recipes)->sortBy('title')->values()->all(),
            default => $recipes,
        };
    }

    /** @return list<array{name: string, icon: string, count: int}> */
    public static function categories(): array
    {
        if (self::databaseIsReady()) {
            $categories = Cache::remember('catalog:home:categories', now()->addMinutes(10), fn (): array => Categoria::query()->withCount('receitas')->orderBy('nome')->get()->map(fn (Categoria $category): array => ['name' => $category->nome, 'icon' => $category->icone ?: '✦', 'count' => (int) $category->receitas_count])->all());
            if ($categories !== []) {
                return $categories;
            }
        }

        return [
            ['name' => 'Doces', 'icon' => '✦', 'count' => 0],
            ['name' => 'Almoço', 'icon' => '◒', 'count' => 0],
            ['name' => 'Lanches', 'icon' => '⌁', 'count' => 0],
            ['name' => 'Saudáveis', 'icon' => '❋', 'count' => 0],
            ['name' => 'Massas e grãos', 'icon' => '◌', 'count' => 0],
        ];
    }

    private static function databaseIsReady(): bool
    {
        try {
            return Schema::hasTable('receitas');
        } catch (\Throwable) {
            return false;
        }
    }

    public static function clearCache(): void
    {
        Cache::forget('catalog:home:recipes');
        Cache::forget('catalog:home:categories');
    }

    /** @return array<string, mixed> */
    private static function toCard(Receita $recipe): array
    {
        return ['title' => $recipe->titulo, 'slug' => $recipe->slug, 'category' => $recipe->categoria?->nome ?? 'Receitas', 'time' => $recipe->tempoTotalMin().' min', 'difficulty' => ucfirst($recipe->dificuldade), 'rating' => number_format((float) $recipe->nota_media, 1, ',', '.'), 'reviews' => $recipe->total_avaliacoes, 'image' => $recipe->foto_url ?: 'https://placehold.co/900x600/f3eadf/bd5b32?text=Receita'];
    }
}
