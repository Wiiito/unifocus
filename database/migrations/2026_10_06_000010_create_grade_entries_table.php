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
         * Nota de (matrícula, avaliação), nunca de matéria. Modelo de pontos:
         * 25 de 30 é guardado como points = 25, max_points = 30. Com peso 2,
         * ela conta 50 de 60 no total do período.
         *
         * Sem soft delete de propósito: um registro apagado logicamente
         * continuaria ocupando o índice único (enrollment_id, activity_id) e
         * impediria lançar a nota de novo. O histórico fica em audit_logs.
         */
        Schema::create('grade_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();

            /** NULL = nota avulsa (participação, prova sem atividade cadastrada...). */
            $table->foreignId('activity_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('label', 120);
            $table->enum('kind', ['activity', 'exam', 'recovery', 'manual'])->default('activity');
            $table->decimal('points', 6, 2);
            $table->decimal('max_points', 6, 2);
            $table->decimal('weight', 5, 2)->default(1.00);
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('recorded_at')->useCurrent();
            $table->timestampsTz();

            $table->unique(['enrollment_id', 'activity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_entries');
    }
};
