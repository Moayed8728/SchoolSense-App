<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(): View
    {
        $schools = auth()->user()
            ->favoriteSchools()
            ->paginate(12);

        return view('favorites.index', compact('schools'));
    }

    public function store(School $school): RedirectResponse
    {
        auth()->user()->favorites()->firstOrCreate([
            'schoolId' => $school->id,
        ]);

        return back();
    }
    


    public function destroy(School $school): RedirectResponse
    {
        auth()->user()
            ->favorites()
            ->where('schoolId', $school->id)
            ->delete();

        return back();
    }
}