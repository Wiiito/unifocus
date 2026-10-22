<?php

namespace App\Providers;

use App\Contracts\QuestionGenerator;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Support\Questions\AnthropicQuestionGenerator;
use App\Support\Questions\OpenRouterQuestionGenerator;
use App\Support\Questions\UnavailableQuestionGenerator;
use App\Support\Streaks\StreakService;
use App\View\Composers\DailyChallengeComposer;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        /** Com a IA ligada em config/ai.php e a chave do provedor escolhido definida, gera de verdade; senão, indisponível. */
        $this->app->bind(QuestionGenerator::class, function (Application $app): QuestionGenerator {
            $generator = $app->make(match (config('ai.provider')) {
                'openrouter' => OpenRouterQuestionGenerator::class,
                default => AnthropicQuestionGenerator::class,
            });

            return config('ai.questions.enabled') && $generator->isAvailable()
                ? $generator
                : $app->make(UnavailableQuestionGenerator::class);
        });

        /** Uma instância por requisição: o quadro do dia é calculado uma vez só. */
        $this->app->scoped(StreakService::class);
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

        View::composer(
            ['layouts.partials.header', 'layouts.partials.sidebar', 'dashboard'],
            DailyChallengeComposer::class,
        );
    }
}
