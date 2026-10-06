<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /**
         * Log de cada chamada de IA: sem custo/token registrado não há controle
         * da conta. Ainda não há geração ligada; a tabela existe para que as
         * questões já nasçam com o vínculo de origem.
         */
        Schema::create('ai_generations', function (Blueprint $table) {
            $table->id();

            /** Quem disparou: um estudante ou um admin (geração em lote pelo painel). */
            $table->nullableMorphs('requested_by');
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('purpose', ['questions', 'summary', 'flashcards', 'explanation'])->default('questions');
            $table->string('provider', 40)->default('anthropic');
            $table->string('model', 80);
            $table->text('prompt')->nullable();

            /** sha256 do prompt: cache/idempotência. */
            $table->char('prompt_hash', 64);
            $table->unsignedInteger('tokens_input')->nullable();
            $table->unsignedInteger('tokens_output')->nullable();
            $table->unsignedInteger('cost_cents')->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->enum('status', ['queued', 'running', 'succeeded', 'failed'])->default('queued');
            $table->text('error')->nullable();
            $table->timestampsTz();

            $table->index(['prompt_hash', 'model']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_generations');
    }
};
