@extends('surveys.wizard')

@section('content')
<div class="max-w-2xl mx-auto text-center py-8 sm:py-12">

    {{-- Success Icon --}}
    <div class="flex justify-center mb-6">
        <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-da-green-100 flex items-center justify-center">
            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-da-green-600 flex items-center justify-center shadow-lg">
                <svg class="w-9 h-9 sm:w-11 sm:h-11 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </div>
        </div>
    </div>

    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-3">
        Survey Submitted Successfully!
    </h1>

    <p class="text-base sm:text-lg text-gray-600 leading-relaxed mb-2">
        Thank you for participating in the EZ-Seed Farmer-Centered Seed Preference Survey.
    </p>
    <p class="text-sm sm:text-base text-gray-500 leading-relaxed mb-8">
        Your responses have been recorded and will help the Department of Agriculture &ndash; Regional Field Office No. 02
        design better seed distribution programs for farmers like you. We sincerely appreciate your time and cooperation.
    </p>

    @isset($referenceNumber)
        <div class="inline-flex flex-col items-center bg-da-green-50 border border-da-green-200 rounded-xl px-6 py-4 mb-8">
            <span class="text-xs uppercase tracking-wide text-da-green-700 font-semibold mb-1">Reference Number</span>
            <span class="text-lg sm:text-xl font-bold text-da-green-800 font-mono">{{ $referenceNumber }}</span>
        </div>
    @endisset

    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
        <a href="{{ route('surveys.start') }}"
           class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-da-green-600 text-white font-semibold text-base shadow-sm hover:bg-da-green-700 focus:outline-none focus:ring-2 focus:ring-da-green-500 focus:ring-offset-2 transition flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Start New Survey
        </a>
        {{-- This page is public / farmer-facing (no auth). It should NOT
             link to the auth-protected DA dashboard — that only "worked"
             during testing because the tester happened to be logged in
             as an Admin in the same browser session. --}}
        <a href="{{ route('landing') }}"
           class="w-full sm:w-auto px-8 py-3.5 rounded-xl border-2 border-da-green-600 text-da-green-700 font-semibold text-base hover:bg-da-green-50 focus:outline-none focus:ring-2 focus:ring-da-green-500 transition flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
            Return to Home
        </a>
    </div>

    <p class="mt-10 text-xs sm:text-sm text-gray-400">
        For inquiries regarding this survey, please contact your Municipal Agriculture Office or the
        DA Regional Field Office No. 02.
    </p>
</div>
@endsection