<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Contracts\View\View;

class SubjectController extends Controller
{
    /**
     * Display a public listing of the subjects catalog.
     */
    public function index(): View
    {
        $subjects = Subject::query()->orderBy('name')->paginate(12);

        return view('subjects.index', ['subjects' => $subjects]);
    }

    /**
     * Display the public details of a subject.
     */
    public function show(Subject $subject): View
    {
        return view('subjects.show', ['subject' => $subject]);
    }
}
