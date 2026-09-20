<?php

use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

/**
 * Prefixo de URL propositalmente discreto: a existência desta área não deve
 * ser óbvia para quem não é admin. O middleware "admin" reforça isso
 * devolvendo 404 (não 403) para quem tentar acessar sem estar autenticado.
 */
Route::prefix('staff')->name('admin.')->group(function () {
    Route::middleware('admin.guest')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])
            ->name('login');

        Route::post('login', [AuthenticatedSessionController::class, 'store']);
    });

    Route::middleware('admin')->group(function () {
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
            ->name('logout');

        Route::livewire('subjects', 'admin.subjects.manager')
            ->name('subjects.index');
    });
});
