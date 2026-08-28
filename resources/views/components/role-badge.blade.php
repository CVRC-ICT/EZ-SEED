@props(['user' => null])

@php($user = $user ?? auth()->user())

@if ($user)
    @if ($user->isAdministrator())
        <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800']) }}>
            ● Administrator
        </span>
    @else
        <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700']) }}>
            ● User
        </span>
    @endif
@endif
