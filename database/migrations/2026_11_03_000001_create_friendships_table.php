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
         * Uma linha por par de pessoas, na direção em que o pedido foi feito
         * (requester → addressee). O unique cobre só uma direção; a inversa é
         * barrada pela action SendFriendRequest, que aceita o pedido contrário
         * em vez de criar um segundo.
         */
        Schema::create('friendships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('addressee_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'accepted'])->default('pending');
            $table->timestampTz('accepted_at')->nullable();
            $table->timestampsTz();

            $table->unique(['requester_id', 'addressee_id']);
            $table->index(['addressee_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('friendships');
    }
};
