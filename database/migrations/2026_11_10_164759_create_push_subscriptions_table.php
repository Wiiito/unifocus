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
         * Um navegador/dispositivo inscrito no Web Push. endpoint é a URL do
         * serviço de push do navegador (única por inscrição); public_key e
         * auth_token são as chaves que ele gerou para cifrar o conteúdo.
         */
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('endpoint', 1000)->unique();
            $table->string('public_key');
            $table->string('auth_token');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
