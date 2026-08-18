<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compartilhamentos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('receita_id')->constrained('receitas')->cascadeOnDelete();
            $table->enum('canal', ['whatsapp', 'facebook', 'telegram', 'x', 'pinterest', 'link']);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['receita_id', 'canal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compartilhamentos');
    }
};
