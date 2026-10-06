<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\Enrollments\ActivityController;
use App\Http\Controllers\Enrollments\GradeEntryController;
use App\Http\Controllers\Enrollments\LessonController;
use App\Http\Controllers\InstitutionMembershipController;
use App\Http\Controllers\PracticeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportCardController;
use App\Http\Controllers\SubjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
Route::get('/subjects/{subject}', [SubjectController::class, 'show'])->name('subjects.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('enrollments', EnrollmentController::class);

    /**
     * Conteúdo da turma lançado pelo próprio estudante. scoped() garante que a
     * aula/atividade/nota pertence à matrícula da URL.
     */
    Route::resource('enrollments.lessons', LessonController::class)->except(['index', 'show'])->scoped();
    Route::resource('enrollments.activities', ActivityController::class)->except(['index', 'show'])->scoped();
    Route::resource('enrollments.grade-entries', GradeEntryController::class)->except(['index', 'show'])->scoped();

    Route::get('/agenda', AgendaController::class)->name('agenda');
    Route::get('/report-card', ReportCardController::class)->name('report-card');

    Route::get('/practice', [PracticeController::class, 'show'])->name('practice.show');
    Route::post('/practice/{question}', [PracticeController::class, 'store'])->name('practice.store');

    Route::post('/institution-memberships', [InstitutionMembershipController::class, 'store'])
        ->name('institution-memberships.store');
    Route::delete('/institution-memberships/{membership}', [InstitutionMembershipController::class, 'destroy'])
        ->whereNumber('membership')
        ->name('institution-memberships.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
