<?php

namespace App\Http\Controllers\SchoolManager;

use App\Http\Controllers\Controller;
use App\Models\SchoolRepRequest;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function index()
    {
        $requestRow = SchoolRepRequest::where('userId', auth()->id())
            ->latest()
            ->first();

        return view('school-manager.verify.index', compact('requestRow'));
    }

    public function create()
    {
        return view('school-manager.verify.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fullName' => ['required','string','max:255'],
            'schoolName' => ['required','string','max:255'],
            'websiteUrl' => ['nullable','url','max:255'],
            'proofText' => ['nullable','string','max:2000'],
        ]);

        SchoolRepRequest::create([
            'userId' => auth()->id(),
            ...$data,
            'status' => 'pending',
        ]);

        return redirect()->route('school-manager.verify.index');
    }
}