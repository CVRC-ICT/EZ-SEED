@php
    $navItem = fn ($route, $label, $icon, $params = []) => [
        'active' => request()->routeIs($route),
        'href'   => route($route, $params),
        'label'  => $label,
        'icon'   => $icon,
    ];

    $items = [
        $navItem('dashboard', 'Dashboard', '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>'),
        $navItem('dashboard.farmers', 'Farmer Profiles', '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>'),
        $navItem('dashboard.seed-monitoring', 'Seed Monitoring', '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>'),
        $navItem('dashboard.seasonal-analysis', 'Seasonal Analysis', '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M3 10h18M8 2v4M16 2v4"/></svg>'),
        $navItem('dashboard.province-maps', 'Province Maps', '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 6v16l7-4 8 4 7-4V2l-7 4-8-4-7 4z"/><path d="M8 2v16M16 6v16"/></svg>'),
        $navItem('dashboard.reports', 'Reports', '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M9 15h6M9 11h2"/></svg>'),
        $navItem('settings.edit', 'Settings', '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>'),
    ];

    // Administrator-only nav item. This is purely cosmetic (hides the
    // link for normal Users) — the real access control is enforced
    // server-side by the 'admin' route middleware on /admin/users/*.
    if (auth()->user()?->isAdministrator()) {
        $items[] = $navItem('admin.users.index', 'User Management', '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><path d="M19 8v6M22 11h-6"/></svg>');
    }
@endphp

<aside class="hidden lg:flex lg:flex-col w-64 shrink-0 bg-green-950 text-white min-h-[calc(100vh-4rem)] px-4 py-6">
    <div class="mb-8 px-2 flex items-center gap-2">
        <div class="w-9 h-9 rounded-lg bg-green-700 flex items-center justify-center text-lg">🌾</div>
        <div>
            <p class="text-sm font-bold tracking-wide leading-tight">EZ-Seed</p>
            <p class="text-[11px] text-green-300 leading-tight">Analytics Dashboard</p>
        </div>
    </div>

    <p class="text-[11px] font-semibold text-green-400 tracking-widest px-3 mb-2">NAVIGATION</p>

    <nav class="space-y-1">
        @foreach ($items as $item)
            <a href="{{ $item['href'] }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                      {{ $item['active']
                            ? 'bg-amber-500 text-green-950'
                            : 'text-green-100 hover:bg-green-900' }}">
                {!! $item['icon'] !!}
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="mt-auto pt-6 text-[11px] text-green-400 px-2">
        <p>v1.0.0 · Region 02</p>
    </div>
</aside>
