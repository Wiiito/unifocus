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
        Schema::table('subjects', function (Blueprint $table) {
            /** NULL = matéria do catálogo geral, disponível para qualquer estudante. */
            $table->foreignId('institution_id')->nullable()->after('id')->constrained()->cascadeOnDelete();

            /** O mesmo código (MAT101) pode existir em instituições diferentes. */
            $table->dropUnique(['code']);
            $table->unique(['institution_id', 'code'])->nullsNotDistinct();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropUnique(['institution_id', 'code']);
            $table->dropConstrainedForeignId('institution_id');
            $table->unique('code');
        });
    }
};
