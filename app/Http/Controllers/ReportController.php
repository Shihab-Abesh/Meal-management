<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportRequest;
use App\Models\Client;
use App\Services\ActivityLogger;
use App\Services\ReportExporter;
use App\Services\ReportService;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * One-click reports: pick the report and the date or month, see it, export
 * it as CSV (Excel) or PDF.
 */
class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reports) {}

    public function index(ReportRequest $request): Response
    {
        $user = $request->user();

        return Inertia::render('reports/index', [
            'report' => $this->build($request),
            'params' => [
                'type' => $request->type(),
                'date' => $request->day(),
                'month' => $request->month(),
                'from' => $request->from(),
                'to' => $request->to(),
                'client' => $user->isVendorUser() ? (string) ($request->validated('client') ?? '') : '',
            ],
            'types' => $request->typeOptions(),
            'clients' => $user->isVendorUser()
                ? Client::withTrashed()->orderBy('name')->get(['id', 'name'])->map(fn (Client $client) => ['value' => (string) $client->id, 'label' => $client->name])
                : null,
            'today' => now()->toDateString(),
        ]);
    }

    public function export(ReportRequest $request, ReportExporter $exporter, ActivityLogger $activity): HttpResponse
    {
        $report = $this->build($request);
        abort_if($report === null, 422, 'Choose a client first.');

        $format = $request->validated('format') ?? 'csv';
        $activity->log('reports', 'exported', "Exported {$report['title']} ({$report['subtitle']}) as ".strtoupper($format), null, [
            'type' => $report['type'],
            'format' => $format,
        ]);

        return $format === 'pdf' ? $exporter->pdf($report) : $exporter->csv($report);
    }

    /**
     * @return array<string, mixed>|null null when the report needs a client that is not chosen yet
     */
    private function build(ReportRequest $request): ?array
    {
        $showMoney = $request->showMoney();

        return match ($request->type()) {
            'daily' => $this->reports->daily($request->day(), $showMoney),
            'monthly' => $this->reports->monthly($request->month(), $showMoney),
            'client' => ($client = $request->client())
                ? $this->reports->client($client, $request->from(), $request->to(), $showMoney)
                : null,
            'employees' => $this->reports->employees($request->client(), $request->from(), $request->to(), $showMoney),
            'menu' => $this->reports->menu($request->from(), $request->to()),
            'riders' => $this->reports->riders($request->from(), $request->to()),
        };
    }
}
