<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Curtida;
use App\Models\Favorito;
use App\Models\Avaliacao;
use App\Models\Comentario;
use App\Models\Compartilhamento;
use App\Models\Receita;
use App\Models\User;
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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RecipePlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_recipe_pages_and_sitemap_are_available(): void
    {
        $this->get('/')->assertOk()->assertSee('Cantinho das Receitas');
        $this->get('/receitas')->assertOk()->assertSee('Todas as receitas');
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

    public function test_authenticated_drafts_tab_is_available(): void
    {
        $this->actingAs(User::factory()->create())->get('/minhas-receitas?aba=rascunhos')->assertOk()->assertSee('Rascunhos');
    }

    public function test_authenticated_recipe_creation_screen_is_available(): void
    {
        $this->actingAs(User::factory()->create())->get('/receitas/criar')->assertOk()->assertSee('Salvar rascunho');
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
