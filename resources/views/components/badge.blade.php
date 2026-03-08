@props([
    'color' => 'indigo', // indigo | purple | emerald | amber | sky | rose
    'size' => 'sm',
])

@php
$colors = [
    'indigo'  => 'bg-indigo-500/15 text-indigo-300 border-indigo-500/25',
    'purple'  => 'bg-purple-500/15 text-purple-300 border-purple-500/25',
    'emerald' => 'bg-emerald-500/15 text-emerald-300 border-emerald-500/25',
    'amber'   => 'bg-amber-500/15 text-amber-300 border-amber-500/25',
    'sky'     => 'bg-sky-500/15 text-sky-300 border-sky-500/25',
    'rose'    => 'bg-rose-500/15 text-rose-300 border-rose-500/25',
    'slate'   => 'bg-slate-700/60 text-slate-300 border-slate-600/50',
];
$color = is_string($color) ? $color : 'indigo';
$cls = $colors[$color] ?? $colors['indigo'];
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $cls }}{{ $attributes->has('class') ? ' ' . $attributes->get('class') : '' }}">
    {{ $slot }}
</span>
