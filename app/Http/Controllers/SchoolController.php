<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index()
    {
        $schools = School::query()
            ->paginate(5);

        return view('schools.index', compact('schools'));
    }

    public function show(School $school)
    {$isFavorited = auth()->check()
        ? auth()->user()->favorites()->where('schoolId', $school->id)->exists()
        : false;
    
    return view('schools.show', compact('school', 'isFavorited'));
    }
}