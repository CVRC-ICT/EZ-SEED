@props(['label', 'value', 'sub' => null, 'color' => 'green', 'icon' => null])

@php
    $badge = match ($color) {
        'amber'   => 'bg-amber-100 text-amber-700',
        'blue'    => 'bg-sky-100 text-sky-700',
        'emerald' => 'bg-emerald-100 text-emerald-700',
        default   => 'bg-green-100 text-green-700',
    };
@endphp

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
    <div class="flex items-center justify-between mb-4">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg {{ $badge }}">
            {{ $icon }}
        </div>
    </div>
    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $value ?? '—' }}</p>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $label }}</p>
    @if ($sub)
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $sub }}</p>
    @endif
</div>
