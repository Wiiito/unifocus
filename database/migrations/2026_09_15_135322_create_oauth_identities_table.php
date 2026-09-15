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
        Schema::create('oauth_identities', function (Blueprint $table) {
            $table->id();

            $table->string('provider');
            $table->string('provider_id');

            /** Dono da identidade: qualquer model autenticável, não só o User. */
            $table->morphs('authenticatable');

            $table->string('name')->nullable();
            $table->string('nickname')->nullable();
            $table->string('email')->nullable();
            $table->string('avatar')->nullable();

            $table->text('token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();

            $table->timestamps();

            $table->unique(['provider', 'provider_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oauth_identities');
    }
};
