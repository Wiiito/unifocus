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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();

            /** Quem cadastrou a matéria; preservado mesmo se o admin for removido. */
            $table->foreignId('created_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();

            $table->string('name', 160);
            $table->string('code', 40)->nullable()->unique();
            $table->text('description')->nullable();
            $table->smallInteger('credits')->nullable();
            $table->smallInteger('workload_hours')->nullable();
            $table->string('color', 9)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
