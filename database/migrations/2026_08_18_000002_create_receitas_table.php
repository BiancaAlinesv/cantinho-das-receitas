<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receitas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('categoria_id')->constrained('categorias')->cascadeOnDelete();
            $table->string('titulo');
            $table->string('slug')->unique();
            $table->text('descricao');
            $table->string('foto_principal_path')->nullable();
            $table->unsignedInteger('tempo_preparo_min');
            $table->unsignedInteger('tempo_cozimento_min')->nullable();
            $table->unsignedInteger('porcoes');
            $table->enum('custo', ['baixo', 'medio', 'alto']);
            $table->enum('dificuldade', ['facil', 'medio', 'dificil']);
            $table->string('rendimento')->nullable();
            $table->text('dicas')->nullable();
            $table->text('variacoes')->nullable();
            $table->text('observacoes')->nullable();
            $table->string('video_url')->nullable();
            $table->enum('status', ['rascunho', 'publicada'])->default('rascunho')->index();
            $table->unsignedBigInteger('visualizacoes_total')->default(0);
            $table->decimal('nota_media', 3, 2)->default(0);
            $table->unsignedInteger('total_avaliacoes')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receitas');
    }
};
