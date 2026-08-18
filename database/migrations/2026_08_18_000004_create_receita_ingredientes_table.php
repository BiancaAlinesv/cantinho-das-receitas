<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receita_ingredientes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('receita_id')->constrained('receitas')->cascadeOnDelete();
            $table->foreignId('ingrediente_id')->constrained('ingredientes')->cascadeOnDelete();
            $table->decimal('quantidade', 10, 2)->nullable();
            $table->enum('unidade', ['g', 'kg', 'ml', 'l', 'xicara', 'colher_sopa', 'colher_cha', 'unidade', 'a_gosto']);
            $table->string('observacao')->nullable();
            $table->unsignedSmallInteger('ordem')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receita_ingredientes');
    }
};
