<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolManagerApplication;
use App\Models\SchoolUpdateRequest;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'pendingApplicationCount' => SchoolManagerApplication::where('status', 'pending')->count(),
            'pendingUpdateCount' => SchoolUpdateRequest::where('status', 'pending')->count(),
        ]);
    }
}
