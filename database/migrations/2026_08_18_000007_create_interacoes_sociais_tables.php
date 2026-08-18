<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comentarios', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('receita_id')->constrained('receitas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('comentario_pai_id')->nullable()->constrained('comentarios')->cascadeOnDelete();
            $table->text('conteudo');
            $table->enum('status', ['publicado', 'oculto'])->default('publicado')->index();
            $table->timestamps();
        });

        Schema::create('avaliacoes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('receita_id')->constrained('receitas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('nota');
            $table->timestamps();
            $table->unique(['receita_id', 'user_id']);
        });

        foreach (['favoritos', 'curtidas'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('receita_id')->constrained('receitas')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['user_id', 'receita_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('curtidas');
        Schema::dropIfExists('favoritos');
        Schema::dropIfExists('avaliacoes');
        Schema::dropIfExists('comentarios');
    }
};
