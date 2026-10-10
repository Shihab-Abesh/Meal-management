@php
    $format = function ($value, string $kind) {
        if ($value === null || $value === '') {
            return '—';
        }

        return match ($kind) {
            'currency' => number_format((float) $value, 2),
            'number' => number_format((float) $value),
            'date' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $value) ? \Carbon\CarbonImmutable::parse($value)->format('d M Y') : $value,
            default => $value,
        };
    };
    $hasMoney = collect($report['columns'])->contains('format', 'currency') || collect($report['summary'])->contains('format', 'currency');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $report['title'] }} · {{ $report['subtitle'] }}</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1e293b; }
        h1 { font-size: 18px; margin: 0; color: #0f172a; }
        .muted { color: #64748b; }
        .header { border-bottom: 2px solid #059669; padding-bottom: 10px; margin-bottom: 14px; }
        .header td { vertical-align: top; }
        .vendor { font-size: 13px; font-weight: bold; color: #047857; }
        .summary { width: 100%; border-collapse: separate; border-spacing: 6px; margin: 0 -6px 12px; }
        .summary td { background: #f1f5f9; border-radius: 4px; padding: 7px 9px; width: 25%; }
        .summary .label { font-size: 8.5px; text-transform: uppercase; letter-spacing: .04em; color: #64748b; }
        .summary .value { font-size: 13px; font-weight: bold; margin-top: 2px; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th { text-align: left; font-size: 8.5px; text-transform: uppercase; letter-spacing: .04em; color: #475569; border-bottom: 1px solid #cbd5e1; padding: 6px 5px; }
        table.data td { padding: 5px; border-bottom: 1px solid #e2e8f0; }
        table.data tr.total td { font-weight: bold; border-top: 1.5px solid #94a3b8; border-bottom: none; }
        table.data th.right, table.data td.right, .right { text-align: right; }
        .footer { margin-top: 14px; font-size: 8.5px; color: #94a3b8; }
    </style>
</head>
<body>
    <table class="header" width="100%">
        <tr>
            <td>
                <div class="vendor">{{ $vendor }}</div>
                @if ($vendorDetails)
                    <div class="muted">{{ implode(' · ', $vendorDetails) }}</div>
                @endif
            </td>
            <td class="right">
                <h1>{{ $report['title'] }}</h1>
                <div class="muted">{{ $report['subtitle'] }}</div>
            </td>
        </tr>
    </table>

    @if (count($report['summary']))
        <table class="summary">
            @foreach (array_chunk($report['summary'], 4) as $chunk)
                <tr>
                    @foreach ($chunk as $figure)
                        <td>
                            <div class="label">{{ $figure['label'] }}</div>
                            <div class="value">{{ $format($figure['value'], $figure['format']) }}</div>
                            @if ($figure['hint'])
                                <div class="muted">{{ $figure['hint'] }}</div>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </table>
    @endif

    @if (count($report['rows']) === 0)
        <p class="muted">No data for this period.</p>
    @else
        <table class="data">
            <thead>
                <tr>
                    @foreach ($report['columns'] as $column)
                        <th class="{{ $column['align'] === 'right' ? 'right' : '' }}">{{ $column['label'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($report['rows'] as $row)
                    <tr>
                        @foreach ($report['columns'] as $column)
                            <td class="{{ $column['align'] === 'right' ? 'right' : '' }}">{{ $format($row[$column['key']] ?? null, $column['format']) }}</td>
                        @endforeach
                    </tr>
                @endforeach
                @if ($report['totals'])
                    <tr class="total">
                        @foreach ($report['columns'] as $column)
                            <td class="{{ $column['align'] === 'right' ? 'right' : '' }}">{{ array_key_exists($column['key'], $report['totals']) ? $format($report['totals'][$column['key']], $column['format']) : '' }}</td>
                        @endforeach
                    </tr>
                @endif
            </tbody>
        </table>
    @endif

    <p class="footer">
        Generated {{ $generatedAt }}@if ($hasMoney) · Amounts in {{ $currency }}@endif
    </p>
</body>
</html>
