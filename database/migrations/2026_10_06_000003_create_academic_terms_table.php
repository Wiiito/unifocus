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
         * Período letivo de uma instituição: cada uma tem o próprio calendário.
         *
         * Não existe a coluna is_current: "período atual" é derivado das datas,
         * o que evita dois períodos marcados como atuais ao mesmo tempo.
         */
        Schema::create('academic_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('name', 40);
            $table->date('starts_on');
            $table->date('ends_on');
            $table->timestampsTz();

            $table->unique(['institution_id', 'name']);
        });

        /** Marcos do calendário do período: semana de provas, recesso, feriados... */
        Schema::create('academic_term_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_term_id')->constrained()->cascadeOnDelete();
            $table->string('title', 120);
            $table->enum('type', ['exam_week', 'holiday', 'recess', 'enrollment', 'other'])->default('other');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->timestampsTz();

            $table->index(['academic_term_id', 'starts_on']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_term_events');
        Schema::dropIfExists('academic_terms');
    }
};
