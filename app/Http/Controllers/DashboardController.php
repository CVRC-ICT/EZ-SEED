<?php

namespace App\Http\Controllers;

use App\Models\Farmer;
use App\Models\Survey;
use App\Models\SeedPreference;
use App\Models\SeedVariety;
use App\Models\Province;
use App\Models\Municipality;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    protected function surveyScope()
    {
        $user = Auth::user();
        $query = Survey::query();

        if ($user->isEnumerator()) {
            $personnelId = optional($user->daPersonnel)->id;
            $query->where('d_a_personnel_id', $personnelId ?? 0);
        }

        return $query;
    }

    /**
     * Read the dashboard filter bar inputs from the request.
     */
    protected function activeFilters(Request $request): array
    {
        return [
            'province' => $request->input('province'),
            'municipality' => $request->input('municipality'),
            'season' => $request->input('season'),
            'seed_type' => $request->input('seed_type'),
            'year' => $request->input('year'),
        ];
    }

    /**
     * Base SeedPreference query, scoped to the current user's surveys and
     * joined through survey -> farmer so province/municipality/year filters
     * can be applied, plus direct season/seed_type filters.
     */
    protected function filteredPreferenceQuery(array $filters)
    {
        $surveyIds = (clone $this->surveyScope())->pluck('id');

        $query = SeedPreference::whereIn('survey_id', $surveyIds)
            ->whereHas('survey', function ($q) use ($filters) {
                if ($filters['year']) {
                    $q->whereYear(DB::raw('COALESCE(submitted_at, created_at)'), $filters['year']);
                }

                if ($filters['province'] || $filters['municipality']) {
                    $q->whereHas('farmer', function ($fq) use ($filters) {
                        if ($filters['province']) {
                            $fq->where('province_id', $filters['province']);
                        }
                        if ($filters['municipality']) {
                            $fq->where('municipality_id', $filters['municipality']);
                        }
                    });
                }
            });

        if ($filters['season']) {
            $query->where('season', $filters['season']);
        }

        if ($filters['seed_type']) {
            $query->where('seed_type', $filters['seed_type']);
        }

        return $query;
    }

    /**
     * Base Farmer query, scoped + filtered by province/municipality/year and,
     * if season/seed_type are set, restricted to farmers who have at least
     * one matching seed preference.
     */
    protected function filteredFarmerQuery(bool $isAdmin, array $filters)
    {
        $farmerIds = (clone $this->surveyScope())->pluck('farmer_id')->unique();
        $query = $isAdmin ? Farmer::query() : Farmer::whereIn('id', $farmerIds);

        if ($filters['province']) {
            $query->where('province_id', $filters['province']);
        }

        if ($filters['municipality']) {
            $query->where('municipality_id', $filters['municipality']);
        }

        if ($filters['year'] || $filters['season'] || $filters['seed_type']) {
            $query->whereHas('surveys', function ($sq) use ($filters) {
                if ($filters['year']) {
                    $sq->whereYear(DB::raw('COALESCE(submitted_at, created_at)'), $filters['year']);
                }
                if ($filters['season'] || $filters['seed_type']) {
                    $sq->whereHas('seedPreferences', function ($pq) use ($filters) {
                        if ($filters['season']) {
                            $pq->where('season', $filters['season']);
                        }
                        if ($filters['seed_type']) {
                            $pq->where('seed_type', $filters['seed_type']);
                        }
                    });
                }
            });
        }

        return $query;
    }

    /**
     * Distinct years available in submitted survey data, for the Year filter.
     */
    protected function availableYears(): array
    {
        return (clone $this->surveyScope())
            ->selectRaw('DISTINCT EXTRACT(YEAR FROM COALESCE(submitted_at, created_at)) as y')
            ->orderByDesc('y')
            ->pluck('y')
            ->filter()
            ->values()
            ->all();
    }

    /*
    |--------------------------------------------------------------------
    | 1. DASHBOARD OVERVIEW
    |--------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->isAdminOrSupervisor();
        $filters = $this->activeFilters($request);

        $preferenceQuery = $this->filteredPreferenceQuery($filters);
        $farmerQuery = $this->filteredFarmerQuery($isAdmin, $filters);

        $totalFarmers = (clone $farmerQuery)->count();

        $surveyIdsForCompletion = (clone $this->surveyScope());
        if ($filters['year']) {
            $surveyIdsForCompletion->whereYear(DB::raw('COALESCE(submitted_at, created_at)'), $filters['year']);
        }
        if ($filters['province'] || $filters['municipality']) {
            $surveyIdsForCompletion->whereHas('farmer', function ($fq) use ($filters) {
                if ($filters['province']) {
                    $fq->where('province_id', $filters['province']);
                }
                if ($filters['municipality']) {
                    $fq->where('municipality_id', $filters['municipality']);
                }
            });
        }
        $totalSurveysFiltered = (clone $surveyIdsForCompletion)->count();
        $submittedSurveysFiltered = (clone $surveyIdsForCompletion)->where('status', 'submitted')->count();
        $pendingSurveys = (clone $surveyIdsForCompletion)->where('status', 'draft')->count();
        $approvedSurveys = (clone $surveyIdsForCompletion)->where('status', 'approved')->count();

        $totalPreferences = (clone $preferenceQuery)->count();
        $hybridCount = (clone $preferenceQuery)->where('seed_type', 'Hybrid')->count();
        $inbredCount = (clone $preferenceQuery)->where('seed_type', 'Inbred')->count();
        $hybridPct = $totalPreferences ? round(($hybridCount / $totalPreferences) * 100, 1) : null;
        $inbredPct = $totalPreferences ? round(($inbredCount / $totalPreferences) * 100, 1) : null;

        $topVariety = (clone $preferenceQuery)
            ->select('seed_variety_id', DB::raw('COUNT(*) as total'))
            ->with('seedVariety')
            ->groupBy('seed_variety_id')
            ->orderByDesc('total')
            ->first();

        $topProvinceRow = (clone $farmerQuery)
            ->whereNotNull('province_id')
            ->select('province_id', DB::raw('COUNT(*) as total'))
            ->groupBy('province_id')
            ->orderByDesc('total')
            ->with('province')
            ->first();

        // Top 10 preferred varieties. NOTE: preference_rank no longer exists
        // in the survey wizard (removed when Step 6 was rebuilt to a
        // freeform "Add Variety" format), so every recorded preference
        // entry is counted equally rather than filtering to a "rank #1"
        // subset that the data can no longer express.
        $topVarieties = (clone $preferenceQuery)
            ->select('seed_variety_id', DB::raw('COUNT(*) as total'))
            ->with('seedVariety')
            ->groupBy('seed_variety_id')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        // Seasonal comparison: top varieties (combined) split by season.
        $seasonalTopVarietyIds = (clone $preferenceQuery)
            ->select('seed_variety_id', DB::raw('COUNT(*) as total'))
            ->groupBy('seed_variety_id')
            ->orderByDesc('total')
            ->take(6)
            ->pluck('seed_variety_id');

        $seasonalBreakdown = SeedVariety::whereIn('id', $seasonalTopVarietyIds)
            ->get()
            ->map(function ($variety) use ($preferenceQuery) {
                return [
                    'name' => $variety->variety_name,
                    'dry' => (clone $preferenceQuery)->where('seed_variety_id', $variety->id)->where('season', 'Dry Season')->count(),
                    'wet' => (clone $preferenceQuery)->where('seed_variety_id', $variety->id)->where('season', 'Wet Season')->count(),
                ];
            })
            ->sortByDesc(fn ($r) => $r['dry'] + $r['wet'])
            ->values();

        // Farmers by province (respects filters where applicable).
        $farmersByProvince = (clone $farmerQuery)
            ->whereNotNull('province_id')
            ->select('province_id', DB::raw('COUNT(*) as total'))
            ->groupBy('province_id')
            ->orderByDesc('total')
            ->with('province')
            ->get()
            ->map(fn ($row) => [
                'name' => optional($row->province)->name ?? 'Unknown',
                'total' => $row->total,
            ]);

        $reasonBreakdown = (clone $preferenceQuery)
            ->whereNotNull('reason_category')
            ->select('reason_category', DB::raw('COUNT(*) as total'))
            ->groupBy('reason_category')
            ->orderByDesc('total')
            ->get();

        // problems_encountered is stored as a comma-separated string per
        // preference row (flattened from checkbox selections). Tally by
        // splitting rather than assuming a single predefined value per row.
        $problemsTally = [];
        (clone $preferenceQuery)
            ->whereNotNull('problems_encountered')
            ->where('problems_encountered', '!=', '')
            ->pluck('problems_encountered')
            ->each(function ($value) use (&$problemsTally) {
                foreach (explode(',', $value) as $piece) {
                    $piece = trim($piece);
                    if ($piece === '' || strtolower($piece) === 'none') {
                        continue;
                    }
                    $problemsTally[$piece] = ($problemsTally[$piece] ?? 0) + 1;
                }
            });
        arsort($problemsTally);
        $problemsTally = collect($problemsTally)->take(6);

        $averageFarmArea = (clone $farmerQuery)->avg('farm_area');

        $insights = $this->buildInsights(
            topVariety: $topVariety,
            topProvinceRow: $topProvinceRow,
            hybridPct: $hybridPct,
            drySeasonCount: (clone $preferenceQuery)->where('season', 'Dry Season')->count(),
            wetSeasonCount: (clone $preferenceQuery)->where('season', 'Wet Season')->count(),
            topProblem: $problemsTally->keys()->first()
        );

        $recentActivity = $this->buildRecentActivity($isAdmin);

        return view('dashboard.index', [
            'isAdmin' => $isAdmin,
            'filters' => $filters,
            'provinces' => Province::orderBy('name')->get(),
            'municipalities' => $filters['province']
                ? Municipality::where('province_id', $filters['province'])->orderBy('name')->get()
                : collect(),
            'availableYears' => $this->availableYears(),

            'totalFarmers' => $totalFarmers,
            'totalSurveys' => $totalSurveysFiltered,
            'submittedSurveys' => $submittedSurveysFiltered,
            'pendingSurveys' => $pendingSurveys,
            'approvedSurveys' => $approvedSurveys,

            'hybridCount' => $hybridCount,
            'inbredCount' => $inbredCount,
            'hybridPct' => $hybridPct,
            'inbredPct' => $inbredPct,
            'totalPreferences' => $totalPreferences,

            'topVarietyName' => optional($topVariety)->seedVariety->variety_name ?? '—',
            'topVarietyCount' => optional($topVariety)->total ?? 0,

            'topProvinceName' => $topProvinceRow ? optional($topProvinceRow->province)->name : '—',
            'topProvinceCount' => optional($topProvinceRow)->total ?? 0,

            'averageFarmArea' => $averageFarmArea ? round($averageFarmArea, 2) : null,

            'topVarieties' => $topVarieties,
            'seasonalBreakdown' => $seasonalBreakdown,
            'farmersByProvince' => $farmersByProvince,
            'reasonBreakdown' => $reasonBreakdown,
            'problemsTally' => $problemsTally,

            'insights' => $insights,
            'recentActivity' => $recentActivity,
        ]);
    }

    /**
     * Build simple, factual, data-derived insight strings. No fabrication —
     * every line either states a real computed fact or explains that there
     * isn't enough data yet.
     */
    protected function buildInsights($topVariety, $topProvinceRow, ?float $hybridPct, int $drySeasonCount, int $wetSeasonCount, ?string $topProblem): array
    {
        $insights = [];

        $insights[] = [
            'icon' => '🌾',
            'label' => 'Top Variety',
            'text' => $topVariety
                ? ($topVariety->seedVariety->variety_name ?? 'Unknown') . ' is currently the most preferred variety.'
                : 'Not enough data to generate this insight.',
        ];

        $insights[] = [
            'icon' => '📍',
            'label' => 'Leading Province',
            'text' => $topProvinceRow
                ? optional($topProvinceRow->province)->name . ' has the highest number of participating farmers.'
                : 'Not enough data to generate this insight.',
        ];

        $insights[] = [
            'icon' => '🌱',
            'label' => 'Seed Type',
            'text' => $hybridPct !== null
                ? "Hybrid varieties account for {$hybridPct}% of recorded preferences."
                : 'Not enough data to generate this insight.',
        ];

        $insights[] = [
            'icon' => '☀️',
            'label' => 'Season',
            'text' => ($drySeasonCount || $wetSeasonCount)
                ? ($drySeasonCount === $wetSeasonCount
                    ? 'Dry and Wet Season responses are currently even.'
                    : (($drySeasonCount > $wetSeasonCount) ? 'Dry Season' : 'Wet Season') . ' currently has more recorded responses than the other season.')
                : 'Not enough data to generate this insight.',
        ];

        $insights[] = [
            'icon' => '⚠️',
            'label' => 'Common Problem',
            'text' => $topProblem
                ? "{$topProblem} is the most frequently reported issue."
                : 'Not enough data to generate this insight.',
        ];

        return $insights;
    }

    /**
     * Recent activity feed built from the latest farmers/surveys, since no
     * dedicated activity log table exists.
     */
    protected function buildRecentActivity(bool $isAdmin): \Illuminate\Support\Collection
    {
        $farmerQuery = $isAdmin ? Farmer::query() : Farmer::whereIn('id', (clone $this->surveyScope())->pluck('farmer_id')->unique());

        $recentFarmers = (clone $farmerQuery)
            ->whereNotNull('created_at')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($f) => [
                'type' => 'New farmer registered',
                'place' => $f->province_name ?? '—',
                'at' => $f->created_at,
            ]);

        $recentSurveys = (clone $this->surveyScope())
            ->where('status', 'submitted')
            ->with('farmer')
            ->latest('submitted_at')
            ->take(5)
            ->get()
            ->map(fn ($s) => [
                'type' => 'Survey submitted',
                'place' => optional($s->farmer)->province_name ?? '—',
                'at' => $s->submitted_at ?? $s->created_at,
            ]);

        return $recentFarmers->merge($recentSurveys)
            ->sortByDesc('at')
            ->take(8)
            ->values();
    }

    /*
    |--------------------------------------------------------------------
    | 2. FARMER PROFILES
    |--------------------------------------------------------------------
    */
    public function farmers(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->isAdminOrSupervisor();

        $farmerIds = (clone $this->surveyScope())->pluck('farmer_id')->unique();

        $baseQuery = $isAdmin ? Farmer::query() : Farmer::whereIn('id', $farmerIds);

        $baseQuery->withCount('surveys')
            ->with([
                'province', 'municipality',
                'surveys:id,farmer_id,assisted_by_da_personnel',
            ]);

        if ($search = $request->input('search')) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('rsbsa_number', 'like', "%{$search}%");
            });
        }

        if ($province = $request->input('province')) {
            $baseQuery->where('province_id', $province);
        }

        if ($request->boolean('portal_only')) {
            $baseQuery->whereDoesntHave('surveys', fn ($q) => $q->where('assisted_by_da_personnel', true));
        }

        // RSBSA tab counts computed on the same filtered scope (search/province/
        // portal_only applied), but BEFORE the rsbsa tab filter itself, so each
        // tab shows an accurate count relative to the other active filters.
        $rsbsaCounts = [
            'all' => (clone $baseQuery)->count(),
            'registered' => (clone $baseQuery)->whereNotNull('rsbsa_number')->where('rsbsa_number', '!=', '')->count(),
            'not_registered' => (clone $baseQuery)->where(function ($q) {
                $q->whereNull('rsbsa_number')->orWhere('rsbsa_number', '');
            })->count(),
        ];

        $selectedRsbsa = $request->input('rsbsa', '');

        if ($selectedRsbsa === 'registered') {
            $baseQuery->whereNotNull('rsbsa_number')->where('rsbsa_number', '!=', '');
        } elseif ($selectedRsbsa === 'not_registered') {
            $baseQuery->where(function ($q) {
                $q->whereNull('rsbsa_number')->orWhere('rsbsa_number', '');
            });
        }

        $farmers = $baseQuery->latest()->paginate(15)->withQueryString();

        return view('dashboard.farmers', [
            'isAdmin' => $isAdmin,
            'farmers' => $farmers,
            'search' => $search ?? '',
            'provinces' => Province::orderBy('name')->get(),
            'selectedProvince' => $province ?? '',
            'portalOnly' => $request->boolean('portal_only'),
            'selectedRsbsa' => $selectedRsbsa,
            'rsbsaCounts' => $rsbsaCounts,
        ]);
    }

    /*
    |--------------------------------------------------------------------
    | 3. SEED MONITORING
    |--------------------------------------------------------------------
    */
    public function seedMonitoring(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->isAdminOrSupervisor();
        $surveyIds = (clone $this->surveyScope())->pluck('id');

        $preferenceQuery = SeedPreference::whereIn('survey_id', $surveyIds);

        if ($season = $request->input('season')) {
            $preferenceQuery->where('season', $season);
        }

        $topByType = function (string $type) use ($preferenceQuery) {
            return (clone $preferenceQuery)
                ->where('seed_type', $type)
                ->select('seed_variety_id', DB::raw('COUNT(*) as total'))
                ->with('seedVariety')
                ->groupBy('seed_variety_id')
                ->orderByDesc('total')
                ->take(5)
                ->get();
        };

        // problems_encountered is a comma-separated string (flattened from
        // checkbox selections in the survey). Split + humanize each piece
        // rather than treating the whole raw string as one label.
        $problemsTally = [];
        (clone $preferenceQuery)
            ->whereNotNull('problems_encountered')
            ->where('problems_encountered', '!=', '')
            ->pluck('problems_encountered')
            ->each(function ($value) use (&$problemsTally) {
                foreach (explode(',', $value) as $piece) {
                    $piece = trim($piece);
                    if ($piece === '' || strtolower($piece) === 'none') {
                        continue;
                    }
                    $label = str($piece)->replace('_', ' ')->title()->toString();
                    $problemsTally[$label] = ($problemsTally[$label] ?? 0) + 1;
                }
            });
        arsort($problemsTally);
        $problemsTally = collect($problemsTally)->take(6);

        // Seed Selection Criteria — from Step 5's seed_criteria, stored on the
        // parent Survey's payload (not a seed_preferences column), so pull it
        // via the related surveys rather than the preference query.
        $criteriaTally = [];
        Survey::whereIn('id', $surveyIds)
            ->whereNotNull('payload')
            ->pluck('payload')
            ->each(function ($payload) use (&$criteriaTally) {
                foreach (data_get($payload, 'seed_criteria', []) as $criterion) {
                    $label = str($criterion)->replace('_', ' ')->title()->toString();
                    $criteriaTally[$label] = ($criteriaTally[$label] ?? 0) + 1;
                }
            });
        arsort($criteriaTally);
        $criteriaTally = collect($criteriaTally);

        return view('dashboard.seed-monitoring', [
            'isAdmin' => $isAdmin,
            'season' => $season ?? '',

            'totalHybrid' => SeedVariety::where('seed_type', 'Hybrid')->count(),
            'totalInbred' => SeedVariety::where('seed_type', 'Inbred')->count(),

            'topHybrid' => $topByType('Hybrid'),
            'topInbred' => $topByType('Inbred'),

            'problems' => $problemsTally,
            'criteria' => $criteriaTally,
        ]);
    }

    /*
    |--------------------------------------------------------------------
    | 4. SEASONAL ANALYSIS
    |--------------------------------------------------------------------
    */
    public function seasonalAnalysis()
    {
        $user = Auth::user();
        $isAdmin = $user->isAdminOrSupervisor();
        $surveyIds = (clone $this->surveyScope())->pluck('id');

        $preferenceQuery = SeedPreference::whereIn('survey_id', $surveyIds);

        // Responses per month, split by season. Uses submitted_at when set,
        // otherwise falls back to created_at.
        $monthly = Survey::whereIn('id', $surveyIds)
            ->where('status', 'submitted')
            ->select(
                DB::raw('EXTRACT(MONTH FROM COALESCE(submitted_at, created_at)) as month'),
                DB::raw("COUNT(CASE WHEN id IN (
                    SELECT survey_id FROM seed_preferences WHERE season = 'Dry Season'
                ) THEN 1 END) as dry_total"),
                DB::raw("COUNT(CASE WHEN id IN (
                    SELECT survey_id FROM seed_preferences WHERE season = 'Wet Season'
                ) THEN 1 END) as wet_total")
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Top varieties compared Dry vs Wet Season — same comparison chart
        // style used on the main Dashboard, scoped to this user's surveys.
        $topVarietyIds = (clone $preferenceQuery)
            ->select('seed_variety_id', DB::raw('COUNT(*) as total'))
            ->groupBy('seed_variety_id')
            ->orderByDesc('total')
            ->take(8)
            ->pluck('seed_variety_id');

        $varietySeasonal = SeedVariety::whereIn('id', $topVarietyIds)
            ->get()
            ->map(function ($variety) use ($preferenceQuery) {
                return [
                    'name' => $variety->variety_name,
                    'dry' => (clone $preferenceQuery)->where('seed_variety_id', $variety->id)->where('season', 'Dry Season')->count(),
                    'wet' => (clone $preferenceQuery)->where('seed_variety_id', $variety->id)->where('season', 'Wet Season')->count(),
                ];
            })
            ->sortByDesc(fn ($r) => $r['dry'] + $r['wet'])
            ->values();

        return view('dashboard.seasonal-analysis', [
            'isAdmin' => $isAdmin,

            'dryTotal' => (clone $preferenceQuery)->where('season', 'Dry Season')->count(),
            'wetTotal' => (clone $preferenceQuery)->where('season', 'Wet Season')->count(),

            'monthly' => $monthly,
            'varietySeasonal' => $varietySeasonal,
        ]);
    }

    /*
    |--------------------------------------------------------------------
    | 5. PROVINCE MAPS
    |--------------------------------------------------------------------
    */
    public function provinceMaps()
    {
        $user = Auth::user();
        $isAdmin = $user->isAdminOrSupervisor();

        return view('dashboard.province-maps', [
            'isAdmin' => $isAdmin,
            'provinces' => Province::orderBy('name')->get(),
            'availableYears' => $this->availableYears(),
        ]);
    }

    /**
     * JSON data endpoint powering the Response Intensity map.
     *
     * Response Intensity = Submitted Surveys ÷ Registered Farmers, per
     * province or municipality. This is deliberately NOT based on a
     * "target/quota" figure, since EZ-Seed has no such field anywhere in
     * the schema (this was a conscious decision made earlier in the
     * project for the Reports module). It uses only real farmers/surveys
     * data:
     *   0–40%  = Low
     *   41–70% = Medium
     *   71–100% = High
     *   0 registered farmers = No Data (not "Low" — there's nothing to rate)
     *
     * level=province  -> stats for the 5 Region II provinces
     * level=municipality&province_id=X -> stats for municipalities in X
     */
    public function provinceMapData(Request $request)
    {
        $level = $request->input('level', 'province');
        $year = $request->input('year');

        $applyYear = function ($query) use ($year) {
            if ($year) {
                $query->whereYear(DB::raw('COALESCE(submitted_at, created_at)'), $year);
            }
        };

        if ($level === 'municipality') {
            $provinceId = $request->input('province_id');
            if (! $provinceId) {
                return response()->json(['error' => 'province_id is required for municipality level'], 422);
            }

            $municipalities = Municipality::where('province_id', $provinceId)->orderBy('name')->get();

            $rows = $municipalities->map(function ($muni) use ($applyYear) {
                $farmers = Farmer::where('municipality_id', $muni->id)->count();

                $responsesQuery = Survey::where('status', 'submitted')
                    ->whereHas('farmer', fn ($q) => $q->where('municipality_id', $muni->id));
                $applyYear($responsesQuery);
                $responses = $responsesQuery->count();

                return $this->intensityRow($muni->id, $muni->name, $farmers, $responses);
            })->values();

            return response()->json(['level' => 'municipality', 'rows' => $rows]);
        }

        // level === 'province'
        $provinces = Province::orderBy('name')->get();

        $rows = $provinces->map(function ($province) use ($applyYear) {
            $farmers = Farmer::where('province_id', $province->id)->count();

            $responsesQuery = Survey::where('status', 'submitted')
                ->whereHas('farmer', fn ($q) => $q->where('province_id', $province->id));
            $applyYear($responsesQuery);
            $responses = $responsesQuery->count();

            return $this->intensityRow($province->id, $province->name, $farmers, $responses);
        })->values();

        return response()->json(['level' => 'province', 'rows' => $rows]);
    }

    protected function intensityRow(int $id, string $name, int $farmers, int $responses): array
    {
        $rate = $farmers > 0 ? round(($responses / $farmers) * 100, 1) : null;

        $intensity = match (true) {
            $farmers === 0 => 'none',
            $rate <= 40 => 'low',
            $rate <= 70 => 'medium',
            default => 'high',
        };

        return [
            'id' => $id,
            'name' => $name,
            'farmers' => $farmers,
            'responses' => $responses,
            'response_rate' => $rate,
            'intensity' => $intensity,
        ];
    }

    /*
    |--------------------------------------------------------------------
    | 6. DASHBOARD PDF EXPORT
    |--------------------------------------------------------------------
    | Requires barryvdh/laravel-dompdf. Install with:
    |   composer require barryvdh/laravel-dompdf
    | If not installed, this route will throw a clear "class not found"
    | error rather than failing silently.
    */
    public function exportPdf(Request $request)
    {
        // Reuse the same filtered index() computation so the PDF matches
        // exactly what's on screen. We call index() and pull its view data
        // back out rather than duplicating all the query logic.
        $response = $this->index($request);
        $data = $response->getData();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.pdf-export', $data);

        return $pdf->download('ezseed-dashboard-' . now()->format('Ymd_His') . '.pdf');
    }
}