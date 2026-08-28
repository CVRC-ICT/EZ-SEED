<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email | EZ-Seed</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;0,9..144,800;1,9..144,500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        da: {
                            green: {
                                50: '#f0f9f0', 100: '#dcf0dc', 200: '#b8e0b8',
                                600: '#1a7a1a', 700: '#156015', 800: '#0f4a0f', 900: '#0a350a',
                            },
                            yellow: {
                                50: '#fffbeb', 100: '#fef3c7', 400: '#fbbf24', 500: '#f5a623', 600: '#d68910',
                            },
                            cream: '#f7f4ec',
                        },
                    },
                    fontFamily: {
                        display: ['Fraunces', 'serif'],
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                },
            },
        };
    </script>

    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; height: 100vh; overflow: hidden; }
        .grain-row { display: flex; align-items: flex-end; gap: 6px; }
        .grain-row svg { flex-shrink: 0; }
        a:focus-visible, button:focus-visible { outline: 3px solid #1a7a1a; outline-offset: 2px; }
    </style>
</head>
<body class="bg-da-cream antialiased h-screen overflow-hidden">

    <div class="h-screen lg:flex">

        {{-- ===================== LEFT: BRAND / STORY PANEL ===================== --}}
        <aside class="relative lg:w-[42%] h-screen overflow-hidden bg-da-green-900">

            <img src="{{ asset('images/sidebar-bg.jpg') }}"
                 alt=""
                 class="absolute inset-0 w-full h-full object-cover"
                 onerror="this.style.display='none'">

            <div class="absolute inset-0 bg-gradient-to-b from-white/85 via-white/55 to-white/20"></div>

            <div class="relative h-full px-6 sm:px-10 lg:px-14 py-8 lg:py-10 flex flex-col justify-center">
            <div class="max-w-md mx-auto lg:mx-0 w-full">

                <div class="flex items-center gap-3 mb-5">
                    <div class="flex-shrink-0 bg-white rounded-full p-2 shadow-sm ring-1 ring-da-green-100">
                        <img src="{{ asset('images/da-logo.png') }}"
                             alt="Department of Agriculture seal"
                             class="w-14 h-14 rounded-full object-contain">
                    </div>
                    <div>
                        <p class="text-sm uppercase tracking-[0.14em] text-da-green-700/70 font-semibold">
                            Republic of the Philippines
                        </p>
                        <p class="text-lg font-bold text-da-green-900 -mt-0.5">Department of Agriculture</p>
                    </div>
                </div>

                <span class="inline-flex items-center gap-1.5 bg-da-yellow-100 text-da-yellow-600 text-xs font-semibold uppercase tracking-wide px-3 py-1.5 rounded-full mb-5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                    </svg>
                    Regional Field Office No. II
                </span>

                <h1 class="font-display font-800 text-5xl sm:text-6xl leading-[0.95] text-da-green-900 tracking-tight mb-4">
                    EZ&#8209;<span class="text-da-yellow-600">Seed</span>
                </h1>

                <p class="text-base sm:text-lg text-da-green-900/70 leading-relaxed mb-6">
                    One last step before you can access the DA Analytics Dashboard for
                    <span class="font-semibold text-da-green-800">Batanes, Cagayan, Isabela, Nueva Vizcaya,</span>
                    and <span class="font-semibold text-da-green-800">Quirino</span>.
                </p>

                <div class="grain-row mb-6" aria-hidden="true">
                    @for ($i = 0; $i < 7; $i++)
                        <svg class="w-2.5 text-da-green-600/70" style="height: {{ 14 + ($i % 3) * 5 }}px" viewBox="0 0 10 24" fill="none">
                            <path d="M5 24V6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M5 8c0-3 4-3 4-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M5 8c0-3-4-3-4-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    @endfor
                </div>

            </div>
            </div>
        </aside>

        {{-- ===================== RIGHT: VERIFY EMAIL CARD ===================== --}}
        <main class="lg:w-[58%] h-screen bg-white lg:rounded-l-[2.5rem] lg:shadow-[0_0_60px_-15px_rgba(15,74,15,0.15)] px-6 sm:px-10 lg:px-16 py-8 lg:py-10 flex flex-col justify-center overflow-y-auto">
            <div class="max-w-xl mx-auto w-full bg-white rounded-3xl ring-1 ring-da-green-900/10 shadow-[0_10px_40px_-15px_rgba(15,74,15,0.15)] p-6 sm:p-8">

                <div class="flex items-center gap-2 mb-2">
                    <span class="text-xs font-bold uppercase tracking-[0.14em] text-da-green-700">Almost there</span>
                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-da-green-700 bg-da-green-50 px-2 py-0.5 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-da-green-600 animate-pulse"></span>
                        Live
                    </span>
                </div>

                <h2 class="font-display font-700 text-3xl sm:text-4xl leading-tight text-da-green-900 mb-2">
                    Verify your email
                </h2>
                <p class="text-base text-da-green-900/60 mb-5">
                    Check your inbox for the verification link.
                </p>

                {{-- ===================== ORIGINAL VERIFY EMAIL CONTENT (UNCHANGED LOGIC) ===================== --}}

                <div class="mb-4 text-[15px] sm:text-base leading-relaxed text-gray-600 dark:text-gray-400">
                    {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
                </div>

                @if (session('status') == 'verification-link-sent')
                    <div class="mb-4 font-medium text-[15px] sm:text-base text-green-600 dark:text-green-400">
                        {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                    </div>
                @endif

                <div class="mt-4 flex items-center justify-between">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf

                        <div>
                            <x-primary-button>
                                {{ __('Resend Verification Email') }}
                            </x-primary-button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button type="submit" class="underline text-sm sm:text-base text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                            {{ __('Log Out') }}
                        </button>
                    </form>
                </div>

                {{-- ===================== END ORIGINAL VERIFY EMAIL CONTENT ===================== --}}

                <div class="mt-6 text-center">
                    <p class="text-xs text-da-green-900/40">
                        EZ-Seed Survey System v1.0 &middot; Department of Agriculture, Regional Field Office No. II
                    </p>
                </div>
            </div>
        </main>

    </div>

</body>
</html>