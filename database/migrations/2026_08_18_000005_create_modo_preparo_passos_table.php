<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modo_preparo_passos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('receita_id')->constrained('receitas')->cascadeOnDelete();
            $table->unsignedSmallInteger('ordem');
            $table->text('descricao');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modo_preparo_passos');
    }
};
