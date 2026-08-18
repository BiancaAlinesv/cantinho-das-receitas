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
            ['title' => 'Bolo de cenoura com cobertura de chocolate', 'category' => 'Doces', 'time' => '45 min', 'difficulty' => 'Fácil', 'rating' => '4,9', 'reviews' => 128, 'image' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=900&q=85', 'accent' => 'from-orange-950/10'],
            ['title' => 'Risoto cremoso de cogumelos', 'category' => 'Massas e grãos', 'time' => '35 min', 'difficulty' => 'Médio', 'rating' => '4,8', 'reviews' => 86, 'image' => 'https://images.unsplash.com/photo-1476124369491-e7addf5db371?auto=format&fit=crop&w=900&q=85', 'accent' => 'from-stone-950/10'],
            ['title' => 'Pão de queijo mineiro', 'category' => 'Lanches', 'time' => '30 min', 'difficulty' => 'Fácil', 'rating' => '5,0', 'reviews' => 214, 'image' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=900&q=85', 'accent' => 'from-yellow-950/10'],
            ['title' => 'Salada de grão-de-bico', 'category' => 'Saudáveis', 'time' => '20 min', 'difficulty' => 'Fácil', 'rating' => '4,7', 'reviews' => 64, 'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=900&q=85', 'accent' => 'from-emerald-950/10'],
            ['title' => 'Frango assado com ervas', 'category' => 'Almoço', 'time' => '1h 10 min', 'difficulty' => 'Fácil', 'rating' => '4,9', 'reviews' => 97, 'image' => 'https://images.unsplash.com/photo-1532550907401-a500c9a57435?auto=format&fit=crop&w=900&q=85', 'accent' => 'from-red-950/10'],
            ['title' => 'Torta de limão da família', 'category' => 'Doces', 'time' => '50 min', 'difficulty' => 'Médio', 'rating' => '4,8', 'reviews' => 152, 'image' => 'https://images.unsplash.com/photo-1565958011703-44f9829ba187?auto=format&fit=crop&w=900&q=85', 'accent' => 'from-lime-950/10'],
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function search(string $term = ''): array
    {
        if (self::databaseIsReady()) {
            $query = Receita::query()->publicadas()->with('categoria');
            if (trim($term) !== '') {
                $like = '%'.trim($term).'%';
                $query->where(fn ($inner) => $inner->where('titulo', 'like', $like)->orWhere('descricao', 'like', $like)->orWhereHas('categoria', fn ($category) => $category->where('nome', 'like', $like)));
            }
            $recipes = $query->latest('published_at')->get()->map(fn (Receita $recipe): array => self::toCard($recipe))->all();
            if ($recipes !== [] || trim($term) !== '') {
                return $recipes;
            }
        }

        if (trim($term) === '') {
            return self::all();
        }

        return array_values(array_filter(self::all(), fn (array $recipe): bool =>
            str_contains(mb_strtolower($recipe['title'].' '.$recipe['category']), mb_strtolower(trim($term)))
        ));
    }

    /** @return list<array{name: string, icon: string, count: string}> */
    public static function categories(): array
    {
        if (self::databaseIsReady()) {
            $categories = Cache::remember('catalog:home:categories', now()->addMinutes(10), fn (): array => Categoria::query()->withCount('receitas')->orderBy('nome')->get()->map(fn (Categoria $category): array => ['name' => $category->nome, 'icon' => $category->icone ?: '✦', 'count' => $category->receitas_count.' receitas'])->all());
            if ($categories !== []) {
                return $categories;
            }
        }

        return [
            ['name' => 'Doces', 'icon' => '✦', 'count' => '124 receitas'],
            ['name' => 'Almoço', 'icon' => '◒', 'count' => '98 receitas'],
            ['name' => 'Lanches', 'icon' => '⌁', 'count' => '76 receitas'],
            ['name' => 'Saudáveis', 'icon' => '❋', 'count' => '54 receitas'],
            ['name' => 'Massas e grãos', 'icon' => '◌', 'count' => '42 receitas'],
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
