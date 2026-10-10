<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Turns a report built by ReportService into a download: CSV that opens
 * cleanly in Excel, or a printable PDF.
 */
class ReportExporter
{
    public function __construct(private readonly VendorProfileService $vendorProfiles) {}

    /**
     * @param  array<string, mixed>  $report
     */
    public function csv(array $report): StreamedResponse
    {
        return response()->streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');
            // Byte order mark so Excel reads UTF-8 names correctly.
            fwrite($out, "\u{FEFF}");

            fputcsv($out, [$report['title']], ',', '"', '');
            fputcsv($out, [$report['subtitle']], ',', '"', '');
            fputcsv($out, [], ',', '"', '');

            foreach ($report['summary'] as $figure) {
                fputcsv($out, [$figure['label'], $this->plain($figure['value'], $figure['format'])], ',', '"', '');
            }

            fputcsv($out, [], ',', '"', '');
            fputcsv($out, array_column($report['columns'], 'label'), ',', '"', '');

            foreach ($report['rows'] as $row) {
                fputcsv($out, $this->line($report['columns'], $row), ',', '"', '');
            }

            if ($report['totals']) {
                fputcsv($out, $this->line($report['columns'], $report['totals']), ',', '"', '');
            }

            fclose($out);
        }, $this->filename($report, 'csv'), ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  array<string, mixed>  $report
     */
    public function pdf(array $report): Response
    {
        $profile = $this->vendorProfiles->current();

        return Pdf::loadView('reports.pdf', [
            'report' => $report,
            'vendor' => $profile?->business_name ?? config('app.name'),
            'vendorDetails' => array_filter([$profile?->address, $profile?->city, $profile?->phone]),
            'currency' => config('catering.currency.code'),
            'generatedAt' => now()->format('d M Y, g:i A'),
        ])
            // Embed only the glyphs used, keeping files small.
            ->setOption('isFontSubsettingEnabled', true)
            ->setPaper('a4', count($report['columns']) > 6 ? 'landscape' : 'portrait')
            ->download($this->filename($report, 'pdf'));
    }

    /**
     * @param  list<array{key: string, format: string}>  $columns
     * @param  array<string, mixed>  $row
     * @return list<string>
     */
    private function line(array $columns, array $row): array
    {
        return array_map(fn (array $column) => $this->plain($row[$column['key']] ?? '', $column['format']), $columns);
    }

    /**
     * Values as Excel should read them: plain numbers, ISO dates. Text that
     * looks like a formula is prefixed so a spreadsheet never runs it.
     */
    private function plain(mixed $value, string $format): string
    {
        $value = (string) ($value ?? '');

        if ($format === 'text' && preg_match('/^[=+\-@\t\r]/', $value)) {
            return "'".$value;
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function filename(array $report, string $extension): string
    {
        return Str::slug($report['title'].' '.$report['subtitle']).'-'.CarbonImmutable::now()->format('Ymd-His').'.'.$extension;
    }
}
