<?php

namespace App\Http\Controllers\SchoolManager;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolUpdateRequest;
use Illuminate\Http\Request;

class UpdateRequestController extends Controller
{
    public function index()
    {
        $requests = SchoolUpdateRequest::where('userId', auth()->id())
            ->latest()
            ->paginate(12);

        return view('school-manager.updates.index', compact('requests'));
    }

    public function create()
    {
        $schools = School::where('ownerUserId', auth()->id())
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('school-manager.updates.create', compact('schools'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'schoolId' => ['required', 'uuid', 'exists:schools,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'contactEmail' => ['nullable', 'email', 'max:255'],
            'contactPhone' => ['nullable', 'string', 'max:50'],
            'contactPageUrl' => ['nullable', 'url', 'max:255'],
            'feesMin' => ['nullable', 'integer', 'min:0'],
            'feesMax' => ['nullable', 'integer', 'min:0'],
        ]);

        $school = School::where('id', $data['schoolId'])
            ->where('ownerUserId', auth()->id())
            ->firstOrFail();

        $changes = array_filter([
            'description' => $data['description'] ?? null,
            'contactEmail' => $data['contactEmail'] ?? null,
            'contactPhone' => $data['contactPhone'] ?? null,
            'contactPageUrl' => $data['contactPageUrl'] ?? null,
            'feesMin' => $data['feesMin'] ?? null,
            'feesMax' => $data['feesMax'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        if (empty($changes)) {
            return back()
                ->withErrors(['description' => 'Please provide at least one field to update.'])
                ->withInput();
        }

        SchoolUpdateRequest::create([
            'userId' => auth()->id(),
            'schoolId' => $school->id,
            'changes' => $changes,
            'status' => 'pending',
        ]);

        return redirect()->route('school-manager.updates.index');
    }

    public function show(SchoolUpdateRequest $update)
    {
        abort_unless($update->userId === auth()->id(), 403);

        return view('school-manager.updates.show', compact('update'));
    }
}