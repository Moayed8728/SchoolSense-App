@props([
    'title' => null,
    'icon' => null,
    'hover' => false,
    'padding' => 'p-6',
])

<div {{ $attributes->merge([
    'class' => '
        rounded-xl ' . $padding . '
        bg-white border border-slate-200
        dark:bg-slate-900 dark:border-slate-700
        ' . ($hover ? 'transition hover:-translate-y-1 cursor-pointer' : '')
]) }}>

    @if($title || $icon)
        <div class="flex items-center gap-3 mb-5">
            @if($icon)
                <div class="w-9 h-9 rounded-lg bg-indigo-500/15 border border-indigo-500/25 flex items-center justify-center">
                    {!! $icon !!}
                </div>
            @endif

            @if($title)
                <h3 class="font-display font-semibold text-slate-900 dark:text-slate-100 text-base">
                    {{ $title }}
                </h3>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>