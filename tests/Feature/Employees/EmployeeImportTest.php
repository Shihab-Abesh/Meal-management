<?php

namespace Tests\Feature\Employees;

use App\Enums\MealPreference;
use App\Enums\RecordStatus;
use App\Models\Client;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class EmployeeImportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->vendorAdmin()->create();
        $this->client = Client::factory()->create();
    }

    public function test_import_page_and_template(): void
    {
        $this->actingAs($this->admin)->get('/employees/import')->assertOk();

        $template = $this->get('/employees/import/template');
        $template->assertOk()->assertDownload('employee-import-template.csv');
        $this->assertStringContainsString('employee_id,name,phone', $template->streamedContent());
    }

    public function test_valid_rows_are_imported(): void
    {
        $response = $this->import(<<<'CSV'
            Employee ID,Name,Phone,Email,Department,Designation,Meal Preference,Lunch,Dinner,Status,Joining Date,Notes
            EMP-001,Abdullah Al Mamun,01711-000000,ABDULLAH@example.com,Engineering,Engineer,Vegetarian,yes,yes,active,2025-01-15,
            EMP-002,Rahim Uddin,,,Finance,,,,,,15/02/2024,Allergic to nuts
            CSV);

        $response->assertSessionHasNoErrors()->assertRedirect("/employees/import?client={$this->client->id}");
        $result = $response->inertiaFlash('importResult');

        $this->assertSame(2, $result['total']);
        $this->assertSame(2, $result['imported']);
        $this->assertSame([], $result['failed']);
        $this->assertSame([], $result['duplicates']);

        $first = Employee::where('employee_code', 'EMP-001')->firstOrFail();
        $this->assertSame($this->client->id, $first->client_id);
        $this->assertSame('abdullah@example.com', $first->email);
        $this->assertSame(MealPreference::Vegetarian, $first->meal_preference);
        $this->assertTrue($first->dinner_enabled);

        // Defaults for empty optional columns.
        $second = Employee::where('employee_code', 'EMP-002')->firstOrFail();
        $this->assertSame(MealPreference::Regular, $second->meal_preference);
        $this->assertTrue($second->lunch_enabled);
        $this->assertFalse($second->dinner_enabled);
        $this->assertSame(RecordStatus::Active, $second->status);
        $this->assertSame('2024-02-15', $second->joining_date->toDateString());

        $this->assertDatabaseHas('activity_logs', ['module' => 'employees', 'action' => 'imported', 'client_id' => $this->client->id]);
    }

    public function test_invalid_rows_are_reported_and_skipped(): void
    {
        $result = $this->import(<<<'CSV'
            employee_id,name,phone,meal_preference,lunch,joining_date
            EMP-001,Valid Person,01711000000,regular,yes,2025-01-01
            EMP-002,,01711000000,regular,yes,
            EMP-003,Bad Phone,call me,regular,yes,
            EMP-004,Bad Preference,,carnivore,yes,
            EMP-005,Bad Lunch,,regular,sometimes,
            EMP-006,Bad Date,,regular,yes,31/02/2025
            CSV)->inertiaFlash('importResult');

        $this->assertSame(6, $result['total']);
        $this->assertSame(1, $result['imported']);
        $this->assertCount(5, $result['failed']);

        $errors = collect($result['failed'])->keyBy('employee_id');
        $this->assertSame(3, $errors['EMP-002']['row']);
        $this->assertStringContainsString('name is missing', $errors['EMP-002']['errors'][0]);
        $this->assertStringContainsString('Phone number', $errors['EMP-003']['errors'][0]);
        $this->assertStringContainsString('Meal preference "carnivore" is not valid', $errors['EMP-004']['errors'][0]);
        $this->assertStringContainsString('Lunch must be yes or no', $errors['EMP-005']['errors'][0]);
        $this->assertStringContainsString('Joining date', $errors['EMP-006']['errors'][0]);

        $this->assertSame(['EMP-001'], Employee::pluck('employee_code')->all());
    }

    public function test_duplicates_in_the_database_and_in_the_file_are_skipped(): void
    {
        Employee::factory()->forClient($this->client)->create(['employee_code' => 'EMP-001', 'name' => 'Existing']);
        // The same code at another client is not a duplicate.
        Employee::factory()->forClient(Client::factory()->create())->create(['employee_code' => 'EMP-002']);

        $result = $this->import(<<<'CSV'
            employee_id,name
            emp-001,Imported Again
            EMP-002,New Person
            EMP-002,Second Copy
            CSV)->inertiaFlash('importResult');

        $this->assertSame(1, $result['imported']);
        $this->assertCount(2, $result['duplicates']);
        $this->assertSame('Already exists at this client', $result['duplicates'][0]['reason']);
        $this->assertSame('Same ID as row 3', $result['duplicates'][1]['reason']);

        // Existing employees are never overwritten.
        $this->assertSame('Existing', Employee::where('client_id', $this->client->id)->where('employee_code', 'EMP-001')->value('name'));
    }

    public function test_semicolon_separated_files_with_a_byte_order_mark_are_read(): void
    {
        $result = $this->import("\u{FEFF}employee_id;name;dinner\nEMP-010;Semi Colon;yes\n")->inertiaFlash('importResult');

        $this->assertSame(1, $result['imported']);
        $this->assertTrue(Employee::firstOrFail()->dinner_enabled);
    }

    public function test_unusable_files_are_rejected_with_a_message(): void
    {
        $this->import("name,phone\nNo Code,0171\n")->assertSessionHasErrors(['file']);
        $this->import("employee_id,name\n")->assertSessionHasErrors(['file']);

        $this->actingAs($this->admin)
            ->post('/employees/import', [
                'client_id' => $this->client->id,
                'file' => UploadedFile::fake()->create('employees.pdf', 10, 'application/pdf'),
            ])
            ->assertSessionHasErrors('file');

        $this->assertSame(0, Employee::count());
    }

    public function test_vendor_must_choose_an_active_client(): void
    {
        $this->actingAs($this->admin)
            ->post('/employees/import', ['file' => $this->csv("employee_id,name\nEMP-1,A\n")])
            ->assertSessionHasErrors('client_id');

        $inactive = Client::factory()->inactive()->create();

        $this->post('/employees/import', ['client_id' => $inactive->id, 'file' => $this->csv("employee_id,name\nEMP-1,A\n")])
            ->assertForbidden();

        $this->assertSame(0, Employee::count());
    }

    public function test_client_admin_imports_into_their_own_client_only(): void
    {
        $other = Client::factory()->create();
        $clientAdmin = User::factory()->clientAdmin($this->client)->create();

        $this->actingAs($clientAdmin)
            ->post('/employees/import', ['client_id' => $other->id, 'file' => $this->csv("employee_id,name\nEMP-1,Mine\n")])
            ->assertSessionHasNoErrors();

        $this->assertSame($this->client->id, Employee::firstOrFail()->client_id);
        $this->assertSame(0, $other->employees()->count());
    }

    public function test_riders_cannot_import(): void
    {
        $this->actingAs(User::factory()->rider()->create())
            ->post('/employees/import', ['client_id' => $this->client->id, 'file' => $this->csv("employee_id,name\nEMP-1,A\n")])
            ->assertForbidden();
    }

    private function import(string $contents): TestResponse
    {
        return $this->actingAs($this->admin)->post('/employees/import', [
            'client_id' => $this->client->id,
            'file' => $this->csv($contents),
        ]);
    }

    private function csv(string $contents): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('employees.csv', $contents);
    }
}
