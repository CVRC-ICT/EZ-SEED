<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EZ-Seed | Farmer-Centered Seed Preference Platform</title>

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

        /* Signature element: a hand-drawn "grain row" rule — stalks of rice
           standing in a line — used in place of generic numbered markers,
           since this platform is literally about rice & corn seed. */
        .grain-row {
            display: flex;
            align-items: flex-end;
            gap: 6px;
        }
        .grain-row svg { flex-shrink: 0; }

        .portal-card {
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }
        .portal-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px -12px rgba(15, 74, 15, 0.18);
        }
        .portal-card:hover .portal-icon-wrap {
            transform: scale(1.06);
        }
        .portal-icon-wrap { transition: transform 0.25s ease; }

        /* Organic "blob" cutout for the card photos — no hard square
           edges. Applying an asymmetric border-radius to a real
           rectangular photo fakes an irregular cutout without needing a
           separate pre-cut transparent asset. */
        .blob-photo {
            border-radius: 63% 37% 54% 46% / 41% 55% 45% 59%;
        }

        .continue-btn { transition: transform 0.15s ease, box-shadow 0.15s ease; }
        .continue-btn:hover { transform: translateX(2px); }
        .continue-btn:active { transform: scale(0.98); }

        @media (prefers-reduced-motion: reduce) {
            .portal-card, .portal-icon-wrap, .continue-btn { transition: none !important; }
            .portal-card:hover { transform: none; }
        }

        a:focus-visible, button:focus-visible {
            outline: 3px solid #1a7a1a;
            outline-offset: 2px;
        }
    </style>
</head>
<body class="bg-da-cream antialiased h-screen overflow-hidden">

    <div class="h-screen lg:flex">

        {{-- ===================== LEFT: BRAND / STORY PANEL ===================== --}}
        <aside class="relative lg:w-[42%] h-screen overflow-hidden bg-da-green-900">

            {{-- Side-panel background photo — swap this file for your own image.
                 Falls back to the solid da-green-900 color above if missing. --}}
            <img src="{{ asset('images/sidebar-bg.jpg') }}"
                 alt=""
                 class="absolute inset-0 w-full h-full object-cover"
                 onerror="this.style.display='none'">

            {{-- Readability scrim: darker toward the top-left where the
                 wordmark/copy sits, lighter lower down --}}
            <div class="absolute inset-0 bg-gradient-to-b from-white/85 via-white/55 to-white/20"></div>

            <div class="relative h-full px-6 sm:px-10 lg:px-14 py-8 lg:py-10 flex flex-col justify-center">
            <div class="max-w-md mx-auto lg:mx-0 w-full">

                {{-- Agency mark --}}
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
                    A digital service connecting Region&nbsp;II farmers with agricultural planners &mdash;
                    capturing seed variety preferences across
                    <span class="font-semibold text-da-green-800">Batanes, Cagayan, Isabela, Nueva Vizcaya,</span>
                    and <span class="font-semibold text-da-green-800">Quirino</span>.
                </p>

                {{-- Signature grain-row divider --}}
                <div class="grain-row mb-6" aria-hidden="true">
                    @for ($i = 0; $i < 7; $i++)
                        <svg class="w-2.5 text-da-green-600/70" style="height: {{ 14 + ($i % 3) * 5 }}px" viewBox="0 0 10 24" fill="none">
                            <path d="M5 24V6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M5 8c0-3 4-3 4-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M5 8c0-3-4-3-4-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    @endfor
                </div>

                <dl class="grid grid-cols-2 gap-3">
                    <div class="bg-white rounded-2xl p-3 shadow-sm ring-1 ring-da-green-900/5">
                        <div class="w-7 h-7 rounded-lg bg-da-green-100 flex items-center justify-center mb-2">
                            <svg class="w-4 h-4 text-da-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.62-5.619 14.734-5.619 20.353 0M12 18.75h.008v.008H12v-.008z" /></svg>
                        </div>
                        <dt class="font-semibold text-sm text-da-green-900">Offline First</dt>
                        <dd class="text-xs text-da-green-900/60 mt-0.5 leading-snug">Submit surveys with no signal &mdash; data is saved on the device.</dd>
                    </div>
                    <div class="bg-white rounded-2xl p-3 shadow-sm ring-1 ring-da-green-900/5">
                        <div class="w-7 h-7 rounded-lg bg-da-green-100 flex items-center justify-center mb-2">
                            <svg class="w-4 h-4 text-da-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                        </div>
                        <dt class="font-semibold text-sm text-da-green-900">Auto Sync</dt>
                        <dd class="text-xs text-da-green-900/60 mt-0.5 leading-snug">Records sync the moment a connection is found.</dd>
                    </div>
                    <div class="bg-white rounded-2xl p-3 shadow-sm ring-1 ring-da-green-900/5">
                        <div class="w-7 h-7 rounded-lg bg-da-green-100 flex items-center justify-center mb-2">
                            <svg class="w-4 h-4 text-da-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.75h-.152c-3.196 0-6.1-1.248-8.25-3.286z" /></svg>
                        </div>
                        <dt class="font-semibold text-sm text-da-green-900">Data Privacy Act</dt>
                        <dd class="text-xs text-da-green-900/60 mt-0.5 leading-snug">Farmer data is handled under RA&nbsp;10173 safeguards.</dd>
                    </div>
                    <div class="bg-white rounded-2xl p-3 shadow-sm ring-1 ring-da-green-900/5">
                        <div class="w-7 h-7 rounded-lg bg-da-green-100 flex items-center justify-center mb-2">
                            <svg class="w-4 h-4 text-da-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                        </div>
                        <dt class="font-semibold text-sm text-da-green-900">Real-Time Analytics</dt>
                        <dd class="text-xs text-da-green-900/60 mt-0.5 leading-snug">Planners see seed-preference trends as they arrive.</dd>
                    </div>
                </dl>
            </div>
            </div>
        </aside>

        {{-- ===================== RIGHT: PORTAL SELECTION ===================== --}}
        <main class="lg:w-[58%] h-screen bg-white lg:rounded-l-[2.5rem] lg:shadow-[0_0_60px_-15px_rgba(15,74,15,0.15)] px-6 sm:px-10 lg:px-16 py-8 lg:py-10 flex flex-col justify-center">
            <div class="max-w-xl mx-auto w-full bg-white rounded-3xl ring-1 ring-da-green-900/10 shadow-[0_10px_40px_-15px_rgba(15,74,15,0.15)] p-6 sm:p-8">

                <div class="flex items-center gap-2 mb-2">
                    <span class="text-xs font-bold uppercase tracking-[0.14em] text-da-green-700">Welcome</span>
                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-da-green-700 bg-da-green-50 px-2 py-0.5 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-da-green-600 animate-pulse"></span>
                        Live
                    </span>
                </div>

                <h2 class="font-display font-700 text-3xl sm:text-4xl leading-tight text-da-green-900 mb-2">
                    Choose your portal
                </h2>
                <p class="text-base text-da-green-900/60 mb-5">
                    Select how you're accessing EZ-Seed today.
                </p>

                <div class="space-y-4">

                    {{-- ---------- FARMER PORTAL ---------- --}}
                    <a href="{{ route('surveys.my-surveys') }}"
                       class="portal-card group relative block bg-white rounded-3xl p-5 sm:p-6 ring-1 ring-da-green-900/10 hover:ring-da-green-600/40">
                        <div class="flex items-start justify-between mb-3">
                            <div class="portal-icon-wrap w-10 h-10 rounded-2xl bg-da-green-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-da-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c0 4-4 5-4 9a4 4 0 108 0c0-4-4-5-4-9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 12v9M8 21h8" />
                                </svg>
                            </div>
                            <span class="w-8 h-8 rounded-full ring-1 ring-da-green-900/10 flex items-center justify-center text-da-green-700 group-hover:bg-da-green-600 group-hover:text-white group-hover:ring-da-green-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3" /></svg>
                            </span>
                        </div>

                        <div class="sm:pr-32 md:pr-36">
                            <h3 class="font-display font-700 text-2xl text-da-green-900 mb-1">Farmer Portal</h3>
                            <p class="text-xs font-semibold uppercase tracking-wide text-da-green-600 mb-2">Mobile Survey Application</p>
                            <p class="text-sm text-da-green-900/60 leading-relaxed mb-3">
                                Share your seed variety preferences and help shape agricultural planning across Region&nbsp;II.
                            </p>

                            <div class="flex flex-wrap gap-1.5">
                                @foreach (['Works Offline', 'Auto Save', 'QR Code', 'Large Touch Targets'] as $tag)
                                    <span class="text-xs font-medium text-da-green-800 bg-da-green-50 px-2.5 py-1 rounded-full">{{ $tag }}</span>
                                @endforeach
                            </div>
                        </div>

                        {{-- Photo: an organic blob cutout (no square frame), floating
                             over soft blurred color shapes, bleeding past the
                             card's edge — echoes the Figma reference. --}}
                        <div class="hidden sm:block absolute -top-2 right-4 md:right-6 w-32 md:w-36 pointer-events-none">
                            <div class="absolute -top-1 -right-2 w-20 h-20 rounded-full bg-da-green-100/70 blur-[2px]"></div>
                            <div class="absolute bottom-2 -left-3 w-14 h-14 rounded-full bg-da-yellow-100/70 blur-[2px]"></div>
                            <img src="{{ asset('images/hero-farmer.jpg') }}"
                                 alt=""
                                 class="blob-photo relative w-full aspect-square object-cover shadow-lg">
                        </div>

                        <span class="continue-btn mt-4 inline-flex w-full items-center justify-center gap-2 bg-da-green-700 hover:bg-da-green-800 text-white font-semibold text-base py-3 rounded-xl">
                            Open Farmer Portal
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3" /></svg>
                        </span>
                    </a>

                    {{-- ---------- DA ANALYTICS DASHBOARD ---------- --}}
                    <a href="{{ route('login') }}"
                       class="portal-card group relative block bg-white rounded-3xl p-5 sm:p-6 ring-1 ring-da-green-900/10 hover:ring-da-yellow-500/50">
                        <div class="flex items-start justify-between mb-3">
                            <div class="portal-icon-wrap w-10 h-10 rounded-2xl bg-da-yellow-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-da-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75h6v6h-6v-6zM14.25 3.75h6v6h-6v-6zM3.75 14.25h6v6h-6v-6zM14.25 14.25h6v6h-6v-6z" />
                                </svg>
                            </div>
                            <span class="w-8 h-8 rounded-full ring-1 ring-da-green-900/10 flex items-center justify-center text-da-yellow-600 group-hover:bg-da-yellow-500 group-hover:text-white group-hover:ring-da-yellow-500 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3" /></svg>
                            </span>
                        </div>

                        <div class="sm:pr-32 md:pr-36">
                            <h3 class="font-display font-700 text-2xl text-da-green-900 mb-1">DA Analytics Dashboard</h3>
                            <p class="text-xs font-semibold uppercase tracking-wide text-da-yellow-600 mb-2">For DA Personnel</p>
                            <p class="text-sm text-da-green-900/60 leading-relaxed mb-3">
                                Monitor survey responses, analyze variety trends, and generate province-level reports.
                            </p>

                            <div class="flex flex-wrap gap-1.5">
                                @foreach (['Real-Time Analytics', 'Province Heat Maps', 'Reports & Export', 'Role-Based Access', 'Data Visualization'] as $tag)
                                    <span class="text-xs font-medium text-da-yellow-700 bg-da-yellow-50 px-2.5 py-1 rounded-full">{{ $tag }}</span>
                                @endforeach
                            </div>
                        </div>

                        {{-- Photo: dashboard preview floating over soft blurred
                             color shapes, no square frame. This placeholder jpg
                             is blob-cropped for now — once you have a real
                             laptop-mockup PNG with a transparent background,
                             swap the file and you can drop the blob-photo class
                             for a cleaner product-shot silhouette. --}}
                        <div class="hidden sm:block absolute -top-2 right-4 md:right-6 w-32 md:w-36 pointer-events-none">
                            <div class="absolute -top-1 -right-2 w-20 h-20 rounded-full bg-da-yellow-100/70 blur-[2px]"></div>
                            <div class="absolute bottom-2 -left-3 w-14 h-14 rounded-full bg-da-yellow-50 blur-[2px]"></div>
                            <img src="{{ asset('images/dashboard-preview.jpg') }}"
                                 alt=""
                                 class="blob-photo relative w-full aspect-square object-cover shadow-lg bg-da-yellow-50"
                                 onerror="this.outerHTML='<div class=&quot;blob-photo relative w-full aspect-square bg-da-yellow-50 flex items-center justify-center shadow-lg&quot;><svg class=&quot;w-9 h-9 text-da-yellow-400&quot; fill=&quot;none&quot; viewBox=&quot;0 0 24 24&quot; stroke=&quot;currentColor&quot; stroke-width=&quot;1.5&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; d=&quot;M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z&quot; /></svg></div>'">
                        </div>

                        <span class="continue-btn mt-4 inline-flex w-full items-center justify-center gap-2 bg-da-yellow-500 hover:bg-da-yellow-600 text-white font-semibold text-base py-3 rounded-xl">
                            Open Dashboard
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3" /></svg>
                        </span>
                    </a>

                </div>

                <div class="mt-5 text-center">
                    <p class="text-xs text-da-green-900/40 mb-1.5">
                        EZ-Seed Survey System v1.0 &middot; Department of Agriculture, Regional Field Office No. II
                    </p>
                    <p class="flex items-center justify-center gap-4 text-xs font-medium text-da-green-900/45">
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                            Secure
                        </span>
                        <span aria-hidden="true">&middot;</span>
                        <span>RA 10173 Compliant</span>
                        <span aria-hidden="true">&middot;</span>
                        <span>Official Government System</span>
                    </p>
                </div>
            </div>
        </main>

    </div>

</body>
</html>