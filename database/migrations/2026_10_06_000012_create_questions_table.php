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
         * Questão reutilizável. O statement_hash único impede que a IA (ou um
         * admin) cadastre o mesmo enunciado duas vezes.
         *
         * A coluna de embedding (pgvector) fica para a migração que ligar a
         * geração por IA: exige a extensão no Postgres e não existe no SQLite
         * usado pelos testes.
         */
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignId('ai_generation_id')->nullable()->constrained()->nullOnDelete();
            $table->text('statement');
            $table->char('statement_hash', 64)->unique();
            $table->enum('type', ['multiple_choice', 'true_false', 'open', 'numeric'])->default('multiple_choice');
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->string('topic', 180)->nullable();

            /** Gabarito comentado. */
            $table->text('explanation')->nullable();
            $table->enum('source', ['ai', 'manual', 'imported'])->default('manual');

            /** Moderação: a IA erra, então o que ela gera nasce pendente. */
            $table->enum('review_status', ['pending', 'approved', 'rejected'])->default('pending');

            /** Caches de question_attempts. */
            $table->unsignedInteger('times_used')->default(0);
            $table->decimal('correct_rate', 5, 2)->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['subject_id', 'difficulty', 'review_status']);
        });

        /** Alternativa em tabela (e não JSON) para permitir estatística por alternativa. */
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->string('label', 4)->nullable();
            $table->text('content');
            $table->boolean('is_correct')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
    }
};
