<?php

namespace App\Http\Controllers;

use App\Models\Barangay;
use App\Models\DAPersonnel;
use App\Models\Farmer;
use App\Models\Municipality;
use App\Models\Province;
use App\Support\PlaceName;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SurveyController extends Controller
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


    public function start(): RedirectResponse
    {
        $farmer = Farmer::create([
            'first_name' => '',
            'last_name' => '',
        ]);

        return redirect()->route('surveys.step.show', ['farmer' => $farmer, 'step' => 1]);
    }

    /**
     * NEW: Enumerator code lookup — called via fetch() from Step 1 when the
     * enumerator types their code. Returns their saved info so the form can
     * auto-fill name/position/office/location instead of retyping every time.
     * Returns 404 JSON (not an error page) when the code isn't found yet,
     * since that's an expected/normal case for a first-time code.
     */
    public function lookupEnumerator(string $code): JsonResponse
    {
        $personnel = DAPersonnel::where('code', $code)->first();

        if (! $personnel) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found' => true,
            'name' => $personnel->name,
            'position' => $personnel->position,
            'office' => $personnel->office,
            // province/municipality/barangay aren't columns on DAPersonnel
            // today (only a free-text 'province' string exists), so those
            // three location selects still need to be picked manually.
        ]);
    }

    public function showStep(Farmer $farmer, int $step): View|RedirectResponse
    {
        $this->guardStepNumber($step);

        if ($redirect = $this->redirectIfStepLocked($farmer, $step)) {
            return $redirect;
        }

        $data = $this->getSessionData($farmer);

        $viewData = [
            'farmer' => $farmer,
            'currentStep' => $step,
            'totalSteps' => self::TOTAL_STEPS,
            'stepTitle' => self::STEP_TITLES[$step],
            'progressPercent' => (int) round(($step / self::TOTAL_STEPS) * 100),
        ];


        if (in_array($step, [1, 3], true)) {
            $viewData['provinces'] = Province::orderBy('name')->get();
        }

        // Step 10 (Final Confirmation) needs a quick summary snapshot, and
        // needs to know whether the farmer answered alone (no DA assistance)
        // so it can show the proof-of-interview photo/signature block here
        // instead of Step 1 (which already shows it for the assisted case).
        if ($step === 10) {
            $viewData['summary'] = $this->buildSummarySnapshot($farmer, $data);
            $viewData['needsProofHere'] = ($data['assisted_by_da'] ?? null) !== 'yes';
        }

        return view("surveys.steps.step{$step}", $viewData)
            ->with('old_data', $data);
    }

    public function storeStep(Request $request, Farmer $farmer, int $step): RedirectResponse
    {
        $this->guardStepNumber($step);

        if ($redirect = $this->redirectIfStepLocked($farmer, $step)) {
            return $redirect;
        }

        if ($step === 1 && $request->input('action') === 'disagree') {
            $this->resetSession($farmer);

            return redirect()
                ->route('surveys.step.show', ['farmer' => $farmer, 'step' => 1])
                ->with('error', 'You must agree to the informed consent to proceed with the survey.');
        }

        $validated = $request->validate(
            $this->rulesForStep($step),
            [],
            $this->attributesForStep($step)
        );

        $validated = $this->normalizeStepData($step, $validated);

        $this->mergeSessionData($farmer, $step, $validated);
        $this->markStepComplete($farmer, $step);


        if ($step === 2) {
            $farmer->update([
                // Only keep an RSBSA number on record if the farmer is actually
                // an RSBSA member; otherwise clear it out rather than silently
                // retaining a stale value from a previous "Yes" answer.
                'rsbsa_number' => ($validated['is_rsbsa_member'] ?? null) === 'yes'
                    ? ($validated['rsbsa_number'] ?? $farmer->rsbsa_number)
                    : null,
                'first_name' => $validated['first_name'] ?? $farmer->first_name,
                'middle_name' => $validated['middle_name'] ?? $farmer->middle_name,
                'last_name' => $validated['last_name'] ?? $farmer->last_name,
                'suffix' => $validated['suffix'] ?? $farmer->suffix,
                'birth_date' => $validated['birthdate'] ?? $farmer->birth_date,
                'sex' => $validated['sex'] ?? $farmer->sex,
                'civil_status' => $validated['civil_status'] ?? $farmer->civil_status,
                'contact_number' => $validated['contact_number'] ?? $farmer->contact_number,
            ]);
        }

        if ($step === 3) {
            $farmer->update([
                'province_id' => $validated['farm_province_id'] ?? $farmer->province_id,
                'municipality_id' => $validated['farm_municipality_id'] ?? $farmer->municipality_id,
                'barangay_id' => $validated['farm_barangay_id'] ?? $farmer->barangay_id,
                'farm_area' => $validated['farm_area'] ?? $farmer->farm_area,
                'tenurial_status' => $validated['tenurial_status'] ?? $farmer->tenurial_status,
            ]);
        }

        if ($step === self::TOTAL_STEPS) {
            return redirect()->route('surveys.review', ['farmer' => $farmer]);
        }

        return redirect()->route('surveys.step.show', ['farmer' => $farmer, 'step' => $step + 1]);
    }

    public function review(Farmer $farmer): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfStepLocked($farmer, self::TOTAL_STEPS)) {
            return $redirect;
        }

        $data = $this->getSessionData($farmer);

        return view('surveys.review', [
            'farmer' => $farmer,
            'data' => $data,
        ]);
    }


    public function submit(Request $request, Farmer $farmer): RedirectResponse
    {
        $data = $this->getSessionData($farmer);

        if (empty($data)) {
            return redirect()
                ->route('surveys.step.show', ['farmer' => $farmer, 'step' => 1])
                ->with('error', 'Your survey session has expired. Please start again.');
        }

        if (empty($data['certification'])) {
            return redirect()
                ->route('surveys.step.show', ['farmer' => $farmer, 'step' => 10])
                ->with('error', 'You must certify that the information provided is true before submitting.');
        }

        $result = $this->createSurveyFromPayload($farmer, $data);

        session()->flash('reference_number', $result['reference_number']);
        session()->flash('success', 'Survey submitted successfully. Thank you for your participation!');

        $this->resetSession($farmer);

        return redirect()->route('surveys.completed', ['farmer' => $farmer]);
    }


    protected function mapSeason(?string $season): string
    {
        return $season === 'wet' ? 'Wet Season' : 'Dry Season';
    }


    protected function mapSeedType(?string $seedType): string
    {
        return $seedType === 'hybrid' ? 'Hybrid' : 'Inbred';
    }


    protected function categorizeReason(?string $reason): string
    {
        if (empty($reason)) {
            return 'Others';
        }

        $reason = strtolower($reason);

        return match (true) {
            str_contains($reason, 'yield') => 'High Yield',
            str_contains($reason, 'pest') || str_contains($reason, 'disease') || str_contains($reason, 'resist') => 'Pest Resistance',
            str_contains($reason, 'adapt') || str_contains($reason, 'climate') || str_contains($reason, 'soil') => 'Adaptability',
            str_contains($reason, 'grain') || str_contains($reason, 'quality') || str_contains($reason, 'taste') => 'Grain Quality',
            str_contains($reason, 'early') || str_contains($reason, 'matur') || str_contains($reason, 'fast') => 'Early Maturing',
            default => 'Others',
        };
    }

    /**
     * Show the success/completion page.
     */
    public function completed(Farmer $farmer): View
    {
        return view('surveys.completed', [
            'farmer' => $farmer,
            'referenceNumber' => session('reference_number'),
        ]);
    }

    /**
     * Abandon the current survey session and start over from step 1.
     */
    public function restart(Farmer $farmer): RedirectResponse
    {
        $this->resetSession($farmer);

        return redirect()->route('surveys.step.show', ['farmer' => $farmer, 'step' => 1]);
    }

    /* =====================================================================
     | Validation Rules
     |====================================================================*/

    protected function rulesForStep(int $step): array
    {
        return match ($step) {


            1 => [
                'assisted_by_da' => ['required', 'in:yes,no'],

                // NEW: optional code — when provided and found, the client
                // auto-fills the fields below via lookupEnumerator(); the
                // fields themselves stay editable and are still what's
                // actually validated/saved.
                'enumerator_code' => ['nullable', 'string', 'max:50'],

                'enumerator_name' => ['required_if:assisted_by_da,yes', 'nullable', 'string', 'max:255'],
                'enumerator_position' => ['required_if:assisted_by_da,yes', 'nullable', 'string', 'max:255'],
                'enumerator_office' => ['required_if:assisted_by_da,yes', 'nullable', 'string', 'max:255'],
                'survey_date' => ['required_if:assisted_by_da,yes', 'nullable', 'date'],
                'enum_province_id' => ['required_if:assisted_by_da,yes', 'nullable', 'exists:provinces,id'],
                'enum_municipality_id' => ['required_if:assisted_by_da,yes', 'nullable', 'exists:municipalities,id'],
                'enum_barangay_id' => ['required_if:assisted_by_da,yes', 'nullable', 'exists:barangays,id'],
                'cropping_system' => ['required_if:assisted_by_da,yes', 'nullable', 'in:irrigated,rainfed'],

                'consent_voluntary' => ['required', 'accepted'],
                'consent_data_privacy' => ['required', 'accepted'],
                'consent_accurate_info' => ['required', 'accepted'],

                // NEW: proof of interview when DA-assisted. Required only in
                // that case — captured right here in Step 1.
                'proof_photo' => ['required_if:assisted_by_da,yes', 'nullable', 'string'],
                'proof_signature' => ['required_if:assisted_by_da,yes', 'nullable', 'string'],
            ],


            2 => [
                'is_rsbsa_member' => ['required', 'in:yes,no'],
                'rsbsa_number' => ['required_if:is_rsbsa_member,yes', 'nullable', 'string', 'max:100'],
                'first_name' => ['required', 'string', 'max:255'],
                'middle_name' => ['nullable', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'suffix' => ['nullable', 'string', 'max:20'],
                'birthdate' => ['required', 'date', 'before:today'],
                'age' => ['required', 'integer', 'min:18', 'max:120'],
                'sex' => ['required', 'in:male,female'],
                'civil_status' => ['required', 'in:Single,Married,Widowed,Separated,Divorced'],
                'ethnicity' => ['nullable', 'string', 'max:255'],
                'ethnicity_others' => ['nullable', 'string', 'max:255'],
                'educational_attainment' => ['required', 'string', 'max:255'],
                // CHANGED: contact number is now optional.
                'contact_number' => ['nullable', 'string', 'max:20'],
                'crop_type' => ['required', 'in:rice,corn,both'],
            ],

            // Step 3: Farm Information — Rice & Corn split by season.
            // NOTE: farm_area_hvc / HVC removed per request — this rule
            // stays here only if your Step 3 blade still references it.
            // If you've removed the HVC field from step-3.blade.php, also
            // delete the 'farm_area_hvc' line below.
            3 => [
                'farm_province_id' => ['required', 'exists:provinces,id'],
                'farm_municipality_id' => ['required', 'exists:municipalities,id'],
                'farm_barangay_id' => ['required', 'exists:barangays,id'],
                'farm_area' => ['required', 'numeric', 'min:0'],
                'farm_area_rice_dry' => ['nullable', 'numeric', 'min:0'],
                'farm_area_rice_wet' => ['nullable', 'numeric', 'min:0'],
                'farm_area_corn_dry' => ['nullable', 'numeric', 'min:0'],
                'farm_area_corn_wet' => ['nullable', 'numeric', 'min:0'],
                'tenurial_status' => ['required', 'string', 'max:255'],
                'years_farming' => ['required', 'integer', 'min:0', 'max:100'],
                'household_size' => ['required', 'integer', 'min:1', 'max:30'],
                'occupation' => ['required', 'string', 'max:255'],
                'annual_income' => ['required', 'numeric', 'min:0'],
                'organization_membership' => ['required', 'in:yes,no'],
                'organization_name' => ['required_if:organization_membership,yes', 'nullable', 'string', 'max:255'],
                'organization_status' => ['nullable', 'in:registered,not_registered'],
                'organization_registered_with' => ['nullable', 'array'],
                'organization_registered_with.*' => ['string'],
            ],

            // Step 4: Training, Information Sources, Income & Finance
            4 => [
                'trainings_attended' => ['nullable', 'array'],
                'trainings_attended.*.title' => ['nullable', 'string', 'max:255'],
                'trainings_attended.*.conducted_by' => ['nullable', 'string', 'max:255'],
                'trainings_attended.*.month_year' => ['nullable', 'string', 'max:100'],
                'information_sources' => ['required', 'array', 'min:1'],
                'information_sources.*' => ['string'],
                'information_sources_others' => ['required_if:information_sources.*,others', 'nullable', 'string', 'max:255'],
                'no_information_source_reason' => ['nullable', 'string', 'max:500'],
                'preferred_iec_materials' => ['required', 'array', 'min:1'],
                'preferred_iec_materials.*' => ['string'],
                'preferred_iec_materials_others' => ['required_if:preferred_iec_materials.*,others', 'nullable', 'string', 'max:255'],

                'income_source_primary' => ['required', 'string', 'max:100'],
                'income_source_secondary' => ['nullable', 'string', 'max:100'],
                'income_primary_dry' => ['nullable', 'numeric', 'min:0'],
                'income_primary_wet' => ['nullable', 'numeric', 'min:0'],
                'income_primary_monthly' => ['nullable', 'numeric', 'min:0'],
                'income_secondary_dry' => ['nullable', 'numeric', 'min:0'],
                'income_secondary_wet' => ['nullable', 'numeric', 'min:0'],
                'income_secondary_monthly' => ['nullable', 'numeric', 'min:0'],

                'finance_sources' => ['required', 'array', 'min:1'],
                'finance_sources.*.source' => ['required', 'string', 'max:100'],
                'finance_sources.*.amount' => ['nullable', 'numeric', 'min:0'],
                'finance_sources.*.interest' => ['nullable', 'numeric', 'min:0', 'max:100'],
            ],

            // Step 5: Seed Selection Criteria
            5 => [
                'seed_criteria' => ['required', 'array', 'min:1'],
                'seed_criteria.*' => ['string'],
                'seed_criteria_others' => ['required_if:seed_criteria.*,others', 'nullable', 'string', 'max:255'],
            ],

            // Step 6: Seed Variety Preference — nested season -> type -> variety cards,
            // each with its own reasons[] and problems[] checkboxes.
            6 => [
                'preferences' => ['nullable', 'array'],
                'preferences.*.*.*.variety' => ['nullable', 'string', 'max:255'],
                'preferences.*.*.*.source_of_information' => ['nullable', 'string', 'max:255'],
                'preferences.*.*.*.actual_yield' => ['nullable', 'numeric', 'min:0'],
                'preferences.*.*.*.area_planted' => ['nullable', 'numeric', 'min:0'],
                'preferences.*.*.*.maturity_days' => ['nullable', 'integer', 'min:0'],
                'preferences.*.*.*.reasons' => ['nullable', 'array'],
                'preferences.*.*.*.reasons.*' => ['string'],
                'preferences.*.*.*.problems' => ['nullable', 'array'],
                'preferences.*.*.*.problems.*' => ['string'],
            ],

            // Step 7: Varieties Planted — now optional. If left completely
            // empty, nothing is required. If any row is started, that row's
            // fields are still validated for sane values, but the row can
            // also be left incomplete without blocking progress.
            7 => [
                'planted' => ['nullable', 'array'],
                'planted.*.season' => ['nullable', 'in:dry,wet'],
                'planted.*.crop' => ['nullable', 'string', 'max:255'],
                'planted.*.variety' => ['nullable', 'string', 'max:255'],
                'planted.*.area' => ['nullable', 'numeric', 'min:0'],
                'planted.*.yield' => ['nullable', 'numeric', 'min:0'],
            ],

            // Step 8: Government Seed Subsidy
            8 => [
                'received_subsidy' => ['required', 'in:yes,no'],

                'subsidy_history' => ['nullable', 'array'],
                'subsidy_history.*.year' => ['nullable', 'digits:4'],
                'subsidy_history.*.season' => ['nullable', 'in:dry,wet'],
                'subsidy_history.*.source' => ['nullable', 'array'],
                'subsidy_history.*.source.*' => ['string'],
                'subsidy_history.*.source_others' => ['nullable', 'string', 'max:255'],

                'subsidy_variety_ds' => ['nullable', 'array'],
                'subsidy_variety_ds.*' => ['nullable', 'string', 'max:255'],
                'subsidy_variety_ws' => ['nullable', 'array'],
                'subsidy_variety_ws.*' => ['nullable', 'string', 'max:255'],

                'used_subsidized_seed' => ['required_if:received_subsidy,yes', 'nullable', 'in:yes,no'],
                'used_subsidized_seed_reason' => ['required_if:used_subsidized_seed,no', 'nullable', 'string', 'max:1000'],

                'preferred_by_traders' => ['required_if:received_subsidy,yes', 'nullable', 'in:yes,no'],
                'preferred_by_traders_reason' => ['required_if:preferred_by_traders,yes', 'nullable', 'string', 'max:1000'],

                'willing_to_accept_other_variety' => ['required_if:received_subsidy,yes', 'nullable', 'in:yes,no'],
                'willing_to_accept_other_variety_reason' => ['nullable', 'string', 'max:1000'],

                'best_variety_ds' => ['nullable', 'array'],
                'best_variety_ds.*.variety' => ['nullable', 'string', 'max:255'],
                'best_variety_ds.*.reason' => ['nullable', 'string', 'max:255'],
                'best_variety_ws' => ['nullable', 'array'],
                'best_variety_ws.*.variety' => ['nullable', 'string', 'max:255'],
                'best_variety_ws.*.reason' => ['nullable', 'string', 'max:255'],

                'subsidy_problems' => ['nullable', 'array'],
                'subsidy_problems.*' => ['string'],
                'subsidy_problems_others' => ['required_if:subsidy_problems.*,others', 'nullable', 'string', 'max:255'],

                'subsidy_recommendation' => ['nullable', 'string', 'max:2000'],

                'capable_to_buy_own_seed' => ['required', 'in:yes,no'],
            ],

            // Step 9: Issues and Recommendations — problems_encountered is now
            // a checkbox array (matches Reports categories) instead of free text.
            9 => [
                'problems_encountered' => ['required', 'array', 'min:1'],
                'problems_encountered.*' => ['string'],
                'problems_encountered_others' => ['required_if:problems_encountered.*,Others', 'nullable', 'string', 'max:255'],
                'recommendations' => ['required', 'string', 'max:5000'],
                'additional_comments' => ['nullable', 'string', 'max:5000'],
            ],

            // Step 10: Final Confirmation.
            // NEW: proof photo/signature required ONLY when the farmer
            // answered without DA assistance (captured here instead of
            // Step 1, since that's the only place we know 'assisted_by_da'
            // by the time we reach the end of the wizard).
            10 => [
                'certification' => ['required', 'accepted'],
                'proof_photo' => ['required_if:assisted_by_da,no', 'nullable', 'string'],
                'proof_signature' => ['required_if:assisted_by_da,no', 'nullable', 'string'],
            ],

            default => [],
        };
    }

    protected function attributesForStep(int $step): array
    {
        return match ($step) {
            1 => [
                'assisted_by_da' => 'assisted by DA personnel',
                'enumerator_code' => 'enumerator code',
                'enumerator_name' => 'enumerator name',
                'enumerator_position' => 'enumerator position',
                'enumerator_office' => 'enumerator office',
                'enum_province_id' => 'enumerator province',
                'enum_municipality_id' => 'enumerator municipality',
                'enum_barangay_id' => 'enumerator barangay',
                'consent_voluntary' => 'voluntary participation consent',
                'consent_data_privacy' => 'data privacy consent',
                'consent_accurate_info' => 'accurate information consent',
                'proof_photo' => 'proof-of-interview photo',
                'proof_signature' => 'proof-of-interview signature',
            ],
            2 => [
                'is_rsbsa_member' => 'RSBSA membership',
                'rsbsa_number' => 'RSBSA number',
            ],
            3 => [
                'farm_province_id' => 'farm province',
                'farm_municipality_id' => 'farm municipality',
                'farm_barangay_id' => 'farm barangay',
            ],
            10 => [
                'proof_photo' => 'proof-of-interview photo',
                'proof_signature' => 'proof-of-interview signature',
            ],
            default => [],
        };
    }

    protected function normalizeStepData(int $step, array $data): array
    {
        if ($step === 1) {
            $data['consent_voluntary'] = $this->boolFromCheckbox($data['consent_voluntary'] ?? null);
            $data['consent_data_privacy'] = $this->boolFromCheckbox($data['consent_data_privacy'] ?? null);
            $data['consent_accurate_info'] = $this->boolFromCheckbox($data['consent_accurate_info'] ?? null);
        }

        if ($step === 10) {
            $data['certification'] = $this->boolFromCheckbox($data['certification'] ?? null);
        }

        return $data;
    }

    protected function boolFromCheckbox(mixed $value): bool
    {
        return in_array($value, [1, '1', 'on', true, 'yes'], true);
    }

    /* =====================================================================
     | Session Handling
     |====================================================================*/

    protected function sessionKey(Farmer $farmer): string
    {
        return "ezseed_survey_data_{$farmer->id}";
    }

    protected function furthestStepKey(Farmer $farmer): string
    {
        return "ezseed_furthest_step_{$farmer->id}";
    }

    protected function getSessionData(Farmer $farmer): array
    {
        return session($this->sessionKey($farmer), []);
    }

    protected function mergeSessionData(Farmer $farmer, int $step, array $stepData): void
    {
        $data = $this->getSessionData($farmer);
        $data = array_merge($data, $stepData);

        session([$this->sessionKey($farmer) => $data]);
    }

    protected function markStepComplete(Farmer $farmer, int $step): void
    {
        $furthest = session($this->furthestStepKey($farmer), 0);

        if ($step > $furthest) {
            session([$this->furthestStepKey($farmer) => $step]);
        }
    }

    protected function redirectIfStepLocked(Farmer $farmer, int $step): ?RedirectResponse
    {
        $furthest = session($this->furthestStepKey($farmer), 0);

        if ($step > $furthest + 1) {
            return redirect()
                ->route('surveys.step.show', ['farmer' => $farmer, 'step' => max(1, $furthest + 1)])
                ->with('error', 'Please complete the survey in order.');
        }

        return null;
    }

    protected function resetSession(Farmer $farmer): void
    {
        session()->forget([$this->sessionKey($farmer), $this->furthestStepKey($farmer)]);
    }

    protected function guardStepNumber(int $step): void
    {
        abort_if($step < 1 || $step > self::TOTAL_STEPS, 404);
    }

    /* =====================================================================
     | Helpers
     |====================================================================*/

    protected function buildSummarySnapshot(Farmer $farmer, array $data): array
    {
        $farmerName = collect([
            $data['first_name'] ?? $farmer->first_name ?? null,
            $data['middle_name'] ?? $farmer->middle_name ?? null,
            $data['last_name'] ?? $farmer->last_name ?? null,
            $data['suffix'] ?? $farmer->suffix ?? null,
        ])->filter()->implode(' ');

        $farmLocation = collect([
            PlaceName::clean(optional(Barangay::find($data['farm_barangay_id'] ?? null))->name),
            PlaceName::clean(optional(Municipality::find($data['farm_municipality_id'] ?? null))->name),
            PlaceName::clean(optional(Province::find($data['farm_province_id'] ?? null))->name),
        ])->filter()->implode(', ');

        return [
            'farmer_name' => $farmerName ?: null,
            'rsbsa_number' => $data['rsbsa_number'] ?? $farmer->rsbsa_number ?? null,
            'farm_location' => $farmLocation ?: null,
        ];
    }

    protected function generateReferenceNumber(): string
    {
        return 'EZS-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
    }

    /* =====================================================================
     | Shared logic — used by both the session-based web wizard's submit()
     | and the API-based offline sync controller, so validation and
     | creation logic exists in exactly one place.
     |====================================================================*/

    public function allStepRules(): array
    {
        $all = [];
        for ($step = 1; $step <= self::TOTAL_STEPS; $step++) {
            $all = array_merge($all, $this->rulesForStep($step));
        }
        return $all;
    }

    public function findOrCreateFarmer(array $data): Farmer
    {
        $isRsbsaMember = ($data['is_rsbsa_member'] ?? null) === 'yes';
        $rsbsaNumber = $isRsbsaMember ? ($data['rsbsa_number'] ?? null) : null;

        $farmer = $rsbsaNumber
            ? Farmer::where('rsbsa_number', $rsbsaNumber)->first()
            : null;

        $attributes = [
            'rsbsa_number' => $rsbsaNumber,
            'first_name' => $data['first_name'] ?? '',
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'] ?? '',
            'suffix' => $data['suffix'] ?? null,
            'birth_date' => $data['birthdate'] ?? null,
            'sex' => $data['sex'] ?? null,
            'civil_status' => $data['civil_status'] ?? null,
            'contact_number' => $data['contact_number'] ?? null,
            'province_id' => $data['farm_province_id'] ?? null,
            'municipality_id' => $data['farm_municipality_id'] ?? null,
            'barangay_id' => $data['farm_barangay_id'] ?? null,
            'farm_area' => $data['farm_area'] ?? null,
            'tenurial_status' => $data['tenurial_status'] ?? null,
        ];

        if ($farmer) {
            $farmer->update($attributes);
            return $farmer;
        }

        return Farmer::create($attributes);
    }

    /**
     * Create the Survey + SeedPreference records from a complete survey
     * payload. Also handles: creating/updating the DAPersonnel record with
     * its lookup 'code' (NEW), and saving the proof photo/signature (NEW)
     * onto the survey row, whichever step they were captured on.
     */
    public function createSurveyFromPayload(Farmer $farmer, array $data): array
    {
        $referenceNumber = $this->generateReferenceNumber();

        $assistedByDa = ($data['assisted_by_da'] ?? null) === 'yes';

        $daPersonnelId = null;
        if ($assistedByDa && ! empty($data['enumerator_name'])) {
            $daPersonnel = DAPersonnel::firstOrCreate(
                [
                    'name' => $data['enumerator_name'],
                    'office' => $data['enumerator_office'] ?? '',
                ],
                [
                    'position' => $data['enumerator_position'] ?? '',
                ]
            );

            // NEW: if the enumerator typed a code this time and their
            // record doesn't have one yet, save it now so future surveys
            // can look them up by that code.
            if (! empty($data['enumerator_code']) && empty($daPersonnel->code)) {
                $daPersonnel->update(['code' => $data['enumerator_code']]);
            }

            $daPersonnelId = $daPersonnel->id;
        }

        $survey = $farmer->surveys()->create([
            'reference_number' => $referenceNumber,
            'payload' => $data,
            'submitted_at' => now(),
            'status' => 'submitted',
            'assisted_by_da_personnel' => $assistedByDa,
            'd_a_personnel_id' => $daPersonnelId,
            // NEW: proof of interview — whichever step it was captured on
            // (Step 1 if assisted, Step 10 if not), it ends up in the same
            // session data by submit time, so this one line covers both.
            'proof_photo' => $data['proof_photo'] ?? null,
            'proof_signature' => $data['proof_signature'] ?? null,
        ]);

        $problemsEncounteredFlat = is_array($data['problems_encountered'] ?? null)
            ? implode(', ', $data['problems_encountered'])
            : ($data['problems_encountered'] ?? null);

        foreach (['dry', 'wet'] as $season) {
            foreach (['hybrid', 'inbred'] as $seedType) {
                $entries = data_get($data, "preferences.{$season}.{$seedType}", []);

                foreach ($entries as $pref) {
                    if (empty($pref['variety'])) {
                        continue;
                    }

                    $mappedSeason = $this->mapSeason($season);
                    $mappedSeedType = $this->mapSeedType($seedType);
                    $reasonLabels = collect($pref['reasons'] ?? [])->implode(', ');
                    $problemLabels = collect($pref['problems'] ?? [])->implode(', ');

                    $variety = \App\Models\SeedVariety::firstOrCreate(
                        ['variety_name' => $pref['variety']],
                        [
                            'seed_type' => $mappedSeedType,
                            'season' => $mappedSeason,
                            'crop_type' => ucfirst($data['crop_type'] ?? 'rice'),
                            'is_active' => true,
                        ]
                    );

                    $problemsValue = $pref['problems'] ?? $problemsEncounteredFlat;

                    \App\Models\SeedPreference::create([
                        'survey_id' => $survey->id,
                        'seed_variety_id' => $variety->id,
                        'season' => $mappedSeason,
                        'seed_type' => $mappedSeedType,
                        'reason' => $reasonLabels ?: null,
                        'reason_category' => $this->categorizeReason($reasonLabels),
                        'problems_encountered' => is_array($problemsValue)
                            ? (empty($problemsValue) ? null : implode(', ', $problemsValue))
                            : $problemsValue,
                    ]);
                }
            }
        }

        return ['survey' => $survey, 'reference_number' => $referenceNumber];
    }
}
