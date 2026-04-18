@props(['changes', 'school' => null, 'type' => 'update'])

@php
    $changes = $changes ?? [];
    $labels = [
        'name' => 'School name',
        'country' => 'Country',
        'city' => 'City',
        'address' => 'Address',
        'websiteUrl' => 'Website URL',
        'contactEmail' => 'Contact email',
        'contactPhone' => 'Contact phone',
        'contactPageUrl' => 'Contact page URL',
        'description' => 'Description',
        'feesMin' => 'Minimum fees',
        'feesMax' => 'Maximum fees',
        'currency' => 'Currency',
        'feePeriod' => 'Fee period',
        'curriculumIds' => 'Curricula',
        'activityIds' => 'Activities',
        'languageIds' => 'Languages',
    ];

    $formatValue = function ($key, $value) {
        if (in_array($key, ['curriculumIds', 'activityIds', 'languageIds'], true)) {
            $model = match ($key) {
                'curriculumIds' => \App\Models\Curriculum::class,
                'activityIds' => \App\Models\Activity::class,
                'languageIds' => \App\Models\Language::class,
            };

            $names = $model::whereIn('id', $value ?? [])->orderBy('name')->pluck('name')->all();

            return empty($names) ? 'None selected' : implode(', ', $names);
        }

        if (is_null($value) || $value === '') {
            return 'Empty';
        }

        if (in_array($key, ['feesMin', 'feesMax'], true)) {
            return number_format((int) $value);
        }

        if (is_array($value)) {
            return implode(', ', $value);
        }

        return (string) $value;
    };
@endphp

@if($type === 'delete')
    <div class="rounded-2xl border border-rose-300/25 bg-rose-500/10 p-4">
        <p class="text-sm font-semibold text-rose-100">Deletion requested</p>
        <p class="mt-2 text-sm leading-6 text-rose-100/80">{{ $changes['reason'] ?? 'No reason provided.' }}</p>
    </div>
@else
    <div class="space-y-3">
        @foreach($changes as $key => $value)
            <div class="rounded-2xl border border-slate-700/75 bg-slate-900/45 p-4">
                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">{{ $labels[$key] ?? $key }}</p>
                        <p class="mt-2 text-sm font-semibold text-slate-100">{{ $formatValue($key, $value) }}</p>
                    </div>

                    @if($school && !in_array($key, ['curriculumIds', 'activityIds', 'languageIds'], true) && isset($labels[$key]))
                        <div class="max-w-md text-left md:text-right">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-600">Current</p>
                            <p class="mt-2 break-words text-sm text-slate-400">{{ $formatValue($key, $school->{$key} ?? null) }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
