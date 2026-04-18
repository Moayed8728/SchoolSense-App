<?php

namespace App\Http\Controllers\SchoolManager;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolUpdateRequest;

class DashboardController extends Controller
{
    public function index()
    {
        $schools = School::query()
            ->where('ownerUserId', auth()->id())
            ->latest()
            ->get();

        $pendingUpdateCount = SchoolUpdateRequest::query()
            ->where('userId', auth()->id())
            ->where('status', 'pending')
            ->count();

        return view('school-manager.dashboard', compact('schools', 'pendingUpdateCount'));
    }
}
