<?php

namespace App\Http\Controllers;

use App\Models\ExportLog;
use App\Models\Province;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class ReportController extends Controller
{
    protected function availableTypes(): array
    {
        return [
            'variety-by-province'     => 'Variety by Province',
            'variety-by-municipality' => 'Variety by Municipality',
            'reasons-for-selection'   => 'Reasons for Selection',
            'problems-encountered'    => 'Problems Encountered',
            'training-attendance'     => 'Training Attendance',
            'information-sources'     => 'Information Sources',
            'source-of-finance'       => 'Source of Finance',
            'source-of-income'        => 'Source of Income',
            'demographic-profiles'    => 'Demographic Profiles',
        ];
    }

    protected function surveyScope()
    {
        $user = Auth::user();
        $query = Survey::query()->with(['farmer.province', 'farmer.municipality']);

        if ($user->isEnumerator()) {
            $personnelId = optional($user->daPersonnel)->id;
            $query->where('d_a_personnel_id', $personnelId ?? 0);
        }

        return $query;
    }

    /**
     * Apply Province / Season / Date filters and return a plain collection
     * of Survey models (payload-aware filtering happens in PHP since
     * season lives inside a JSON array, not a column).
     */
    protected function filteredSurveys(Request $request): Collection
    {
        $query = $this->surveyScope();

        if ($from = $request->input('from')) {
            $query->whereDate('submitted_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->whereDate('submitted_at', '<=', $to);
        }

        $surveys = $query->get();

        if ($province = $request->input('province')) {
            $surveys = $surveys->filter(
                fn ($s) => optional($s->farmer)->province_id == $province
            );
        }

        if ($season = $request->input('season')) {
            $surveys = $surveys->filter(function ($s) use ($season) {
                foreach (['hybrid', 'inbred'] as $seedType) {
                    $entries = data_get($s->payload, "preferences.{$season}.{$seedType}", []);
                    if (! empty($entries)) {
                        return true;
                    }
                }
                return false;
            });
        }

        return $surveys->values();
    }

    /**
     * Tally a field that may be a scalar, a comma-separated string, or an array,
     * pulled out of each survey's payload via dot notation.
     */
    protected function tally(Collection $surveys, string $path): Collection
    {
        $counts = [];

        foreach ($surveys as $survey) {
            $value = data_get($survey->payload, $path);

            if (is_null($value) || $value === '') {
                continue;
            }

            $items = is_array($value) ? $value : array_map('trim', explode(',', $value));

            foreach ($items as $item) {
                if ($item === '' || is_null($item)) {
                    continue;
                }
                $label = $this->humanize((string) $item);
                $counts[$label] = ($counts[$label] ?? 0) + 1;
            }
        }

        arsort($counts);

        return collect($counts);
    }

    protected function humanize(string $value): string
    {
        return str($value)->replace('_', ' ')->title()->toString();
    }

    protected function topVariety(Collection $surveys): ?string
    {
        $counts = [];

        foreach ($surveys as $survey) {
            foreach (['dry', 'wet'] as $season) {
                foreach (['hybrid', 'inbred'] as $seedType) {
                    $entries = data_get($survey->payload, "preferences.{$season}.{$seedType}", []);

                    foreach ($entries as $entry) {
                        $variety = $entry['variety'] ?? null;
                        if ($variety) {
                            $counts[$variety] = ($counts[$variety] ?? 0) + 1;
                        }
                    }
                }
            }
        }

        if (empty($counts)) {
            return null;
        }

        arsort($counts);

        return array_key_first($counts);
    }

    protected function avgFarmArea(Collection $surveys): ?float
    {
        $areas = $surveys->map(fn ($s) => (float) data_get($s->payload, 'farm_area', 0))
            ->filter(fn ($a) => $a > 0);

        return $areas->isEmpty() ? null : round($areas->avg(), 1);
    }

    /**
     * Builds everything the view needs for the active report type:
     * chart (labels/data), table rows, and (where relevant) a summary table.
     */
    protected function buildReport(string $type, Collection $surveys): array
    {
        return match ($type) {

            'variety-by-province' => (function () use ($surveys) {
                $grouped = $surveys->groupBy(fn ($s) => optional($s->farmer)->province_name ?? 'Unknown');

                $summary = $grouped->map(function ($group, $name) {
                    return [
                        'Province'     => $name,
                        'Responses'    => $group->count(),
                        'Top Variety'  => $this->topVariety($group) ?? '—',
                        'Avg Farm'     => $this->avgFarmArea($group) ? $this->avgFarmArea($group) . ' ha' : '—',
                    ];
                })->sortByDesc('Responses')->values();

                return [
                    'chart' => [
                        'labels' => $summary->pluck('Province'),
                        'data'   => $summary->pluck('Responses'),
                        'type'   => 'bar',
                    ],
                    'rows' => $summary,
                ];
            })(),

            'variety-by-municipality' => (function () use ($surveys) {
                $grouped = $surveys->groupBy(fn ($s) => optional($s->farmer)->municipality_name ?? 'Unknown');

                $summary = $grouped->map(function ($group, $name) {
                    $first = $group->first();
                    return [
                        'Municipality' => $name,
                        'Province'     => optional($first->farmer)->province_name ?? '—',
                        'Responses'    => $group->count(),
                        'Top Variety'  => $this->topVariety($group) ?? '—',
                        'Avg Farm'     => $this->avgFarmArea($group) ? $this->avgFarmArea($group) . ' ha' : '—',
                    ];
                })->sortByDesc('Responses')->values();

                return [
                    'chart' => [
                        'labels' => $summary->pluck('Municipality'),
                        'data'   => $summary->pluck('Responses'),
                        'type'   => 'bar',
                    ],
                    'rows' => $summary,
                ];
            })(),

            'reasons-for-selection' => (function () use ($surveys) {
                $tally = $this->tally($surveys, 'seed_criteria');

                // Also fold in per-variety reasons captured in Step 6, so nothing
                // gets silently dropped from this report.
                foreach ($surveys as $survey) {
                    foreach (['dry', 'wet'] as $season) {
                        foreach (['hybrid', 'inbred'] as $seedType) {
                            $entries = data_get($survey->payload, "preferences.{$season}.{$seedType}", []);
                            foreach ($entries as $entry) {
                                foreach ($entry['reasons'] ?? [] as $reason) {
                                    $label = $this->humanize($reason);
                                    $tally[$label] = ($tally[$label] ?? 0) + 1;
                                }
                            }
                        }
                    }
                }

                $tally = collect($tally)->sortDesc();
                $total = $surveys->count() ?: 1;

                $rows = $tally->map(fn ($count, $reason) => [
                    'Reason'      => $reason,
                    'Count'       => $count,
                    '% of Farmers' => round(($count / $total) * 100) . '%',
                ])->values();

                return [
                    'chart' => ['labels' => $tally->keys(), 'data' => $tally->values(), 'type' => 'donut'],
                    'rows'  => $rows,
                ];
            })(),

            'problems-encountered' => (function () use ($surveys) {
                $tally = $this->tally($surveys, 'problems_encountered');

                // Also fold in per-variety problems captured in Step 6.
                foreach ($surveys as $survey) {
                    foreach (['dry', 'wet'] as $season) {
                        foreach (['hybrid', 'inbred'] as $seedType) {
                            $entries = data_get($survey->payload, "preferences.{$season}.{$seedType}", []);
                            foreach ($entries as $entry) {
                                foreach ($entry['problems'] ?? [] as $problem) {
                                    $label = $this->humanize($problem);
                                    if ($label === 'None') {
                                        continue;
                                    }
                                    $tally[$label] = ($tally[$label] ?? 0) + 1;
                                }
                            }
                        }
                    }
                }

                $tally = collect($tally)->sortDesc();

                $rows = $tally->map(fn ($count, $problem) => [
                    'Problem'          => $problem,
                    'Farmers Affected' => $count,
                ])->values();

                return [
                    'chart' => ['labels' => $tally->keys(), 'data' => $tally->values(), 'type' => 'bar'],
                    'rows'  => $rows,
                ];
            })(),

            'training-attendance' => (function () use ($surveys) {
                $counts = [];

                foreach ($surveys as $survey) {
                    foreach (data_get($survey->payload, 'trainings_attended', []) as $t) {
                        $title = $t['title'] ?? null;
                        if (! $title) {
                            continue;
                        }
                        $counts[$title] = ($counts[$title] ?? 0) + 1;
                    }
                }

                arsort($counts);

                $rows = collect($counts)->map(fn ($count, $title) => [
                    'Training Program' => $title,
                    'Attended'         => $count,
                ])->values();

                return [
                    'chart' => ['labels' => array_keys($counts), 'data' => array_values($counts), 'type' => 'bar'],
                    'rows'  => $rows,
                ];
            })(),

            'information-sources' => (function () use ($surveys) {
                $tally = $this->tally($surveys, 'information_sources');
                $total = $surveys->count() ?: 1;

                $rows = $tally->map(fn ($count, $source) => [
                    'Information Source' => $source,
                    'Farmers'            => $count,
                    '% Share'            => round(($count / $total) * 100) . '%',
                ])->values();

                return [
                    'chart' => ['labels' => $tally->keys(), 'data' => $tally->values(), 'type' => 'bar'],
                    'rows'  => $rows,
                ];
            })(),

            'source-of-finance' => (function () use ($surveys) {
                $counts = [];
                $amounts = [];
                $interests = [];

                foreach ($surveys as $survey) {
                    foreach (data_get($survey->payload, 'finance_sources', []) as $entry) {
                        $source = $entry['source'] ?? null;
                        if (! $source) {
                            continue;
                        }
                        $label = $this->humanize($source);
                        $counts[$label] = ($counts[$label] ?? 0) + 1;

                        if (is_numeric($entry['amount'] ?? null)) {
                            $amounts[$label][] = (float) $entry['amount'];
                        }
                        if (is_numeric($entry['interest'] ?? null)) {
                            $interests[$label][] = (float) $entry['interest'];
                        }
                    }
                }

                arsort($counts);
                $total = $surveys->count() ?: 1;

                $rows = collect($counts)->map(function ($count, $source) use ($total, $amounts, $interests) {
                    $avgAmount = ! empty($amounts[$source]) ? round(array_sum($amounts[$source]) / count($amounts[$source]), 2) : null;
                    $avgInterest = ! empty($interests[$source]) ? round(array_sum($interests[$source]) / count($interests[$source]), 1) : null;

                    return [
                        'Finance Source' => $source,
                        'Farmers'        => $count,
                        '% Share'        => round(($count / $total) * 100) . '%',
                        'Avg Amount'     => $avgAmount !== null ? '₱' . number_format($avgAmount, 2) : '—',
                        'Avg Interest'   => $avgInterest !== null ? $avgInterest . '%' : '—',
                    ];
                })->values();

                return [
                    'chart' => ['labels' => array_keys($counts), 'data' => array_values($counts), 'type' => 'donut'],
                    'rows'  => $rows,
                ];
            })(),

            'source-of-income' => (function () use ($surveys) {
                $primary = [];
                $secondary = [];

                foreach ($surveys as $survey) {
                    $p = data_get($survey->payload, 'income_source_primary');
                    $s = data_get($survey->payload, 'income_source_secondary');

                    if ($p) {
                        $label = $this->humanize($p);
                        $primary[$label] = ($primary[$label] ?? 0) + 1;
                    }
                    if ($s) {
                        $label = $this->humanize($s);
                        $secondary[$label] = ($secondary[$label] ?? 0) + 1;
                    }
                }

                arsort($primary);
                $allSources = collect($primary)->keys()->merge(collect($secondary)->keys())->unique();

                $rows = $allSources->map(fn ($source) => [
                    'Income Source' => $source,
                    'As Primary'    => $primary[$source] ?? 0,
                    'As Secondary'  => $secondary[$source] ?? 0,
                ])->sortByDesc('As Primary')->values();

                return [
                    'chart' => ['labels' => array_keys($primary), 'data' => array_values($primary), 'type' => 'bar'],
                    'rows'  => $rows,
                ];
            })(),

            'demographic-profiles' => (function () use ($surveys) {
                $buckets = ['18-30' => 0, '31-40' => 0, '41-50' => 0, '51-60' => 0, '61-70' => 0, '71+' => 0];
                $bySex = [];

                foreach ($surveys as $survey) {
                    $age = (int) data_get($survey->payload, 'age', 0);
                    $sex = strtolower((string) data_get($survey->payload, 'sex', 'unknown'));

                    $bucket = match (true) {
                        $age >= 18 && $age <= 30 => '18-30',
                        $age >= 31 && $age <= 40 => '31-40',
                        $age >= 41 && $age <= 50 => '41-50',
                        $age >= 51 && $age <= 60 => '51-60',
                        $age >= 61 && $age <= 70 => '61-70',
                        $age >= 71 => '71+',
                        default => null,
                    };

                    if (! $bucket) {
                        continue;
                    }

                    $buckets[$bucket]++;
                    $bySex[$bucket][$sex] = ($bySex[$bucket][$sex] ?? 0) + 1;
                }

                $rows = collect($buckets)->map(function ($total, $bucket) use ($bySex) {
                    return [
                        'Age Group' => $bucket,
                        'Male'      => $bySex[$bucket]['male'] ?? 0,
                        'Female'    => $bySex[$bucket]['female'] ?? 0,
                        'Total'     => $total,
                    ];
                })->values();

                return [
                    'chart' => ['labels' => array_keys($buckets), 'data' => array_values($buckets), 'type' => 'bar'],
                    'rows'  => $rows,
                ];
            })(),

            default => ['chart' => ['labels' => [], 'data' => [], 'type' => 'bar'], 'rows' => collect()],
        };
    }

    public function index(Request $request)
    {
        $type = $request->input('type', 'variety-by-province');

        if (! array_key_exists($type, $this->availableTypes())) {
            $type = 'variety-by-province';
        }

        $surveys = $this->filteredSurveys($request);
        $report = $this->buildReport($type, $surveys);

        return view('dashboard.reports', [
            'isAdmin'      => Auth::user()->isAdminOrSupervisor(),
            'types'        => $this->availableTypes(),
            'activeType'   => $type,
            'activeLabel'  => $this->availableTypes()[$type],
            'rows'         => $report['rows'],
            'chart'        => $report['chart'],
            'provinces'    => Province::orderBy('name')->get(),
            'filters'      => [
                'province' => $request->input('province'),
                'season'   => $request->input('season'),
                'from'     => $request->input('from'),
                'to'       => $request->input('to'),
            ],
            'responseCount'   => $surveys->count(),
            'downloadHistory' => ExportLog::latest()->take(8)->get(),
        ]);
    }

    public function export(Request $request, string $format)
    {
        $type = $request->input('type', 'variety-by-province');

        if (! array_key_exists($type, $this->availableTypes())) {
            abort(404);
        }

        $rows = $this->buildReport($type, $this->filteredSurveys($request))['rows'];
        $label = str($this->availableTypes()[$type])->slug('');
        $extension = $format === 'pdf' ? 'pdf' : 'csv';
        $fileName = "{$label}_" . now()->format('Ymd_His') . ".{$extension}";

        ExportLog::create([
            'user_id'   => Auth::id(),
            'file_name' => $fileName,
            'format'    => strtoupper($format),
            'size_kb'   => max(1, intdiv(strlen($rows->toJson()), 1024)),
        ]);

        if ($format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.reports-pdf', [
                'label' => $this->availableTypes()[$type],
                'rows' => $rows,
            ]);

            return $pdf->download($fileName);
        }

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');

            if ($rows->isNotEmpty()) {
                fputcsv($out, array_keys($rows->first()));
                foreach ($rows as $row) {
                    fputcsv($out, $row);
                }
            }

            fclose($out);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }
}