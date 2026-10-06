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
        /** Vínculo usuário <-> instituição. O papel vive aqui, nunca no usuário. */
        Schema::create('institution_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['student', 'teacher', 'coordinator'])->default('student');
            $table->enum('status', ['active', 'inactive', 'pending'])->default('active');
            $table->string('registration_code', 40)->nullable();
            $table->timestampTz('joined_at')->nullable();
            $table->timestampsTz();

            $table->unique(['institution_id', 'user_id']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institution_members');
    }
};
