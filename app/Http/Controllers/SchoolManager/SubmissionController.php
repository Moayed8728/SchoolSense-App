<?php

namespace App\Http\Controllers\SchoolManager;

use App\Http\Controllers\Controller;
use App\Models\SchoolSubmissionRequest;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function index()
    {
        $submissions = SchoolSubmissionRequest::where('userId', auth()->id())
            ->latest()
            ->paginate(12);

        return view('school-manager.submissions.index', compact('submissions'));
    }

    public function create()
    {
        return view('school-manager.submissions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'country' => ['required','string','size:2'],
            'city' => ['nullable','string','max:255'],
            'address' => ['nullable','string','max:255'],

            'websiteUrl' => ['nullable','url','max:255'],
            'contactEmail' => ['nullable','email','max:255'],
            'contactPhone' => ['nullable','string','max:50'],
            'contactPageUrl' => ['nullable','url','max:255'],

            'description' => ['nullable','string','max:5000'],

            'feesMin' => ['nullable','integer','min:0'],
            'feesMax' => ['nullable','integer','min:0'],
            'currency' => ['nullable','string','size:3'],
            'feePeriod' => ['required','in:yearly,semester'],

            // Accept comma-separated IDs for now (simple). Later we’ll use multi-select.
            'curriculumIds' => ['nullable','string'],
            'activityIds' => ['nullable','string'],
            'languageIds' => ['nullable','string'],
        ]);

        $toArray = fn ($v) => $v ? array_values(array_filter(array_map('trim', explode(',', $v)))) : null;

        SchoolSubmissionRequest::create([
            'userId' => auth()->id(),

            'name' => $data['name'],
            'country' => $data['country'],
            'city' => $data['city'] ?? null,
            'address' => $data['address'] ?? null,

            'websiteUrl' => $data['websiteUrl'] ?? null,
            'contactEmail' => $data['contactEmail'] ?? null,
            'contactPhone' => $data['contactPhone'] ?? null,
            'contactPageUrl' => $data['contactPageUrl'] ?? null,

            'description' => $data['description'] ?? null,

            'feesMin' => $data['feesMin'] ?? null,
            'feesMax' => $data['feesMax'] ?? null,
            'currency' => $data['currency'] ?? 'SAR',
            'feePeriod' => $data['feePeriod'],

            'curriculumIds' => $toArray($data['curriculumIds'] ?? null),
            'activityIds' => $toArray($data['activityIds'] ?? null),
            'languageIds' => $toArray($data['languageIds'] ?? null),

            'status' => 'pending',
        ]);

        return redirect()->route('school-manager.submissions.index');
    }

    public function show(SchoolSubmissionRequest $submission)
    {
        abort_unless($submission->userId === auth()->id(), 403);

        return view('school-manager.submissions.show', compact('submission'));
    }
}