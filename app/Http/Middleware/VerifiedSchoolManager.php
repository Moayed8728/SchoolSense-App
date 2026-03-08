<?php

namespace App\Http\Middleware;

use App\Models\SchoolRepRequest;
use Closure;
use Illuminate\Http\Request;

class VerifiedSchoolManager
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // role middleware should already ensure role=school_manager, but keep safe:
        if (!$user || $user->role !== 'school_manager') {
            abort(403);
        }

        $latest = SchoolRepRequest::where('userId', $user->id)
            ->latest()
            ->first();

        if (!$latest || $latest->status !== 'approved') {
            return redirect()
                ->route('school-manager.verify.index')
                ->with('error', 'You must be verified before submitting schools or updates.');
        }

        return $next($request);
    }
}