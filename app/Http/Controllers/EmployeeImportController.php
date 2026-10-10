<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Employee;
use App\Services\EmployeeImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Bulk employee import from CSV.
 */
class EmployeeImportController extends Controller
{
    public function __construct(private readonly EmployeeImporter $importer) {}

    public function create(Request $request): Response
    {
        $this->authorize('create', Employee::class);

        $user = $request->user();

        return Inertia::render('employees/import', [
            'clients' => $user->isVendorUser()
                ? Client::active()->orderBy('name')->get(['id', 'name'])
                    ->map(fn (Client $client) => ['value' => (string) $client->id, 'label' => $client->name])
                : null,
            'selectedClient' => $user->isVendorUser() ? ($request->integer('client') ?: null) : $user->client_id,
            'columns' => EmployeeImporter::COLUMNS,
            'maxRows' => EmployeeImporter::MAX_ROWS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Employee::class);

        $user = $request->user();

        $validated = $request->validate([
            'client_id' => [$user->isVendorUser() ? 'required' : 'nullable', 'integer'],
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ], [
            'file.mimes' => 'Upload a CSV file. In Excel use File → Save As → CSV.',
        ]);

        // Client accounts always import into their own organization.
        $client = Client::find($user->isVendorUser() ? $validated['client_id'] : $user->client_id);

        if (! $client) {
            throw ValidationException::withMessages(['client_id' => 'Choose an active client.']);
        }

        $this->authorize('createFor', [Employee::class, $client]);

        try {
            $result = $this->importer->import($request->file('file'), $client, $user);
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages(['file' => $e->getMessage()]);
        }

        Inertia::flash('importResult', [...$result, 'client' => $client->name]);

        $this->toast(
            $result['imported'] > 0 ? "{$result['imported']} employees imported." : 'No employees were imported.',
            $result['imported'] > 0 ? 'success' : 'error',
        );

        return to_route('employees.import', $user->isVendorUser() ? ['client' => $client->id] : []);
    }

    public function template(Request $request): StreamedResponse
    {
        $this->authorize('create', Employee::class);

        $csv = $this->importer->template();

        return response()->streamDownload(fn () => print ($csv), 'employee-import-template.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
