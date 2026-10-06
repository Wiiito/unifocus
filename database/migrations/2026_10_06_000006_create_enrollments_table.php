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
         * Matrícula usuário <-> turma. Presença, entregas e notas apontam para
         * cá (e não para users): garante que a pessoa está na turma e já carrega
         * o período.
         */
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['active', 'completed', 'dropped', 'locked'])->default('active');

            /**
             * Caches recalculados a partir de grade_entries e lesson_attendances:
             * final_grade = pontos obtidos; absence_count = aulas faltadas.
             */
            $table->decimal('final_grade', 6, 2)->nullable();
            $table->enum('final_status', ['in_progress', 'approved', 'failed', 'failed_absence'])->default('in_progress');
            $table->unsignedSmallInteger('absence_count')->default(0);

            $table->timestampTz('enrolled_at')->useCurrent();
            $table->timestampsTz();

            $table->unique(['class_group_id', 'user_id']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
