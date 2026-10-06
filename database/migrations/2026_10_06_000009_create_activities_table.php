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
        /** Atividade/avaliação da turma (tarefa, prova, trabalho, quiz). */
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->enum('type', ['homework', 'exam', 'recovery', 'project', 'quiz', 'reading', 'other'])->default('homework');

            /** Pontos que a atividade distribui (ex.: prova valendo 30). NULL = não vale nota. */
            $table->decimal('max_points', 6, 2)->nullable();

            /** Peso da nota no total (recuperação costuma valer mais). */
            $table->decimal('weight', 5, 2)->default(1.00);
            $table->timestampTz('available_from')->nullable();
            $table->timestampTz('due_at')->nullable();
            $table->boolean('allow_late')->default(true);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['class_group_id', 'due_at']);
        });

        /**
         * Entrega do aluno (máquina de estados). A nota NÃO fica aqui: vive em
         * grade_entries, que também recebe notas sem atividade (participação).
         */
        Schema::create('activity_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'submitted', 'late', 'graded', 'returned'])->default('pending');
            $table->text('content')->nullable();
            $table->unsignedSmallInteger('attempt')->default(1);
            $table->timestampTz('submitted_at')->nullable();
            $table->text('feedback')->nullable();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('reviewed_at')->nullable();
            $table->timestampsTz();

            $table->unique(['activity_id', 'enrollment_id']);
            $table->index('enrollment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_submissions');
        Schema::dropIfExists('activities');
    }
};
