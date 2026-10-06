<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Esconde a existência da área administrativa: quem não está autenticado
 * como admin recebe 404, nunca 403 ou um redirect para o login.
 *
 * Também é persistente no Livewire (AppServiceProvider), então protege as
 * ações dos componentes do painel, não só o carregamento da página.
 */
class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(Auth::guard('admin')->check(), 404);

        /** No painel, Auth::user() passa a ser o admin (ex.: autor na auditoria). */
        Auth::shouldUse('admin');

        return $next($request);
    }
}
