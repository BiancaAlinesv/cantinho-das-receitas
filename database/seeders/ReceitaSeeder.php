<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Receita;
use App\Models\Ingrediente;
use App\Models\ReceitaIngrediente;
use App\Models\ModoPreparoPasso;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ReceitaSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->first() ?? User::factory()->create(['name' => 'Cozinheiro do Cantinho']);

        $recipes = [
            ['Bolo de cenoura com cobertura de chocolate', 'Doces', 'Bolo fofinho, com sabor de infância e uma cobertura generosa de chocolate.', 25, 20, 10, 'facil', 'baixo', 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=900&q=85', 4.9, 128],
            ['Risoto cremoso de cogumelos', 'Massas e grãos', 'Um risoto cremoso e acolhedor para um jantar especial sem complicação.', 10, 25, 4, 'medio', 'medio', 'https://images.unsplash.com/photo-1476124369491-e7addf5db371?auto=format&fit=crop&w=900&q=85', 4.8, 86],
            ['Pão de queijo mineiro', 'Lanches', 'Casquinha dourada por fora, macio por dentro e impossível comer só um.', 15, 15, 25, 'facil', 'baixo', 'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=900&q=85', 5.0, 214],
            ['Salada de grão-de-bico', 'Saudáveis', 'Colorida, fresca e cheia de textura para deixar a rotina mais leve.', 20, 0, 4, 'facil', 'baixo', 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=900&q=85', 4.7, 64],
            ['Frango assado com ervas', 'Almoço', 'Aquele almoço de domingo que perfuma a casa inteira.', 15, 55, 6, 'facil', 'medio', 'https://images.unsplash.com/photo-1532550907401-a500c9a57435?auto=format&fit=crop&w=900&q=85', 4.9, 97],
            ['Torta de limão da família', 'Doces', 'Cremosa, equilibrada e com aquele toque cítrico irresistível.', 30, 20, 8, 'medio', 'medio', 'https://images.unsplash.com/photo-1565958011703-44f9829ba187?auto=format&fit=crop&w=900&q=85', 4.8, 152],
        ];

        foreach ($recipes as [$title, $category, $description, $prep, $cook, $servings, $difficulty, $cost, $image, $rating, $reviews]) {
            $categoryId = Categoria::where('slug', Str::slug($category))->value('id');
            $recipe = Receita::updateOrCreate(
                ['slug' => Str::slug($title)],
                ['user_id' => $user->id, 'categoria_id' => $categoryId, 'titulo' => $title, 'descricao' => $description, 'foto_principal_path' => $image, 'tempo_preparo_min' => $prep, 'tempo_cozimento_min' => $cook, 'porcoes' => $servings, 'dificuldade' => $difficulty, 'custo' => $cost, 'status' => 'publicada', 'published_at' => now(), 'nota_media' => $rating, 'total_avaliacoes' => $reviews]
            );

            $details = self::detailsFor($title);
            $recipe->ingredientes()->delete();
            foreach ($details['ingredients'] as $order => [$name, $amount, $unit, $note]) {
                $ingredient = Ingrediente::firstOrCreate(['nome' => $name]);
                ReceitaIngrediente::create(['receita_id' => $recipe->id, 'ingrediente_id' => $ingredient->id, 'quantidade' => $amount, 'unidade' => $unit, 'observacao' => $note, 'ordem' => $order]);
            }
            $recipe->passos()->delete();
            foreach ($details['steps'] as $order => $step) {
                ModoPreparoPasso::create(['receita_id' => $recipe->id, 'ordem' => $order + 1, 'descricao' => $step]);
            }
        }
    }

    /** @return array{ingredients: list<array{string, float|null, string, string|null}>, steps: list<string>} */
    private static function detailsFor(string $title): array
    {
        return match ($title) {
            'Risoto cremoso de cogumelos' => ['ingredients' => [['arroz arbóreo', 300, 'g', null], ['cogumelos frescos', 250, 'g', 'fatiados'], ['caldo de legumes', 1, 'l', 'quente'], ['cebola', 1, 'unidade', 'picada'], ['queijo parmesão', 80, 'g', 'ralado']], 'steps' => ['Aqueça o caldo e mantenha-o em fogo baixo.', 'Refogue a cebola, junte o arroz e mexa por dois minutos.', 'Adicione o caldo aos poucos, mexendo até o arroz ficar cremoso.', 'Finalize com os cogumelos e o parmesão.']],
            'Pão de queijo mineiro' => ['ingredients' => [['polvilho doce', 500, 'g', null], ['leite', 250, 'ml', null], ['óleo', 100, 'ml', null], ['ovos', 2, 'unidade', null], ['queijo meia cura', 300, 'g', 'ralado']], 'steps' => ['Ferva o leite com o óleo e despeje sobre o polvilho.', 'Misture e espere amornar. Junte os ovos e o queijo.', 'Modele as bolinhas e asse a 200 °C até dourarem.']],
            'Salada de grão-de-bico' => ['ingredients' => [['grão-de-bico cozido', 400, 'g', null], ['tomate-cereja', 200, 'g', 'cortado ao meio'], ['pepino', 1, 'unidade', 'em cubos'], ['azeite', 3, 'colher_sopa', null], ['limão', 1, 'unidade', 'suco']], 'steps' => ['Misture o grão-de-bico, os tomates e o pepino.', 'Tempere com azeite, limão, sal e pimenta.', 'Leve à geladeira por quinze minutos antes de servir.']],
            'Frango assado com ervas' => ['ingredients' => [['sobrecoxas de frango', 1, 'kg', null], ['alho', 4, 'unidade', 'amassados'], ['azeite', 2, 'colher_sopa', null], ['ervas frescas', null, 'a_gosto', null]], 'steps' => ['Tempere o frango com alho, ervas, azeite, sal e pimenta.', 'Cubra e deixe marinar por pelo menos trinta minutos.', 'Asse em forno preaquecido a 200 °C até dourar e ficar macio.']],
            'Torta de limão da família' => ['ingredients' => [['biscoito maisena', 200, 'g', 'triturado'], ['manteiga', 100, 'g', 'derretida'], ['leite condensado', 395, 'g', null], ['creme de leite', 200, 'g', null], ['limão', 3, 'unidade', 'suco']], 'steps' => ['Misture o biscoito com a manteiga e forre o fundo da forma.', 'Bata o leite condensado, o creme de leite e o suco de limão.', 'Despeje sobre a base e leve à geladeira por quatro horas.']],
            default => ['ingredients' => [['cenouras', 3, 'unidade', 'picadas'], ['ovos', 3, 'unidade', null], ['óleo', 120, 'ml', null], ['farinha de trigo', 250, 'g', 'peneirada'], ['açúcar', 300, 'g', null], ['chocolate meio amargo', 150, 'g', 'para a cobertura']], 'steps' => ['Bata as cenouras, os ovos e o óleo até formar um creme.', 'Misture os ingredientes secos e incorpore o creme delicadamente.', 'Asse a 180 °C por aproximadamente trinta e cinco minutos.', 'Cubra com o chocolate derretido e sirva.']],
        };
    }
}
