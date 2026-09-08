<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EZ-Seed | Farmer-Centered Seed Preference Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        da: {
                            green: {
                                50: '#f0f9f0',
                                100: '#dcf0dc',
                                200: '#b8e0b8',
                                600: '#1a7a1a',
                                700: '#156015',
                                800: '#0f4a0f',
                                900: '#0a350a',
                            },
                            yellow: {
                                50: '#fffbeb',
                                100: '#fef3c7',
                                400: '#fbbf24',
                                500: '#f5a623',
                                600: '#d68910',
                            },
                        },
                    },
                    fontFamily: {
                        sans: ['Segoe UI', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                },
            },
        };
    </script>
    <style>
        body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }
        .progress-fill { transition: width 0.4s ease-in-out; }
        @media print {
            .no-print { display: none !important; }
        }

        /* Ensure every answer field has a clearly visible outline,
           even where a utility class forgot to set border width/color. */
        input[type="text"],
        input[type="date"],
        input[type="number"],
        input[type="email"],
        input[type="tel"],
        input[type="password"],
        input[type="search"],
        input[type="url"],
        select,
        textarea {
            border-width: 2px;
            border-style: solid;
            border-color: #cbd5e1; /* slate-300, visible against white cards */
        }
        input[type="text"]:focus,
        input[type="date"]:focus,
        input[type="number"]:focus,
        input[type="email"]:focus,
        input[type="tel"]:focus,
        input[type="password"]:focus,
        input[type="search"]:focus,
        input[type="url"]:focus,
        select:focus,
        textarea:focus {
            border-color: #1a7a1a; /* da-green-600 */
        }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen flex flex-col antialiased">

    {{-- ===================== HEADER ===================== --}}
    <header class="bg-da-green-800 text-white shadow-md no-print">
        <div class="bg-da-yellow-500 h-1.5 w-full"></div>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4">
                <div class="flex-shrink-0 bg-white rounded-full p-2 shadow-sm">
                    <svg class="w-12 h-12 text-da-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c0 4-4 5-4 9a4 4 0 108 0c0-4-4-5-4-9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 12v9M8 21h8" />
                    </svg>
                </div>
                <div class="text-center sm:text-left flex-1">
                    <p class="text-xs sm:text-sm uppercase tracking-wider text-da-green-100 font-medium">
                        Republic of the Philippines &middot; Department of Agriculture
                    </p>
                    <p class="text-xs sm:text-sm text-da-green-100">
                        Regional Field Office No. 02, Cagayan Valley
                    </p>
                    <h1 class="text-2xl sm:text-3xl font-bold mt-1 tracking-tight">
                        EZ-Seed
                    </h1>
                    <p class="text-sm sm:text-base text-da-green-100">
                        Farmer-Centered Seed Preference Platform
                    </p>
                </div>
                <div class="hidden sm:flex flex-col items-end justify-center text-xs text-da-green-100 space-y-1">
                    <span class="inline-flex items-center gap-1 bg-da-green-700/60 px-3 py-1 rounded-full">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Official Survey System
                    </span>
                </div>
            </div>
        </div>
    </header>

    {{-- ===================== PROGRESS BAR ===================== --}}
    @isset($currentStep)
        <div class="bg-white border-b border-gray-200 shadow-sm no-print sticky top-0 z-30">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-da-green-800">
                        Step {{ $currentStep }} of {{ $totalSteps ?? 10 }}
                        <span class="hidden sm:inline text-gray-500 font-normal">&mdash; {{ $stepTitle ?? '' }}</span>
                    </span>
                    <span class="text-sm font-bold text-da-green-700">
                        {{ $progressPercent ?? round(($currentStep / ($totalSteps ?? 10)) * 100) }}%
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden" role="progressbar"
                     aria-valuenow="{{ $progressPercent ?? round(($currentStep / ($totalSteps ?? 10)) * 100) }}"
                     aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-fill bg-gradient-to-r from-da-green-600 to-da-green-700 h-3 rounded-full"
                         style="width: {{ $progressPercent ?? round(($currentStep / ($totalSteps ?? 10)) * 100) }}%">
                    </div>
                </div>

                {{-- Step dots (desktop only) --}}
                <div class="hidden lg:flex items-center justify-between mt-4">
                    @for ($i = 1; $i <= ($totalSteps ?? 10); $i++)
                        <div class="flex flex-col items-center flex-1">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold border-2
                                {{ $i < $currentStep ? 'bg-da-green-600 border-da-green-600 text-white'
                                    : ($i == $currentStep ? 'bg-white border-da-green-600 text-da-green-700 ring-4 ring-da-green-100'
                                    : 'bg-white border-gray-300 text-gray-400') }}">
                                @if ($i < $currentStep)
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                @else
                                    {{ $i }}
                                @endif
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    @endisset

    {{-- ===================== FLASH MESSAGES ===================== --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
        @if (session('success'))
            <div class="mt-6 flex items-start gap-3 bg-da-green-50 border border-da-green-200 text-da-green-800 px-4 py-3 rounded-xl shadow-sm" role="alert">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm sm:text-base font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mt-6 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl shadow-sm" role="alert">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007v.008H12v-.008zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm sm:text-base font-medium">{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mt-6 bg-red-50 border border-red-200 text-red-800 px-4 py-4 rounded-xl shadow-sm" role="alert">
                <div class="flex items-center gap-2 font-semibold mb-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007v.008H12v-.008zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Please correct the following before continuing:
                </div>
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- ===================== MAIN CONTENT ===================== --}}
    <main class="flex-1 max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    {{-- ===================== FOOTER ===================== --}}
    <footer class="bg-da-green-900 text-da-green-100 no-print">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs sm:text-sm">
                <p>&copy; {{ date('Y') }} Department of Agriculture &mdash; Regional Field Office No. 02. All rights reserved.</p>
                <p class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    EZ-Seed Survey System v1.0
                </p>
            </div>
        </div>
    </footer>

    {{-- Offline-sync library must load before per-step scripts (@stack('scripts')) call OfflineSync.attach() --}}
    {{-- Cache-busted with the file's mtime so browsers/CDNs never serve a stale copy after a deploy --}}
    <script src="{{ asset('js/offline-sync.js') }}?v={{ file_exists(public_path('js/offline-sync.js')) ? filemtime(public_path('js/offline-sync.js')) : time() }}"></script>

    {{-- Global drawer showing anything saved offline on this device (queued submissions + in-progress drafts).
         Included once here (not per-step) so it's available no matter which wizard step the user is on. --}}
    @include('surveys.partials.offline-drafts-drawer')

    @stack('scripts')

    <script>
    // Auto-capitalize the first letter of every word in text inputs/textareas,
    // applied globally across every survey step. Skips inputs that shouldn't
    // be touched (numbers, dates, emails, selects, etc.) automatically since
    // it only targets type="text" and <textarea>.
    document.addEventListener('input', function (e) {
        const el = e.target;
        const isTextInput = el.tagName === 'TEXTAREA' ||
            (el.tagName === 'INPUT' && el.type === 'text');

        if (!isTextInput || el.dataset.noAutocap) return;

        const cursorPos = el.selectionStart;
        const capitalized = el.value.replace(/(^|\s)([a-z])/g, (m, boundary, letter) => boundary + letter.toUpperCase());

        if (capitalized !== el.value) {
            el.value = capitalized;
            el.setSelectionRange(cursorPos, cursorPos);
        }
    });
</script>
</body>
</html>