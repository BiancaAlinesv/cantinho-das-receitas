<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Curtida;
use App\Models\Favorito;
use App\Models\Avaliacao;
use App\Models\Comentario;
use App\Models\Compartilhamento;
use App\Models\Receita;
use App\Models\ReceitaFonte;
use App\Models\Ingrediente;
use App\Models\ReceitaIngrediente;
use App\Models\User;
use App\Support\RecipeCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\Interacoes\FavoriteButton;
use App\Livewire\Interacoes\LikeButton;
use App\Livewire\Interacoes\RatingStars;
use App\Livewire\Interacoes\CommentsList;
use App\Livewire\Interacoes\ShareRecipe;
use App\Livewire\Receitas\PortionCalculator;
use App\Livewire\Receitas\CreateRecipe;
use App\Livewire\Receitas\EditRecipe;
use App\Livewire\Receitas\MyRecipes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RecipePlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_recipe_pages_and_sitemap_are_available(): void
    {
        $this->get('/')->assertOk()->assertSee('Cantinho das Receitas')->assertSee('og:locale');
        $this->get('/receitas')->assertOk()->assertSee('Todas as receitas')->assertSee('Encontre receitas caseiras');
        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml');
    }

    public function test_published_recipe_can_be_opened_by_slug(): void
    {
        $user = User::factory()->create();
        $category = Categoria::create(['nome' => 'Doces', 'slug' => 'doces']);
        $recipe = Receita::create([
            'user_id' => $user->id,
            'categoria_id' => $category->id,
            'titulo' => 'Bolo de teste',
            'descricao' => 'Uma receita de teste suficientemente descritiva.',
            'tempo_preparo_min' => 10,
            'tempo_cozimento_min' => 20,
            'porcoes' => 4,
            'custo' => 'baixo',
            'dificuldade' => 'facil',
            'status' => 'publicada',
            'published_at' => now(),
        ]);

        $this->get(route('receitas.mostrar', $recipe))->assertOk()->assertSee($recipe->titulo);
    }

    public function test_draft_recipe_is_not_publicly_accessible(): void
    {
        $user = User::factory()->create();
        $category = Categoria::create(['nome' => 'Doces', 'slug' => 'doces']);
        $recipe = Receita::create([
            'user_id' => $user->id,
            'categoria_id' => $category->id,
            'titulo' => 'Rascunho privado',
            'descricao' => 'Uma receita em rascunho que não deve aparecer publicamente.',
            'tempo_preparo_min' => 10,
            'tempo_cozimento_min' => 20,
            'porcoes' => 4,
            'custo' => 'baixo',
            'dificuldade' => 'facil',
            'status' => 'rascunho',
        ]);

        $this->get(route('receitas.mostrar', $recipe))->assertNotFound();
    }

    public function test_non_admin_cannot_access_admin_panel(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_admin_can_access_admin_panel(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))->get('/admin')->assertOk()->assertSee('Painel administrativo');
    }

    public function test_authenticated_profile_is_available(): void
    {
        $this->actingAs(User::factory()->create())->get('/perfil')->assertOk();
    }

    public function test_authenticated_recipe_list_is_available(): void
    {
        $this->actingAs(User::factory()->create())->get('/minhas-receitas')->assertOk();
    }

    public function test_recipe_catalog_applies_category_and_difficulty_filters(): void
    {
        $user = User::factory()->create();
        $doces = Categoria::create(['nome' => 'Doces', 'slug' => 'doces']);
        $salgados = Categoria::create(['nome' => 'Salgados', 'slug' => 'salgados']);

        Receita::create([
            'user_id' => $user->id, 'categoria_id' => $doces->id, 'titulo' => 'Bolo filtrado',
            'descricao' => 'Uma receita doce suficientemente descritiva para o teste.', 'tempo_preparo_min' => 20,
            'tempo_cozimento_min' => 10, 'porcoes' => 4, 'custo' => 'baixo', 'dificuldade' => 'facil',
            'status' => 'publicada', 'published_at' => now(),
        ]);
        Receita::create([
            'user_id' => $user->id, 'categoria_id' => $salgados->id, 'titulo' => 'Torta não filtrada',
            'descricao' => 'Uma receita salgada suficientemente descritiva para o teste.', 'tempo_preparo_min' => 40,
            'tempo_cozimento_min' => 20, 'porcoes' => 4, 'custo' => 'medio', 'dificuldade' => 'dificil',
            'status' => 'publicada', 'published_at' => now(),
        ]);

        $this->get('/receitas?busca=bolo&categoria=Doces&dificuldade=facil')
            ->assertOk()
            ->assertSee('Bolo filtrado')
            ->assertDontSee('Torta não filtrada');
    }

    public function test_home_category_counts_include_only_published_recipes(): void
    {
        $user = User::factory()->create();
        $category = Categoria::create(['nome' => 'Cadernos', 'slug' => 'cadernos']);
        $dados = [
            'user_id' => $user->id, 'categoria_id' => $category->id, 'descricao' => 'Uma receita suficientemente descritiva para testar a categoria.',
            'tempo_preparo_min' => 10, 'tempo_cozimento_min' => 10, 'porcoes' => 2, 'custo' => 'baixo', 'dificuldade' => 'facil',
        ];

        Receita::create($dados + ['titulo' => 'Receita publicada', 'status' => 'publicada', 'published_at' => now()]);
        Receita::create($dados + ['titulo' => 'Receita em rascunho', 'status' => 'rascunho']);

        RecipeCatalog::clearCache();
        $categoria = collect(RecipeCatalog::categories())->firstWhere('name', 'Cadernos');

        $this->assertSame(1, $categoria['count']);
    }

    public function test_recipe_catalog_searches_by_related_ingredient(): void
    {
        $user = User::factory()->create();
        $category = Categoria::create(['nome' => 'Doces', 'slug' => 'doces']);
        $recipe = Receita::create(['user_id' => $user->id, 'categoria_id' => $category->id, 'titulo' => 'Bolo de festa', 'descricao' => 'Uma receita suficientemente descritiva para buscar ingrediente.', 'tempo_preparo_min' => 20, 'tempo_cozimento_min' => 30, 'porcoes' => 8, 'custo' => 'medio', 'dificuldade' => 'facil', 'status' => 'publicada', 'published_at' => now()]);
        $ingredient = Ingrediente::create(['nome' => 'Canela']);
        ReceitaIngrediente::create(['receita_id' => $recipe->id, 'ingrediente_id' => $ingredient->id, 'quantidade' => 1, 'unidade' => 'colher_cha', 'ordem' => 0]);

        $this->get('/receitas?busca=canela')->assertOk()->assertSee('Bolo de festa');
    }

    public function test_user_cannot_open_another_users_recipe_editor(): void
    {
        $owner = User::factory()->create();
        $visitor = User::factory()->create();
        $category = Categoria::create(['nome' => 'Doces', 'slug' => 'doces']);
        $recipe = Receita::create([
            'user_id' => $owner->id, 'categoria_id' => $category->id, 'titulo' => 'Receita privada',
            'descricao' => 'Uma receita suficientemente descritiva para testar autorização.', 'tempo_preparo_min' => 10,
            'tempo_cozimento_min' => 10, 'porcoes' => 2, 'custo' => 'baixo', 'dificuldade' => 'facil',
            'status' => 'publicada', 'published_at' => now(),
        ]);

        $this->actingAs($visitor)->get(route('receitas.editar', $recipe))->assertForbidden();
    }

    public function test_user_cannot_delete_another_users_recipe(): void
    {
        $owner = User::factory()->create();
        $visitor = User::factory()->create();
        $category = Categoria::create(['nome' => 'Doces', 'slug' => 'doces']);
        $recipe = Receita::create([
            'user_id' => $owner->id, 'categoria_id' => $category->id, 'titulo' => 'Receita protegida',
            'descricao' => 'Uma receita suficientemente descritiva para testar exclusão segura.', 'tempo_preparo_min' => 10,
            'tempo_cozimento_min' => 10, 'porcoes' => 2, 'custo' => 'baixo', 'dificuldade' => 'facil', 'status' => 'rascunho',
        ]);

        $this->actingAs($visitor);
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        Livewire::test(MyRecipes::class)
            ->call('excluir', $recipe->id);

        $this->assertDatabaseHas('receitas', ['id' => $recipe->id]);
    }

    public function test_authenticated_drafts_tab_is_available(): void
    {
        $this->actingAs(User::factory()->create())->get('/minhas-receitas?aba=rascunhos')->assertOk()->assertSee('Rascunhos');
    }

    public function test_user_can_search_own_recipe_library_by_title(): void
    {
        $user = User::factory()->create();
        $category = Categoria::create(['nome' => 'Doces', 'slug' => 'doces']);
        Receita::create(['user_id' => $user->id, 'categoria_id' => $category->id, 'titulo' => 'Bolo da casa', 'descricao' => 'Uma receita suficientemente descritiva para a busca.', 'tempo_preparo_min' => 10, 'tempo_cozimento_min' => 10, 'porcoes' => 2, 'custo' => 'baixo', 'dificuldade' => 'facil', 'status' => 'publicada', 'published_at' => now()]);
        Receita::create(['user_id' => $user->id, 'categoria_id' => $category->id, 'titulo' => 'Sopa da tarde', 'descricao' => 'Outra receita suficientemente descritiva para a busca.', 'tempo_preparo_min' => 10, 'tempo_cozimento_min' => 10, 'porcoes' => 2, 'custo' => 'baixo', 'dificuldade' => 'facil', 'status' => 'publicada', 'published_at' => now()]);

        $this->actingAs($user);
        Livewire::test(MyRecipes::class)->set('busca', 'bolo')->assertSee('Bolo da casa')->assertDontSee('Sopa da tarde');
    }

    public function test_authenticated_recipe_creation_screen_is_available(): void
    {
        $this->actingAs(User::factory()->create())->get('/receitas/criar')->assertOk()->assertSee('Salvar rascunho');
    }

    public function test_authenticated_user_can_open_save_recipe_screen(): void
    {
        $this->get('/receitas/guardar')->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get('/receitas/guardar')->assertOk()->assertSee('Guardar receita');
    }

    public function test_user_can_open_favorites_collection(): void
    {
        $user = User::factory()->create();
        $category = Categoria::create(['nome' => 'Doces', 'slug' => 'doces']);
        $recipe = Receita::create(['user_id' => $user->id, 'categoria_id' => $category->id, 'titulo' => 'Favorita da Bianca', 'descricao' => 'Uma receita suficientemente descritiva para a coleção.', 'tempo_preparo_min' => 10, 'tempo_cozimento_min' => 10, 'porcoes' => 2, 'custo' => 'baixo', 'dificuldade' => 'facil', 'status' => 'publicada', 'published_at' => now()]);
        Favorito::create(['user_id' => $user->id, 'receita_id' => $recipe->id]);

        $this->actingAs($user)->get(route('favoritos'))->assertOk()->assertSee('Favorita da Bianca');
    }

    public function test_guest_cannot_open_favorites_collection(): void
    {
        $this->get(route('favoritos'))->assertRedirect(route('login'));
    }

    public function test_user_can_save_recipe_with_source_and_personal_notes(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $category = Categoria::create(['nome' => 'Doces', 'slug' => 'doces']);

        $this->actingAs($user);
        Livewire::test('receitas.save-recipe')
            ->set('titulo', 'Receita guardada de teste')
            ->set('descricao', 'Uma receita encontrada em outro lugar para guardar no meu cantinho.')
            ->set('categoria_id', $category->id)
            ->set('tipo_fonte', 'instagram')
            ->set('nome_fonte', 'Caderno da família')
            ->set('url_fonte', 'https://example.com/receita')
            ->set('observacoes_pessoais', 'Da próxima vez, usar menos açúcar.')
            ->set('ingredientes', [['nome' => 'Farinha', 'quantidade' => 200, 'unidade' => 'g', 'observacao' => 'peneirada']])
            ->set('modo_preparo', 'Misture os ingredientes e asse até dourar.')
            ->call('guardar');

        $receita = Receita::where('titulo', 'Receita guardada de teste')->firstOrFail();
        $this->assertDatabaseHas('receitas', ['id' => $receita->id, 'status' => 'rascunho', 'user_id' => $user->id]);
        $this->assertDatabaseHas('receita_fontes', ['receita_id' => $receita->id, 'user_id' => $user->id, 'tipo' => 'instagram', 'url' => 'https://example.com/receita']);
    }

    public function test_user_can_update_saved_recipe_source_details(): void
    {
        $user = User::factory()->create();
        $category = Categoria::create(['nome' => 'Doces', 'slug' => 'doces']);
        $recipe = Receita::create([
            'user_id' => $user->id, 'categoria_id' => $category->id, 'titulo' => 'Receita para atualizar',
            'descricao' => 'Uma receita guardada suficientemente descritiva para o teste.', 'tempo_preparo_min' => 20,
            'tempo_cozimento_min' => 10, 'porcoes' => 4, 'custo' => 'baixo', 'dificuldade' => 'facil', 'status' => 'rascunho',
        ]);

        $this->actingAs($user);
        Livewire::test(EditRecipe::class, ['receita' => $recipe])
            ->set('tipo_fonte', 'livro')
            ->set('nome_fonte', 'Livro de receitas')
            ->set('url_fonte', 'https://example.com/livro')
            ->set('observacoes_pessoais', 'Testar com menos sal.')
            ->set('ingredientes', [['chave' => 'teste', 'nome' => 'Farinha', 'quantidade' => 100, 'unidade' => 'g', 'observacao' => '']])
            ->set('modo_preparo', 'Misture tudo e asse até dourar.')
            ->call('salvar');

        $this->assertDatabaseHas('receita_fontes', ['receita_id' => $recipe->id, 'tipo' => 'livro', 'url' => 'https://example.com/livro', 'observacoes' => 'Testar com menos sal.']);
    }

    public function test_saved_recipe_rejects_insecure_source_url(): void
    {
        $user = User::factory()->create();
        $category = Categoria::create(['nome' => 'Doces', 'slug' => 'doces']);

        $this->actingAs($user);
        Livewire::test('receitas.save-recipe')
            ->set('titulo', 'Receita com link inválido')
            ->set('descricao', 'Uma receita suficientemente descritiva para validar o link.')
            ->set('categoria_id', $category->id)
            ->set('url_fonte', 'javascript:alert(1)')
            ->set('ingredientes', [['nome' => 'Farinha', 'quantidade' => 100, 'unidade' => 'g', 'observacao' => '']])
            ->set('modo_preparo', 'Misture tudo e asse até dourar.')
            ->call('guardar')
            ->assertHasErrors(['url_fonte']);
    }

    public function test_user_can_add_ingredient_while_editing_recipe(): void
    {
        $user = User::factory()->create();
        $category = Categoria::create(['nome' => 'Massas', 'slug' => 'massas']);
        $recipe = Receita::create(['user_id' => $user->id, 'categoria_id' => $category->id, 'titulo' => 'Pão de ló', 'descricao' => 'Uma receita de teste suficientemente descritiva.', 'tempo_preparo_min' => 10, 'tempo_cozimento_min' => 20, 'porcoes' => 4, 'custo' => 'baixo', 'dificuldade' => 'facil', 'status' => 'publicada', 'published_at' => now()]);

        $this->actingAs($user);
        Livewire::test(EditRecipe::class, ['receita' => $recipe])
            ->assertCount('ingredientes', 1)
            ->call('adicionarIngrediente')
            ->assertCount('ingredientes', 2);
    }

    public function test_user_can_create_recipe_with_a_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $category = Categoria::create(['nome' => 'Doces', 'slug' => 'doces']);

        $this->actingAs($user);
        Livewire::test(CreateRecipe::class)
            ->set('titulo', 'Bolo com foto')
            ->set('descricao', 'Uma receita de bolo suficientemente descritiva para o teste.')
            ->set('categoria_id', $category->id)
            ->set('ingredientes', [['nome' => 'Farinha', 'quantidade' => 200, 'unidade' => 'g', 'observacao' => 'peneirada']])
            ->set('modo_preparo', 'Misture os ingredientes e asse até dourar.')
            ->set('foto', UploadedFile::fake()->image('bolo.jpg'))
            ->call('salvar', 'rascunho');

        $receita = Receita::where('titulo', 'Bolo com foto')->firstOrFail();
        $this->assertStringStartsWith('receitas/', $receita->foto_principal_path);
        Storage::disk('public')->assertExists($receita->foto_principal_path);
    }

    public function test_recipe_creation_rejects_non_image_upload(): void
    {
        $user = User::factory()->create();
        $category = Categoria::create(['nome' => 'Doces', 'slug' => 'doces']);

        $this->actingAs($user);
        Livewire::test(CreateRecipe::class)
            ->set('titulo', 'Receita com upload inválido')
            ->set('descricao', 'Uma receita suficientemente descritiva para testar upload inválido.')
            ->set('categoria_id', $category->id)
            ->set('ingredientes', [['nome' => 'Farinha', 'quantidade' => 200, 'unidade' => 'g', 'observacao' => '']])
            ->set('modo_preparo', 'Misture os ingredientes e asse até dourar.')
            ->set('foto', UploadedFile::fake()->image('arquivo.png')->size(6000))
            ->call('salvar')
            ->assertHasErrors(['foto']);
    }

    public function test_owner_can_publish_a_saved_draft(): void
    {
        $user = User::factory()->create();
        $category = Categoria::create(['nome' => 'Doces', 'slug' => 'doces']);
        $recipe = Receita::create(['user_id' => $user->id, 'categoria_id' => $category->id, 'titulo' => 'Rascunho para publicar', 'descricao' => 'Uma receita suficientemente descritiva para publicar.', 'tempo_preparo_min' => 10, 'tempo_cozimento_min' => 10, 'porcoes' => 2, 'custo' => 'baixo', 'dificuldade' => 'facil', 'status' => 'rascunho']);

        $this->actingAs($user);
        Livewire::test(EditRecipe::class, ['receita' => $recipe])
            ->set('ingredientes', [['chave' => 'publicar', 'nome' => 'Farinha', 'quantidade' => 100, 'unidade' => 'g', 'observacao' => '']])
            ->set('modo_preparo', 'Misture tudo e asse até dourar.')
            ->call('salvar', 'publicada');

        $this->assertDatabaseHas('receitas', ['id' => $recipe->id, 'status' => 'publicada']);
    }

    public function test_editing_a_published_recipe_preserves_status_and_returns_to_my_recipes(): void
    {
        $user = User::factory()->create();
        $category = Categoria::create(['nome' => 'Doces', 'slug' => 'doces']);
        $recipe = Receita::create([
            'user_id' => $user->id, 'categoria_id' => $category->id, 'titulo' => 'Receita publicada editada',
            'descricao' => 'Uma receita publicada suficientemente descritiva para validar a edição.', 'tempo_preparo_min' => 10,
            'tempo_cozimento_min' => 10, 'porcoes' => 2, 'custo' => 'baixo', 'dificuldade' => 'facil', 'status' => 'publicada', 'published_at' => now(),
        ]);

        $this->actingAs($user);
        Livewire::test(EditRecipe::class, ['receita' => $recipe])
            ->set('ingredientes', [['chave' => 'editar', 'nome' => 'Farinha', 'quantidade' => 100, 'unidade' => 'g', 'observacao' => '']])
            ->set('modo_preparo', 'Misture tudo e asse até dourar com cuidado.')
            ->call('salvar')
            ->assertRedirect(route('minhas-receitas'));

        $this->assertDatabaseHas('receitas', ['id' => $recipe->id, 'status' => 'publicada']);
    }

    public function test_owner_can_replace_recipe_photo_while_editing(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $category = Categoria::create(['nome' => 'Doces', 'slug' => 'doces']);
        $oldPhoto = UploadedFile::fake()->image('antiga.jpg')->store('receitas', 'public');
        $recipe = Receita::create([
            'user_id' => $user->id, 'categoria_id' => $category->id, 'titulo' => 'Receita com foto editável',
            'descricao' => 'Uma receita suficientemente descritiva para validar troca de imagem.', 'tempo_preparo_min' => 10,
            'tempo_cozimento_min' => 10, 'porcoes' => 2, 'custo' => 'baixo', 'dificuldade' => 'facil', 'status' => 'rascunho', 'foto_principal_path' => $oldPhoto,
        ]);

        $this->actingAs($user);
        Livewire::test(EditRecipe::class, ['receita' => $recipe])
            ->set('ingredientes', [['chave' => 'foto', 'nome' => 'Farinha', 'quantidade' => 100, 'unidade' => 'g', 'observacao' => '']])
            ->set('modo_preparo', 'Misture tudo e asse até dourar com cuidado.')
            ->set('foto', UploadedFile::fake()->image('nova.jpg'))
            ->call('salvar');

        $recipe->refresh();
        $this->assertNotSame($oldPhoto, $recipe->foto_principal_path);
        Storage::disk('public')->assertMissing($oldPhoto);
        Storage::disk('public')->assertExists($recipe->foto_principal_path);
    }

    public function test_admin_categories_screen_is_available(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))->get('/admin/categorias')->assertOk();
    }

    public function test_admin_user_and_comment_screens_are_available(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin/usuarios')->assertOk();
        $this->actingAs($admin)->get('/admin/comentarios')->assertOk();
    }

    public function test_authenticated_user_can_like_and_save_recipe(): void
    {
        $user = User::factory()->create();
        $category = Categoria::create(['nome' => 'Massas', 'slug' => 'massas']);
        $recipe = Receita::create([
            'user_id' => $user->id,
            'categoria_id' => $category->id,
            'titulo' => 'Massa de teste',
            'descricao' => 'Uma receita de massa suficientemente descritiva para o teste.',
            'tempo_preparo_min' => 15,
            'tempo_cozimento_min' => 10,
            'porcoes' => 2,
            'custo' => 'baixo',
            'dificuldade' => 'facil',
            'status' => 'publicada',
            'published_at' => now(),
        ]);

        $this->actingAs($user);
        Livewire::test(LikeButton::class, ['receita' => $recipe])->call('toggle');
        Livewire::test(FavoriteButton::class, ['receita' => $recipe])->call('toggle');
        Livewire::test(RatingStars::class, ['receita' => $recipe])->call('avaliar', 5);
        Livewire::test(PortionCalculator::class, ['receita' => $recipe])->call('aumentar')->assertSet('porcoesAtuais', 3);
        Livewire::test(ShareRecipe::class, ['receita' => $recipe])->call('registrar', 'link');
        Livewire::test(CommentsList::class, ['receita' => $recipe])->set('novoComentario', 'Ficou deliciosa!')->call('comentar');

        $this->assertDatabaseHas('curtidas', ['user_id' => $user->id, 'receita_id' => $recipe->id]);
        $this->assertDatabaseHas('favoritos', ['user_id' => $user->id, 'receita_id' => $recipe->id]);
        $this->assertDatabaseHas('avaliacoes', ['user_id' => $user->id, 'receita_id' => $recipe->id, 'nota' => 5]);
        $this->assertDatabaseHas('compartilhamentos', ['receita_id' => $recipe->id, 'canal' => 'link']);
        $this->assertDatabaseHas('comentarios', ['user_id' => $user->id, 'receita_id' => $recipe->id, 'conteudo' => 'Ficou deliciosa!']);
    }

}
