<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Curriculum;
use App\Models\Language;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:255'],
            'curriculum' => ['nullable', 'string', 'max:255'],
            'activity' => ['nullable', 'string', 'max:255'],
            'language' => ['nullable', 'string', 'max:255'],
            'feesMax' => ['nullable', 'integer', 'min:0'],
            'sort' => ['nullable', Rule::in(['name', 'fees', 'newest'])],
        ]);

        $schools = School::query()
            ->with(['curricula', 'activities', 'languages'])
            ->when($filters['q'] ?? null, function ($query, string $search) {
                $search = trim($search);
                $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%';
                $operator = $this->likeOperator();

                $query->where(function ($query) use ($like, $operator) {
                    $query
                        ->where('name', $operator, $like)
                        ->orWhere('country', $operator, $like)
                        ->orWhere('city', $operator, $like)
                        ->orWhere('address', $operator, $like)
                        ->orWhere('websiteUrl', $operator, $like)
                        ->orWhere('description', $operator, $like)
                        ->orWhereHas('curricula', fn ($relation) => $relation->where('name', $operator, $like))
                        ->orWhereHas('activities', fn ($relation) => $relation->where('name', $operator, $like))
                        ->orWhereHas('languages', fn ($relation) => $relation->where('name', $operator, $like));
                });
            })
            ->when($filters['city'] ?? null, fn ($query, string $city) => $query->where('city', $city))
            ->when($filters['curriculum'] ?? null, fn ($query, string $value) => $this->whereTaxonomy($query, 'curricula', $value))
            ->when($filters['activity'] ?? null, fn ($query, string $value) => $this->whereTaxonomy($query, 'activities', $value))
            ->when($filters['language'] ?? null, fn ($query, string $value) => $this->whereTaxonomy($query, 'languages', $value))
            ->when($filters['feesMax'] ?? null, function ($query, int $feesMax) {
                $query->where(function ($query) use ($feesMax) {
                    $query
                        ->whereNull('feesMin')
                        ->orWhere('feesMin', '<=', $feesMax);
                });
            })
            ->when(($filters['sort'] ?? 'name') === 'fees', fn ($query) => $query->orderBy('feesMin')->orderBy('name'))
            ->when(($filters['sort'] ?? 'name') === 'newest', fn ($query) => $query->latest()->orderBy('name'))
            ->when(($filters['sort'] ?? 'name') === 'name', fn ($query) => $query->orderBy('name'))
            ->paginate(10)
            ->withQueryString();

        return view('schools.index', [
            'schools' => $schools,
            'filters' => $filters,
            'cities' => School::query()
                ->whereNotNull('city')
                ->select('city')
                ->distinct()
                ->orderBy('city')
                ->pluck('city'),
            'curricula' => Curriculum::orderBy('name')->get(),
            'activities' => Activity::orderBy('name')->get(),
            'languages' => Language::orderBy('name')->get(),
        ]);
    }

    public function show(School $school)
    {$isFavorited = auth()->check()
        ? auth()->user()->favorites()->where('schoolId', $school->id)->exists()
        : false;
    
    return view('schools.show', compact('school', 'isFavorited'));
    }

    private function whereTaxonomy($query, string $relation, string $value): void
    {
        $operator = $this->likeOperator();

        $query->whereHas($relation, function ($relationQuery) use ($value, $operator) {
            $relationQuery
                ->where('slug', $value)
                ->orWhere('name', $operator, $value);
        });
    }

    private function likeOperator(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
    }
}
