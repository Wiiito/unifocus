<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            /** Foto do provedor OAuth (Google), atualizada a cada login. NULL = avatar de iniciais. */
            $table->string('avatar_url', 1000)->nullable()->after('email');
        });

        /** Quem já entrou com o Google antes desta coluna existir ganha a foto agora. */
        DB::table('users')->update([
            'avatar_url' => DB::table('oauth_identities')
                ->select('avatar')
                ->whereColumn('oauth_identities.authenticatable_id', 'users.id')
                ->where('oauth_identities.authenticatable_type', User::class)
                ->whereNotNull('oauth_identities.avatar')
                ->latest('oauth_identities.updated_at')
                ->limit(1),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar_url');
        });
    }
};
