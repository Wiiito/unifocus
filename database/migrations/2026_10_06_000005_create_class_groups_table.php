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
         * Turma = matéria + período + pessoas. Enquanto não há integração com as
         * instituições, cada estudante cria a própria turma pessoal (is_personal)
         * ao se matricular: assim existe UM caminho de código para os dois casos.
         *
         * A instituição não é guardada aqui: ela vem da matéria/período, e uma
         * cópia poderia divergir da origem.
         */
        Schema::create('class_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->nullable()->constrained()->nullOnDelete();

            /** A turma pessoal pertence ao estudante e some junto com a conta dele. */
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->cascadeOnDelete();

            $table->string('name', 80);
            $table->boolean('is_personal')->default(false);

            /** [{weekday, start, end}]: base para gerar as aulas automaticamente. */
            $table->jsonb('schedule')->nullable();
            $table->string('room', 60)->nullable();

            /** Total de aulas previstas no período: base do limite de faltas. */
            $table->unsignedSmallInteger('total_classes')->nullable();

            /**
             * Regras de aprovação próprias da turma. Obrigatórias quando não há
             * instituição; com instituição, NULL = vale a regra dela.
             */
            $table->unsignedSmallInteger('total_points')->nullable();
            $table->decimal('passing_percent', 5, 2)->nullable();
            $table->unsignedSmallInteger('max_absence_percent')->nullable();
            $table->enum('status', ['planned', 'in_progress', 'finished', 'canceled'])->default('in_progress');
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_groups');
    }
};
