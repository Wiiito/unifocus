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
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 180);
            $table->string('slug', 80)->unique();

            /** CNPJ. */
            $table->string('document', 20)->nullable();

            /**
             * Regras acadêmicas herdadas pelas matrículas: o semestre distribui
             * total_points pontos e o aluno precisa de passing_percent deles.
             * Os defaults espelham Institution::DEFAULT_*.
             */
            $table->unsignedSmallInteger('total_points')->default(100);
            $table->decimal('passing_percent', 5, 2)->default(60.00);
            $table->unsignedSmallInteger('max_absence_percent')->default(25);

            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};
