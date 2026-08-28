@extends('surveys.wizard')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-3 mb-1">
        <div class="flex-shrink-0 w-11 h-11 rounded-full bg-da-green-600 text-white flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
            </svg>
        </div>
        <div>
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Review Your Answers</h2>
            <p class="text-sm text-gray-600">Please verify all information before final submission.</p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('surveys.submit', ['farmer' => $farmer]) }}" novalidate>
    @csrf

    <div class="space-y-6">

        {{-- ===================== CONSENT & ENUMERATOR ===================== --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between bg-da-green-50 border-b border-da-green-100 px-6 py-4">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-full bg-da-green-600 text-white text-sm flex items-center justify-center font-bold">1</span>
                    Informed Consent
                </h3>
                <a href="{{ route('surveys.step.show', ['farmer' => $farmer, 'step' => 1]) }}" class="text-sm font-semibold text-da-green-700 hover:text-da-green-800 hover:underline flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                    </svg>
                    Edit
                </a>
            </div>
            <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm sm:text-base">
                <div class="flex justify-between sm:block">
                    <span class="text-gray-500">Assisted by DA Personnel</span>
                    <span class="font-semibold text-gray-900 sm:block">{{ ucfirst($data['assisted_by_da'] ?? '—') }}</span>
                </div>
                <div class="flex justify-between sm:block">
                    <span class="text-gray-500">Consent Given</span>
                    <span class="font-semibold text-da-green-700 sm:block">
                        {{ (($data['consent_voluntary'] ?? false) && ($data['consent_data_privacy'] ?? false) && ($data['consent_accurate_info'] ?? false)) ? 'Yes, all conditions accepted' : 'Incomplete' }}
                    </span>
                </div>

                @if (($data['assisted_by_da'] ?? null) == 'yes')
                    <div class="flex justify-between sm:block">
                        <span class="text-gray-500">Enumerator Name</span>
                        <span class="font-semibold text-gray-900 sm:block">{{ $data['enumerator_name'] ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between sm:block">
                        <span class="text-gray-500">Position</span>
                        <span class="font-semibold text-gray-900 sm:block">{{ $data['enumerator_position'] ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between sm:block">
                        <span class="text-gray-500">Office</span>
                        <span class="font-semibold text-gray-900 sm:block">{{ $data['enumerator_office'] ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between sm:block">
                        <span class="text-gray-500">Date of Survey</span>
                        <span class="font-semibold text-gray-900 sm:block">{{ $data['survey_date'] ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between sm:block sm:col-span-2">
                        <span class="text-gray-500">Survey Location</span>
                        <span class="font-semibold text-gray-900 sm:block">
                            {{ collect([
                                \App\Support\PlaceName::clean(optional(\App\Models\Barangay::find($data['enum_barangay_id'] ?? null))->name),
                                \App\Support\PlaceName::clean(optional(\App\Models\Municipality::find($data['enum_municipality_id'] ?? null))->name),
                                \App\Support\PlaceName::clean(optional(\App\Models\Province::find($data['enum_province_id'] ?? null))->name),
                            ])->filter()->implode(', ') ?: '—' }}
                        </span>
                    </div>
                    <div class="flex justify-between sm:block">
                        <span class="text-gray-500">Cropping System</span>
                        <span class="font-semibold text-gray-900 sm:block">{{ ucfirst($data['cropping_system'] ?? '—') }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- ===================== FARMER ===================== --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between bg-da-green-50 border-b border-da-green-100 px-6 py-4">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-full bg-da-green-600 text-white text-sm flex items-center justify-center font-bold">2</span>
                    Farmer Socio-Demographic Profile
                </h3>
                <a href="{{ route('surveys.step.show', ['farmer' => $farmer, 'step' => 2]) }}" class="text-sm font-semibold text-da-green-700 hover:text-da-green-800 hover:underline flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                    </svg>
                    Edit
                </a>
            </div>
            <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-3 gap-x-8 gap-y-3 text-sm sm:text-base">
                <div class="flex justify-between sm:block sm:col-span-3">
                    <span class="text-gray-500">Full Name</span>
                    <span class="font-semibold text-gray-900 sm:block">
                        {{ collect([$data['first_name'] ?? null, $data['middle_name'] ?? null, $data['last_name'] ?? null, $data['suffix'] ?? null])->filter()->implode(' ') ?: '—' }}
                    </span>
                </div>
                <div class="flex justify-between sm:block">
                    <span class="text-gray-500">RSBSA Number</span>
                    <span class="font-semibold text-gray-900 sm:block">{{ $data['rsbsa_number'] ?? '—' }}</span>
                </div>
                <div class="flex justify-between sm:block">
                    <span class="text-gray-500">Age / Sex</span>
                    <span class="font-semibold text-gray-900 sm:block">{{ $data['age'] ?? '—' }} / {{ ucfirst($data['sex'] ?? '—') }}</span>
                </div>
                <div class="flex justify-between sm:block">
                    <span class="text-gray-500">Contact Number</span>
                    <span class="font-semibold text-gray-900 sm:block">{{ $data['contact_number'] ?? '—' }}</span>
                </div>
                <div class="flex justify-between sm:block">
                    <span class="text-gray-500">Civil Status</span>
                    <span class="font-semibold text-gray-900 sm:block">{{ $data['civil_status'] ?? '—' }}</span>
                </div>
                <div class="flex justify-between sm:block">
                    <span class="text-gray-500">Educational Attainment</span>
                    <span class="font-semibold text-gray-900 sm:block">{{ $data['educational_attainment'] ?? '—' }}</span>
                </div>
                <div class="flex justify-between sm:block">
                    <span class="text-gray-500">Ethnicity</span>
                    <span class="font-semibold text-gray-900 sm:block">
                        {{ ($data['ethnicity'] ?? null) === 'others' ? ($data['ethnicity_others'] ?? '—') : ($data['ethnicity'] ?? '—') }}
                    </span>
                </div>
                <div class="flex justify-between sm:block">
                    <span class="text-gray-500">Crop Type</span>
                    <span class="font-semibold text-gray-900 sm:block">{{ ucfirst($data['crop_type'] ?? '—') }}</span>
                </div>
            </div>
        </div>

        {{-- ===================== FARM ===================== --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between bg-da-green-50 border-b border-da-green-100 px-6 py-4">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-full bg-da-green-600 text-white text-sm flex items-center justify-center font-bold">3</span>
                    Farm Information
                </h3>
                <a href="{{ route('surveys.step.show', ['farmer' => $farmer, 'step' => 3]) }}" class="text-sm font-semibold text-da-green-700 hover:text-da-green-800 hover:underline flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                    </svg>
                    Edit
                </a>
            </div>
            <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-3 gap-x-8 gap-y-3 text-sm sm:text-base">
                <div class="flex justify-between sm:block sm:col-span-3">
                    <span class="text-gray-500">Farm Location</span>
                    <span class="font-semibold text-gray-900 sm:block">
                        {{ collect([
                            \App\Support\PlaceName::clean(optional(\App\Models\Barangay::find($data['farm_barangay_id'] ?? null))->name),
                            \App\Support\PlaceName::clean(optional(\App\Models\Municipality::find($data['farm_municipality_id'] ?? null))->name),
                            \App\Support\PlaceName::clean(optional(\App\Models\Province::find($data['farm_province_id'] ?? null))->name),
                        ])->filter()->implode(', ') ?: '—' }}
                    </span>
                </div>
                <div class="flex justify-between sm:block">
                    <span class="text-gray-500">Farm Area</span>
                    <span class="font-semibold text-gray-900 sm:block">
                        {{ $data['farm_area'] ?? '—' }} ha
                        <span class="block text-xs text-gray-500 font-normal mt-0.5">
                            Rice (Dry: {{ $data['farm_area_rice_dry'] ?? 0 }} / Wet: {{ $data['farm_area_rice_wet'] ?? 0 }}) &middot;
                            Corn (Dry: {{ $data['farm_area_corn_dry'] ?? 0 }} / Wet: {{ $data['farm_area_corn_wet'] ?? 0 }}) &middot;
                            HVC: {{ $data['farm_area_hvc'] ?? 0 }}
                        </span>
                    </span>
                </div>
                <div class="flex justify-between sm:block">
                    <span class="text-gray-500">Tenurial Status</span>
                    <span class="font-semibold text-gray-900 sm:block">{{ $data['tenurial_status'] ?? '—' }}</span>
                </div>
                <div class="flex justify-between sm:block">
                    <span class="text-gray-500">Years in Farming</span>
                    <span class="font-semibold text-gray-900 sm:block">{{ $data['years_farming'] ?? '—' }}</span>
                </div>
                <div class="flex justify-between sm:block">
                    <span class="text-gray-500">Household Size</span>
                    <span class="font-semibold text-gray-900 sm:block">{{ $data['household_size'] ?? '—' }}</span>
                </div>
                <div class="flex justify-between sm:block">
                    <span class="text-gray-500">Occupation</span>
                    <span class="font-semibold text-gray-900 sm:block">{{ $data['occupation'] ?? '—' }}</span>
                </div>
                <div class="flex justify-between sm:block">
                    <span class="text-gray-500">Annual Income</span>
                    <span class="font-semibold text-gray-900 sm:block">
                        {{ isset($data['annual_income']) ? '₱' . number_format($data['annual_income'], 2) : '—' }}
                    </span>
                </div>
                <div class="flex justify-between sm:block sm:col-span-3">
                    <span class="text-gray-500">Organization Membership</span>
                    <span class="font-semibold text-gray-900 sm:block">
                        @if (($data['organization_membership'] ?? null) == 'yes')
                            {{ $data['organization_name'] ?? 'Yes' }}
                            @if (!empty($data['organization_status']))
                                &mdash; {{ $data['organization_status'] === 'registered' ? 'Registered' : 'Not Registered' }}
                            @endif
                            @if (!empty($data['organization_registered_with']))
                                ({{ collect($data['organization_registered_with'])->implode(', ') }})
                            @endif
                        @else
                            No
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- ===================== TRAINING, INFORMATION, INCOME & FINANCE ===================== --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between bg-da-green-50 border-b border-da-green-100 px-6 py-4">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-full bg-da-green-600 text-white text-sm flex items-center justify-center font-bold">4</span>
                    Training, Information Sources, Income &amp; Finance
                </h3>
                <a href="{{ route('surveys.step.show', ['farmer' => $farmer, 'step' => 4]) }}" class="text-sm font-semibold text-da-green-700 hover:text-da-green-800 hover:underline flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                    </svg>
                    Edit
                </a>
            </div>
            <div class="px-6 py-5 space-y-4 text-sm sm:text-base">
                <div>
                    <span class="text-gray-500 block mb-2">Trainings Attended</span>
                    @php
                        $trainings = collect($data['trainings_attended'] ?? [])->filter(fn ($t) => !empty($t['title'] ?? null));
                    @endphp
                    @forelse ($trainings as $training)
                        <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-2.5 mb-2">
                            <p class="font-semibold text-gray-900">{{ $training['title'] }}</p>
                            <p class="text-gray-600 text-xs mt-0.5">
                                {{ $training['conducted_by'] ?? '—' }} &middot; {{ $training['month_year'] ?? '—' }}
                            </p>
                        </div>
                    @empty
                        <span class="text-gray-500">None recorded.</span>
                    @endforelse
                </div>
                <div>
                    <span class="text-gray-500 block mb-1">Information Sources</span>
                    <span class="font-semibold text-gray-900">
                        {{ collect($data['information_sources'] ?? [])
                            ->map(fn ($s) => $s === 'others' ? ($data['information_sources_others'] ?? 'Others') : $s)
                            ->implode(', ') ?: '—' }}
                    </span>
                </div>
                <div>
                    <span class="text-gray-500 block mb-1">Preferred IEC Materials</span>
                    <span class="font-semibold text-gray-900">
                        {{ collect($data['preferred_iec_materials'] ?? [])
                            ->map(fn ($s) => $s === 'others' ? ($data['preferred_iec_materials_others'] ?? 'Others') : $s)
                            ->implode(', ') ?: '—' }}
                    </span>
                </div>

                <hr class="border-gray-100">

                <div>
                    <span class="text-gray-500 block mb-1">Primary Source of Income</span>
                    <span class="font-semibold text-gray-900">
                        {{ $data['income_source_primary'] ?? '—' }}
                        @if (($data['income_source_primary'] ?? null) === 'farming')
                            <span class="block text-xs text-gray-500 font-normal mt-0.5">
                                Dry: ₱{{ number_format($data['income_primary_dry'] ?? 0, 2) }} &middot;
                                Wet: ₱{{ number_format($data['income_primary_wet'] ?? 0, 2) }} &middot;
                                Monthly Est.: ₱{{ number_format($data['income_primary_monthly'] ?? 0, 2) }}
                            </span>
                        @endif
                    </span>
                </div>
                @if (!empty($data['income_source_secondary']))
                    <div>
                        <span class="text-gray-500 block mb-1">Secondary Source of Income</span>
                        <span class="font-semibold text-gray-900">
                            {{ $data['income_source_secondary'] }}
                            @if ($data['income_source_secondary'] === 'farming')
                                <span class="block text-xs text-gray-500 font-normal mt-0.5">
                                    Dry: ₱{{ number_format($data['income_secondary_dry'] ?? 0, 2) }} &middot;
                                    Wet: ₱{{ number_format($data['income_secondary_wet'] ?? 0, 2) }} &middot;
                                    Monthly Est.: ₱{{ number_format($data['income_secondary_monthly'] ?? 0, 2) }}
                                </span>
                            @endif
                        </span>
                    </div>
                @endif

                <hr class="border-gray-100">

                <div>
                    <span class="text-gray-500 block mb-2">Source of Finance</span>
                    @php
                        $financeSources = collect($data['finance_sources'] ?? [])->filter(fn ($f) => !empty($f['source'] ?? null));
                    @endphp
                    @forelse ($financeSources as $finance)
                        <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-2.5 mb-2 flex items-center justify-between">
                            <span class="text-gray-900 font-medium">{{ $finance['source'] }}</span>
                            <span class="text-gray-600 text-xs">
                                @if (!empty($finance['amount']))
                                    ₱{{ number_format($finance['amount'], 2) }}
                                @endif
                                @if (!empty($finance['interest']))
                                    &middot; {{ $finance['interest'] }}% interest
                                @endif
                            </span>
                        </div>
                    @empty
                        <span class="text-gray-500">None recorded.</span>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ===================== SEED SELECTION CRITERIA & VARIETY PREFERENCES ===================== --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between bg-da-green-50 border-b border-da-green-100 px-6 py-4">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-full bg-da-green-600 text-white text-sm flex items-center justify-center font-bold">5</span>
                    Seed Preferences
                </h3>
                <a href="{{ route('surveys.step.show', ['farmer' => $farmer, 'step' => 5]) }}" class="text-sm font-semibold text-da-green-700 hover:text-da-green-800 hover:underline flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                    </svg>
                    Edit
                </a>
            </div>
            <div class="px-6 py-5 space-y-4 text-sm sm:text-base">
                <div>
                    <span class="text-gray-500 block mb-1">Selection Criteria</span>
                    <span class="font-semibold text-gray-900">{{ collect($data['seed_criteria'] ?? [])->implode(', ') ?: '—' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block mb-2">Preferred Varieties</span>
                    @php
                        $groupLabels = [
                            'dry' => ['hybrid' => 'Dry Season — Hybrid', 'inbred' => 'Dry Season — Inbred'],
                            'wet' => ['hybrid' => 'Wet Season — Hybrid', 'inbred' => 'Wet Season — Inbred'],
                        ];
                        $anyVariety = false;
                    @endphp
                    @foreach (['dry', 'wet'] as $season)
                        @foreach (['hybrid', 'inbred'] as $seedType)
                            @php
                                $entries = collect(data_get($data, "preferences.{$season}.{$seedType}", []))
                                    ->filter(fn ($e) => !empty($e['variety'] ?? null))
                                    ->values();
                            @endphp
                            @if ($entries->isNotEmpty())
                                @php $anyVariety = true; @endphp
                                <p class="text-xs font-bold text-gray-500 uppercase mt-3 mb-1.5">{{ $groupLabels[$season][$seedType] }}</p>
                                @foreach ($entries as $i => $pref)
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 mb-2">
                                        <p class="font-semibold text-gray-900">
                                            #{{ $i + 1 }} &mdash; {{ $pref['variety'] }}
                                        </p>
                                        <p class="text-gray-600 text-xs mt-0.5">
                                            @if (!empty($pref['source_of_information']))
                                                Source: {{ $pref['source_of_information'] }} &middot;
                                            @endif
                                            @if (!empty($pref['actual_yield']))
                                                Yield: {{ $pref['actual_yield'] }} t/ha &middot;
                                            @endif
                                            @if (!empty($pref['area_planted']))
                                                Area: {{ $pref['area_planted'] }} ha &middot;
                                            @endif
                                            @if (!empty($pref['maturity_days']))
                                                {{ $pref['maturity_days'] }} days maturity
                                            @endif
                                        </p>
                                        @if (!empty($pref['reasons']))
                                            <p class="text-gray-600 text-sm mt-1">Reasons: {{ collect($pref['reasons'])->implode(', ') }}</p>
                                        @endif
                                        @if (!empty($pref['problems']))
                                            <p class="text-gray-600 text-sm mt-1">Problems: {{ collect($pref['problems'])->implode(', ') }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        @endforeach
                    @endforeach
                    @if (! $anyVariety)
                        <span class="text-gray-500">No preferences recorded.</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- ===================== VARIETIES PLANTED ===================== --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between bg-da-green-50 border-b border-da-green-100 px-6 py-4">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-full bg-da-green-600 text-white text-sm flex items-center justify-center font-bold">6</span>
                    Varieties Planted (CY 2024–2025)
                </h3>
                <a href="{{ route('surveys.step.show', ['farmer' => $farmer, 'step' => 7]) }}" class="text-sm font-semibold text-da-green-700 hover:text-da-green-800 hover:underline flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                    </svg>
                    Edit
                </a>
            </div>
            <div class="px-6 py-5 space-y-2 text-sm sm:text-base">
                @forelse (($data['planted'] ?? []) as $entry)
                    @if (!empty($entry['crop']))
                        <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-2.5 flex flex-wrap items-center justify-between gap-2">
                            <span class="text-gray-900 font-medium">
                                {{ ucfirst($entry['season'] ?? '') }} &mdash; {{ $entry['crop'] }} ({{ $entry['variety'] ?? '—' }})
                            </span>
                            <span class="text-gray-600 text-xs">
                                {{ $entry['area'] ?? '—' }} ha &middot; {{ $entry['yield'] ?? '—' }} t yield
                            </span>
                        </div>
                    @endif
                @empty
                    <span class="text-gray-500">No planting records.</span>
                @endforelse
            </div>
        </div>

        {{-- ===================== GOVERNMENT ASSISTANCE ===================== --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between bg-da-green-50 border-b border-da-green-100 px-6 py-4">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-full bg-da-green-600 text-white text-sm flex items-center justify-center font-bold">7</span>
                    Government Seed Subsidy
                </h3>
                <div class="flex gap-3">
                    <a href="{{ route('surveys.step.show', ['farmer' => $farmer, 'step' => 8]) }}" class="text-sm font-semibold text-da-green-700 hover:text-da-green-800 hover:underline flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                        </svg>
                        Edit
                    </a>
                </div>
            </div>
            <div class="px-6 py-5 space-y-4 text-sm sm:text-base">
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Received Seed Subsidy</span>
                    <span class="font-semibold text-gray-900">{{ ucfirst($data['received_subsidy'] ?? '—') }}</span>
                </div>

                @if (($data['received_subsidy'] ?? null) == 'yes')
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Used the Subsidized Seed</span>
                        <span class="font-semibold text-gray-900">{{ ucfirst($data['used_subsidized_seed'] ?? '—') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Preferred by Traders/Millers</span>
                        <span class="font-semibold text-gray-900">{{ ucfirst($data['preferred_by_traders'] ?? '—') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Willing to Accept Other Variety</span>
                        <span class="font-semibold text-gray-900">{{ ucfirst($data['willing_to_accept_other_variety'] ?? '—') }}</span>
                    </div>

                    @if (!empty($data['subsidy_history']))
                        <div>
                            <span class="text-gray-500 block mb-2">Subsidy History</span>
                            @foreach ($data['subsidy_history'] as $entry)
                                @if (!empty($entry['year']))
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-2.5 mb-2 flex items-center justify-between">
                                        <span class="text-gray-700">{{ $entry['year'] }} &mdash; {{ ucfirst($entry['season'] ?? '') }} Season</span>
                                        <span class="text-gray-900 font-medium">{{ collect($entry['source'] ?? [])->implode(', ') ?: '—' }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    @if (!empty($data['subsidy_problems']))
                        <div>
                            <span class="text-gray-500 block mb-1">Problems on Subsidy</span>
                            <span class="font-semibold text-gray-900">{{ collect($data['subsidy_problems'])->implode(', ') ?: '—' }}</span>
                        </div>
                    @endif
                @endif

                @if (!empty($data['subsidy_recommendation']))
                    <div>
                        <span class="text-gray-500 block mb-1">Recommendation to Improve Subsidy</span>
                        <p class="text-gray-900">{{ $data['subsidy_recommendation'] }}</p>
                    </div>
                @endif

                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Capable to Buy Own Seed Without Subsidy</span>
                    <span class="font-semibold text-gray-900">{{ ucfirst($data['capable_to_buy_own_seed'] ?? '—') }}</span>
                </div>
            </div>
        </div>

        {{-- ===================== ISSUES ===================== --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between bg-da-green-50 border-b border-da-green-100 px-6 py-4">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-full bg-da-green-600 text-white text-sm flex items-center justify-center font-bold">8</span>
                    Issues and Recommendations
                </h3>
                <a href="{{ route('surveys.step.show', ['farmer' => $farmer, 'step' => 9]) }}" class="text-sm font-semibold text-da-green-700 hover:text-da-green-800 hover:underline flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                    </svg>
                    Edit
                </a>
            </div>
            <div class="px-6 py-5 space-y-4 text-sm sm:text-base">
                <div>
                    <span class="text-gray-500 block mb-1">Problems Encountered</span>
                    <p class="text-gray-900">
                        {{ collect($data['problems_encountered'] ?? [])->implode(', ') ?: '—' }}
                        @if (!empty($data['problems_encountered_others']))
                            &mdash; {{ $data['problems_encountered_others'] }}
                        @endif
                    </p>
                </div>
                <div>
                    <span class="text-gray-500 block mb-1">Recommendations</span>
                    <p class="text-gray-900">{{ $data['recommendations'] ?? '—' }}</p>
                </div>
                @if (!empty($data['additional_comments']))
                    <div>
                        <span class="text-gray-500 block mb-1">Additional Comments</span>
                        <p class="text-gray-900">{{ $data['additional_comments'] }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Submit Bar --}}
    <div class="sticky bottom-0 mt-8 bg-white border border-gray-200 rounded-2xl shadow-lg px-6 sm:px-8 py-5 flex flex-col-reverse sm:flex-row items-center justify-between gap-3">
        <a href="{{ route('surveys.step.show', ['farmer' => $farmer, 'step' => 10]) }}"
           class="w-full sm:w-auto text-center px-6 py-3.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold text-base hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-400 transition flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Back
        </a>
        <button type="submit"
                class="w-full sm:w-auto px-10 py-3.5 rounded-xl bg-da-green-600 text-white font-bold text-base shadow-sm hover:bg-da-green-700 focus:outline-none focus:ring-2 focus:ring-da-green-500 focus:ring-offset-2 transition flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
            Submit Survey
        </button>
    </div>
</form>
@endsection