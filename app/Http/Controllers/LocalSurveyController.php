<?php

namespace App\Http\Controllers;

use App\Models\Province;
use Illuminate\View\View;

class LocalSurveyController extends Controller
{
    protected const TOTAL_STEPS = 10;

    protected const STEP_TITLES = [
        1 => 'Informed Consent',
        2 => 'Farmer Socio-Demographic Profile',
        3 => 'Farm Information',
        4 => 'Training, Information Sources & Finance',
        5 => 'Seed Selection Criteria',
        6 => 'Seed Variety Preference',
        7 => 'Varieties Planted',
        8 => 'Government Seed Subsidy',
        9 => 'Issues and Recommendations',
        10 => 'Final Confirmation',
    ];

    /**
     * Render the step shell. NOTE: this deliberately does NOT hydrate any
     * survey data server-side — there is no session and no {farmer} model
     * anymore. The page loads with empty/default markup, then
     * local-first-init.blade.php's JS reads the actual answers out of
     * IndexedDB (keyed by $uuid) and fills the form once the page is ready.
     * This is what makes the page work identically online or offline.
     */
    public function showStep(string $uuid, int $step): View
    {
        abort_if($step < 1 || $step > self::TOTAL_STEPS, 404);

        return view("surveys.steps.step{$step}", [
            'uuid' => $uuid,
            'currentStep' => $step,
            'totalSteps' => self::TOTAL_STEPS,
            'stepTitle' => self::STEP_TITLES[$step],
            'progressPercent' => (int) round(($step / self::TOTAL_STEPS) * 100),
            // Step 2 now also needs $provinces for the Farmer's Address
            // cascading dropdown (farmer_province_id / _municipality_id /
            // _barangay_id), same pattern as Step 1's enumerator location
            // and Step 3's farm location.
            'provinces' => in_array($step, [1, 2, 3], true) ? Province::orderBy('name')->get() : collect(),
            // old_data / $farmer no longer exist in this flow. Any Blade
            // markup still referencing them needs updating — see the
            // per-step conversion notes.
            'old_data' => [],
        ]);
    }

    public function review(string $uuid): View
    {
        return view('surveys.local-review', ['uuid' => $uuid]);
    }
}
