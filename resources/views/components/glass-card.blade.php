@props([
    'title' => null,
    'icon' => null,
    'hover' => false,
    'padding' => 'p-4',
])

<div {{ $attributes->merge([
    'class' => '
        rounded-xl ' . $padding . '
        bg-white border border-slate-200
        dark:bg-slate-900 dark:border-slate-700
        ' . ($hover ? 'transition hover:-translate-y-1 cursor-pointer' : '')
]) }}>

    @if($title || $icon)
        <div class="mb-4 flex items-center gap-2.5">
            @if($icon)
                <div class="flex h-8 w-8 items-center justify-center rounded-lg border border-indigo-500/25 bg-indigo-500/15">
                    {!! $icon !!}
                </div>
            @endif

            @if($title)
                <h3 class="font-display text-sm font-semibold text-slate-900 dark:text-slate-100">
                    {{ $title }}
                </h3>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>
