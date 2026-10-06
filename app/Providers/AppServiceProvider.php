<?php

namespace App\Providers;

use App\Contracts\QuestionGenerator;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Support\Questions\UnavailableQuestionGenerator;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        /** Trocar pela implementação com IA quando ela existir. */
        $this->app->bind(QuestionGenerator::class, UnavailableQuestionGenerator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * As requisições de update do Livewire não passam pelas rotas do
         * painel; persistir o middleware garante que toda ação dos
         * componentes admin continue exigindo um admin autenticado.
         */
        Livewire::addPersistentMiddleware([EnsureUserIsAdmin::class]);
    }
}
