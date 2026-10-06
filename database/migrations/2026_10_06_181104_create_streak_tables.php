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
         * Progresso dos desafios diários (um registro por estudante por dia).
         *
         * Só viewed_agenda_at é fato próprio desta tabela; aulas e questões são
         * caches recalculados de lesson_attendances e question_attempts, para
         * o dia "descompletar" se uma presença for removida.
         */
        Schema::create('daily_challenge_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('day');
            $table->unsignedSmallInteger('attended_lessons')->default(0);
            $table->timestampTz('viewed_agenda_at')->nullable();
            $table->unsignedSmallInteger('questions_answered')->default(0);
            $table->timestampTz('completed_at')->nullable();
            $table->timestampsTz();

            $table->unique(['user_id', 'day']);
        });

        /**
         * Foguinho de cada estudante. Desnormalizado de propósito: é lido em
         * toda página e será exibido para outros usuários (amigos), então
         * custa uma linha por pessoa.
         */
        Schema::create('user_streaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('current_count')->default(0);
            $table->unsignedInteger('longest_count')->default(0);
            $table->date('last_completed_on')->nullable();
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_streaks');
        Schema::dropIfExists('daily_challenge_progress');
    }
};
