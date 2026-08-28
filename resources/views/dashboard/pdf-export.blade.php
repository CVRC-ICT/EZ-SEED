<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1f2937; }
        h1 { font-size: 18px; color: #14532d; margin-bottom: 0; }
        .sub { color: #6b7280; font-size: 11px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { text-align: left; padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-size: 11px; }
        th { background: #f0fdf4; color: #14532d; text-transform: uppercase; font-size: 9px; }
        .kpi-grid { width: 100%; margin-bottom: 20px; }
        .kpi-grid td { border: 1px solid #e5e7eb; padding: 10px; width: 33%; }
        .kpi-label { font-size: 9px; text-transform: uppercase; color: #6b7280; }
        .kpi-value { font-size: 16px; font-weight: bold; color: #14532d; }
        .section-title { font-size: 13px; font-weight: bold; color: #14532d; margin: 16px 0 6px; }
        .insight { padding: 6px 0; border-bottom: 1px solid #f3f4f6; font-size: 11px; }
    </style>
</head>
<body>
    <h1>EZ-Seed — DA RFO II Seed Preference Analytics</h1>
    <p class="sub">Generated {{ now()->format('F j, Y g:i A') }}</p>

    <table class="kpi-grid">
        <tr>
            <td><div class="kpi-label">Total Farmers</div><div class="kpi-value">{{ $totalFarmers }}</div></td>
            <td><div class="kpi-label">Completed Surveys</div><div class="kpi-value">{{ $submittedSurveys }}</div></td>
            <td><div class="kpi-label">Top Province</div><div class="kpi-value">{{ $topProvinceName }}</div></td>
        </tr>
        <tr>
            <td><div class="kpi-label">Hybrid Preference</div><div class="kpi-value">{{ $hybridPct !== null ? $hybridPct . '%' : '—' }}</div></td>
            <td><div class="kpi-label">Inbred Preference</div><div class="kpi-value">{{ $inbredPct !== null ? $inbredPct . '%' : '—' }}</div></td>
            <td><div class="kpi-label">Top Variety</div><div class="kpi-value">{{ $topVarietyName }}</div></td>
        </tr>
    </table>

    <div class="section-title">Top Preferred Rice Varieties</div>
    @if ($topVarieties->count())
        <table>
            <tr><th>Variety</th><th>Preferences</th></tr>
            @foreach ($topVarieties as $v)
                <tr><td>{{ $v->seedVariety->variety_name ?? 'Unknown' }}</td><td>{{ $v->total }}</td></tr>
            @endforeach
        </table>
    @else
        <p>No seed preference data available.</p>
    @endif

    <div class="section-title">Farmers by Province</div>
    @if ($farmersByProvince->count())
        <table>
            <tr><th>Province</th><th>Farmers</th></tr>
            @foreach ($farmersByProvince as $row)
                <tr><td>{{ $row['name'] }}</td><td>{{ $row['total'] }}</td></tr>
            @endforeach
        </table>
    @else
        <p>No province data available.</p>
    @endif

    <div class="section-title">Reasons for Choosing a Variety</div>
    @if ($reasonBreakdown->count())
        <table>
            <tr><th>Reason</th><th>Count</th></tr>
            @foreach ($reasonBreakdown as $r)
                <tr><td>{{ $r->reason_category }}</td><td>{{ $r->total }}</td></tr>
            @endforeach
        </table>
    @else
        <p>No categorized reasons recorded yet.</p>
    @endif

    <div class="section-title">Common Problems Reported</div>
    @if ($problemsTally->count())
        <table>
            <tr><th>Problem</th><th>Count</th></tr>
            @foreach ($problemsTally as $label => $count)
                <tr><td>{{ $label }}</td><td>{{ $count }}</td></tr>
            @endforeach
        </table>
    @else
        <p>No reported problems yet.</p>
    @endif

    <div class="section-title">Key Insights</div>
    @foreach ($insights as $insight)
        <div class="insight">{{ $insight['icon'] }} <strong>{{ $insight['label'] }}:</strong> {{ $insight['text'] }}</div>
    @endforeach
</body>
</html>