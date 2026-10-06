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
        /** Aula (encontro). Sem starts_at não existe presença nem cronograma. */
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 180);

            /** Conteúdo da aula: alimenta a geração de questões no futuro. */
            $table->string('topic', 180)->nullable();
            $table->text('description')->nullable();
            $table->timestampTz('starts_at');
            $table->timestampTz('ends_at')->nullable();

            /** Quantas aulas este encontro vale (aula dupla = 2). Faltas contam aulas, não horas. */
            $table->unsignedSmallInteger('class_count')->default(1);
            $table->enum('status', ['scheduled', 'done', 'canceled'])->default('scheduled');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['class_group_id', 'starts_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
