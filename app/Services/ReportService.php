<?php

namespace App\Services;

use App\Enums\MealType;
use App\Enums\OrderStatus;
use App\Models\Bill;
use App\Models\Client;
use App\Models\DailyMenu;
use App\Models\Delivery;
use App\Models\Employee;
use App\Models\MealEntry;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Rider;
use App\Support\Money;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;

/**
 * Builds every report in one shape, so the screen, CSV and PDF exports all
 * show the same figures:
 *
 *   title, subtitle, summary [{key, label, value, format, hint?}],
 *   columns [{key, label, format, align?}], rows [...], totals {...}|null,
 *   charts {...}
 *
 * format is "text", "number", "currency" or "date". Currency columns and
 * figures are removed when the viewer may not see amounts. All figures come
 * from confirmed meals, orders, bills and payments - nothing is stored twice.
 */
class ReportService
{
    public const TYPES = ['daily', 'monthly', 'client', 'employees', 'menu', 'riders'];

    public function __construct(private readonly ClientStatement $statements) {}

    /**
     * @return array<string, mixed>
     */
    public function daily(string $date, bool $showMoney): array
    {
        $day = CarbonImmutable::parse($date);
        $perClient = $this->mealsByClient($date, $date);

        $orders = Order::query()->where('order_date', $date)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->toBase()
            ->pluck('total', 'status');
        $open = collect(OrderStatus::open())->sum(fn (OrderStatus $status) => (int) ($orders[$status->value] ?? 0));

        $totals = $this->sumRows($perClient, ['lunch', 'dinner', 'meals'], ['amount']);

        return $this->finish([
            'type' => 'daily',
            'title' => 'Daily report',
            'subtitle' => $day->format('l, d F Y'),
            'summary' => [
                $this->figure('clients', 'Clients served', count($perClient), 'number', Client::query()->active()->count().' active clients'),
                $this->figure('employees', 'Total employees', $this->activeEmployees(), 'number', 'Across active clients'),
                $this->figure('lunch', 'Lunch meals', $totals['lunch']),
                $this->figure('dinner', 'Dinner meals', $totals['dinner']),
                $this->figure('meals', 'Total meals', $totals['meals']),
                $this->figure('revenue', 'Total revenue', $totals['amount'], 'currency'),
                $this->figure('pending', 'Pending orders', $open, 'number', 'Not delivered yet'),
                $this->figure('delivered', 'Completed deliveries', (int) ($orders[OrderStatus::Delivered->value] ?? 0)),
            ],
            'columns' => [
                $this->column('client', 'Client', 'text'),
                $this->column('lunch', 'Lunch'),
                $this->column('dinner', 'Dinner'),
                $this->column('meals', 'Total'),
                $this->column('amount', 'Amount', 'currency'),
            ],
            'rows' => $perClient,
            'totals' => ['client' => 'Total', ...$totals],
        ], $showMoney);
    }

    /**
     * @return array<string, mixed>
     */
    public function monthly(string $month, bool $showMoney): array
    {
        $start = CarbonImmutable::createFromFormat('!Y-m', $month);
        $from = $start->toDateString();
        $to = $start->endOfMonth()->toDateString();

        $rows = $this->mealsByClient($from, $to);
        $paidByClient = Bill::query()
            ->whereBetween('period_start', [$from, $to])
            ->selectRaw('client_id, COALESCE(SUM(paid_amount), 0) as paid')
            ->groupBy('client_id')
            ->toBase()
            ->pluck('paid', 'client_id');

        foreach ($rows as &$row) {
            $row['paid'] = Money::of($paidByClient[$row['client_id']] ?? 0);
            $due = Money::subtract($row['amount'], $row['paid']);
            $row['due'] = Money::isPositive($due) ? $due : '0.00';
        }
        unset($row);

        $totals = $this->sumRows($rows, ['lunch', 'dinner', 'meals'], ['amount', 'paid', 'due']);

        return $this->finish([
            'type' => 'monthly',
            'title' => 'Monthly report',
            'subtitle' => $start->format('F Y'),
            'summary' => [
                $this->figure('meals', 'Total meals', $totals['meals'], 'number', count($rows).' clients'),
                $this->figure('lunch', 'Lunch', $totals['lunch']),
                $this->figure('dinner', 'Dinner', $totals['dinner']),
                $this->figure('revenue', 'Total revenue', $totals['amount'], 'currency', 'Value of confirmed meals'),
                $this->figure('paid', 'Paid', $totals['paid'], 'currency', "Paid on this month's bills"),
                $this->figure('due', 'Due', $totals['due'], 'currency', 'Not paid yet, billed or not'),
            ],
            'columns' => [
                $this->column('client', 'Client', 'text'),
                $this->column('lunch', 'Lunch'),
                $this->column('dinner', 'Dinner'),
                $this->column('meals', 'Total'),
                $this->column('amount', 'Revenue', 'currency'),
                $this->column('paid', 'Paid', 'currency'),
                $this->column('due', 'Due', 'currency'),
            ],
            'rows' => $rows,
            'totals' => ['client' => 'Total', ...$totals],
            'charts' => [
                'daily' => $this->dailySeries($from, $to),
                'months' => $this->monthlySeries($start),
                'clients' => collect($rows)
                    ->map(fn (array $row) => [
                        'label' => $row['client'],
                        'value' => $showMoney ? $row['amount'] : $row['meals'],
                    ])
                    ->sortByDesc(fn (array $item) => (float) $item['value'])
                    ->values()
                    ->all(),
                'clientsFormat' => $showMoney ? 'currency' : 'number',
            ],
        ], $showMoney);
    }

    /**
     * One client's meals per day in a date range, with what was paid in the
     * range and the balance owed now.
     *
     * @return array<string, mixed>
     */
    public function client(Client $client, string $from, string $to, bool $showMoney): array
    {
        $rows = $this->mealsByDay($from, $to, fn (Builder $query) => $query->where('client_id', $client->id));
        $totals = $this->sumRows($rows, ['lunch', 'dinner', 'meals'], ['amount']);
        $paid = Money::of(Payment::query()->where('client_id', $client->id)->whereBetween('payment_date', [$from, $to])->sum('amount'));
        $balance = $this->statements->balance($client);

        return $this->finish([
            'type' => 'client',
            'title' => 'Client report',
            'subtitle' => $client->name.' · '.$this->rangeLabel($from, $to),
            'summary' => [
                $this->figure('meals', 'Total meals', $totals['meals'], 'number', "{$totals['lunch']} lunch · {$totals['dinner']} dinner"),
                $this->figure('revenue', 'Revenue', $totals['amount'], 'currency', 'Value of meals in the period'),
                $this->figure('paid', 'Paid', $paid, 'currency', 'Payments received in the period'),
                $this->figure('due', 'Due', $balance['due'], 'currency', 'Unpaid bills today'),
            ],
            'columns' => [
                $this->column('date', 'Date', 'date'),
                $this->column('lunch', 'Lunch'),
                $this->column('dinner', 'Dinner'),
                $this->column('meals', 'Total'),
                $this->column('amount', 'Amount', 'currency'),
            ],
            'rows' => $rows,
            'totals' => ['date' => 'Total', ...$totals],
            'charts' => ['daily' => $this->dailySeries($from, $to, $client->id)],
        ], $showMoney);
    }

    /**
     * Meals per employee in a date range, for one client or all.
     *
     * @return array<string, mixed>
     */
    public function employees(?Client $client, string $from, string $to, bool $showMoney): array
    {
        $counts = MealEntry::query()
            ->confirmed()
            ->whereBetween('meal_date', [$from, $to])
            ->when($client, fn (Builder $query) => $query->where('client_id', $client->id))
            ->selectRaw('employee_id, meal_type, COUNT(*) as meals, COALESCE(SUM(unit_price), 0) as amount')
            ->groupBy('employee_id', 'meal_type')
            ->toBase()
            ->get()
            ->groupBy('employee_id');

        $employees = Employee::query()
            ->with('client:id,name')
            ->whereIn('id', $counts->keys())
            ->orderBy('name')
            ->get(['id', 'client_id', 'employee_code', 'name', 'department']);

        $rows = $employees->map(function (Employee $employee) use ($counts) {
            $byType = $counts[$employee->id]->keyBy('meal_type');
            $lunch = (int) ($byType[MealType::Lunch->value]->meals ?? 0);
            $dinner = (int) ($byType[MealType::Dinner->value]->meals ?? 0);

            return [
                'employee_code' => $employee->employee_code,
                'name' => $employee->name,
                'client' => $employee->client->name,
                'department' => $employee->department,
                'lunch' => $lunch,
                'dinner' => $dinner,
                'meals' => $lunch + $dinner,
                'amount' => Money::add(...$counts[$employee->id]->pluck('amount')->map(fn ($amount) => Money::of($amount))->all()),
            ];
        })->all();

        $totals = $this->sumRows($rows, ['lunch', 'dinner', 'meals'], ['amount']);

        return $this->finish([
            'type' => 'employees',
            'title' => 'Employee report',
            'subtitle' => ($client ? $client->name.' · ' : 'All clients · ').$this->rangeLabel($from, $to),
            'summary' => [
                $this->figure('employees', 'Employees with meals', count($rows)),
                $this->figure('lunch', 'Lunch', $totals['lunch']),
                $this->figure('dinner', 'Dinner', $totals['dinner']),
                $this->figure('amount', 'Total amount', $totals['amount'], 'currency'),
            ],
            'columns' => array_values(array_filter([
                $this->column('employee_code', 'ID', 'text'),
                $this->column('name', 'Employee', 'text'),
                $client ? null : $this->column('client', 'Client', 'text'),
                $this->column('department', 'Department', 'text'),
                $this->column('lunch', 'Lunch'),
                $this->column('dinner', 'Dinner'),
                $this->column('meals', 'Total'),
                $this->column('amount', 'Amount', 'currency'),
            ])),
            'rows' => $rows,
            'totals' => ['employee_code' => 'Total', ...$totals],
        ], $showMoney);
    }

    /**
     * How often each menu item was served, and to how many meals.
     *
     * @return array<string, mixed>
     */
    public function menu(string $from, string $to): array
    {
        $menus = DailyMenu::query()
            ->with('items:id,name,category')
            ->whereBetween('menu_date', [$from, $to])
            ->get(['id', 'menu_date', 'meal_type']);

        // Confirmed meals per date and meal type: the portions each dish went into.
        $meals = MealEntry::query()
            ->confirmed()
            ->whereBetween('meal_date', [$from, $to])
            ->selectRaw('meal_date, meal_type, COUNT(*) as meals')
            ->groupBy('meal_date', 'meal_type')
            ->toBase()
            ->get()
            ->mapWithKeys(fn ($row) => [substr((string) $row->meal_date, 0, 10).'|'.$row->meal_type => (int) $row->meals]);

        $items = [];

        foreach ($menus as $menu) {
            $date = $menu->menu_date->toDateString();
            $served = $meals[$date.'|'.$menu->meal_type->value] ?? 0;

            foreach ($menu->items as $item) {
                $items[$item->id] ??= [
                    'name' => $item->name,
                    'category' => $item->category->label(),
                    'lunch' => 0,
                    'dinner' => 0,
                    'usage' => 0,
                    'meals' => 0,
                    'first' => $date,
                    'last' => $date,
                ];
                $items[$item->id][$menu->meal_type->value]++;
                $items[$item->id]['usage']++;
                $items[$item->id]['meals'] += $served;
                $items[$item->id]['first'] = min($items[$item->id]['first'], $date);
                $items[$item->id]['last'] = max($items[$item->id]['last'], $date);
            }
        }

        $rows = collect($items)->sortBy([['usage', 'desc'], ['name', 'asc']])->values()->all();

        return $this->finish([
            'type' => 'menu',
            'title' => 'Menu report',
            'subtitle' => $this->rangeLabel($from, $to),
            'summary' => [
                $this->figure('menus', 'Menus served', $menus->count(), 'number', 'Lunch and dinner menus'),
                $this->figure('items', 'Different items', count($rows)),
                $this->figure('top', 'Most served', $rows[0]['name'] ?? '—', 'text', isset($rows[0]) ? "{$rows[0]['usage']} times" : null),
            ],
            'columns' => [
                $this->column('name', 'Menu item', 'text'),
                $this->column('category', 'Category', 'text'),
                $this->column('usage', 'Times on menu'),
                $this->column('lunch', 'Lunch'),
                $this->column('dinner', 'Dinner'),
                $this->column('meals', 'Meals served'),
                $this->column('first', 'First served', 'date'),
                $this->column('last', 'Last served', 'date'),
            ],
            'rows' => $rows,
            'totals' => null,
        ], true);
    }

    /**
     * Deliveries per rider for orders in a date range.
     *
     * @return array<string, mixed>
     */
    public function riders(string $from, string $to): array
    {
        $stats = Delivery::query()
            ->join('orders', 'orders.id', '=', 'deliveries.order_id')
            ->whereBetween('orders.order_date', [$from, $to])
            ->selectRaw('deliveries.rider_id as rider_id, orders.status as status, COUNT(*) as total, COALESCE(SUM(orders.quantity), 0) as meals')
            ->groupBy('deliveries.rider_id', 'orders.status')
            ->toBase()
            ->get()
            ->groupBy('rider_id');

        $open = array_map(fn (OrderStatus $status) => $status->value, [OrderStatus::Assigned, OrderStatus::PickedUp, OrderStatus::OnTheWay]);

        $rows = Rider::query()
            ->where(fn (Builder $query) => $query->active()->orWhereIn('id', $stats->keys()))
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'status'])
            ->map(function (Rider $rider) use ($stats, $open) {
                $byStatus = ($stats[$rider->id] ?? collect())->keyBy('status');
                $count = fn (string $status) => (int) ($byStatus[$status]->total ?? 0);

                return [
                    'name' => $rider->name,
                    'phone' => $rider->phone,
                    'assigned' => (int) $byStatus->sum('total'),
                    'completed' => $count(OrderStatus::Delivered->value),
                    'cancelled' => $count(OrderStatus::Cancelled->value),
                    'pending' => array_sum(array_map($count, $open)),
                    'meals' => (int) ($byStatus[OrderStatus::Delivered->value]->meals ?? 0),
                ];
            })
            ->all();

        $totals = $this->sumRows($rows, ['assigned', 'completed', 'cancelled', 'pending', 'meals'], []);

        return $this->finish([
            'type' => 'riders',
            'title' => 'Rider report',
            'subtitle' => $this->rangeLabel($from, $to),
            'summary' => [
                $this->figure('assigned', 'Assigned', $totals['assigned']),
                $this->figure('completed', 'Completed', $totals['completed']),
                $this->figure('pending', 'Pending', $totals['pending'], 'number', 'On the road'),
                $this->figure('cancelled', 'Cancelled', $totals['cancelled']),
            ],
            'columns' => [
                $this->column('name', 'Rider', 'text'),
                $this->column('phone', 'Phone', 'text'),
                $this->column('assigned', 'Assigned'),
                $this->column('completed', 'Completed'),
                $this->column('cancelled', 'Cancelled'),
                $this->column('pending', 'Pending'),
                $this->column('meals', 'Meals delivered'),
            ],
            'rows' => $rows,
            'totals' => ['name' => 'Total', ...$totals],
        ], true);
    }

    /**
     * Confirmed meals per day for the last 14 days, for the dashboard.
     *
     * @return list<array{date: string, label: string, lunch: int, dinner: int}>
     */
    public function recentDays(int $days = 14): array
    {
        $to = now()->toDateString();

        return $this->dailySeries(now()->subDays($days - 1)->toDateString(), $to);
    }

    public function rangeLabel(string $from, string $to): string
    {
        $start = CarbonImmutable::parse($from);
        $end = CarbonImmutable::parse($to);

        if ($start->isSameDay($start->startOfMonth()) && $end->isSameDay($start->endOfMonth())) {
            return $start->format('F Y');
        }

        return $start->isSameDay($end) ? $start->format('d M Y') : $start->format('d M Y').' – '.$end->format('d M Y');
    }

    /**
     * Confirmed meals and their value per client in a date range.
     *
     * @return list<array{client_id: int, client: string, lunch: int, dinner: int, meals: int, amount: string}>
     */
    private function mealsByClient(string $from, string $to): array
    {
        $rows = MealEntry::query()
            ->confirmed()
            ->whereBetween('meal_date', [$from, $to])
            ->selectRaw('client_id, meal_type, COUNT(*) as meals, COALESCE(SUM(unit_price), 0) as amount')
            ->groupBy('client_id', 'meal_type')
            ->toBase()
            ->get()
            ->groupBy('client_id');

        $names = Client::withTrashed()->whereIn('id', $rows->keys())->pluck('name', 'id');

        return $rows->map(function ($types, $clientId) use ($names) {
            $byType = $types->keyBy('meal_type');
            $lunch = (int) ($byType[MealType::Lunch->value]->meals ?? 0);
            $dinner = (int) ($byType[MealType::Dinner->value]->meals ?? 0);

            return [
                'client_id' => (int) $clientId,
                'client' => $names[$clientId] ?? '—',
                'lunch' => $lunch,
                'dinner' => $dinner,
                'meals' => $lunch + $dinner,
                'amount' => Money::add(...$types->pluck('amount')->map(fn ($amount) => Money::of($amount))->all()),
            ];
        })->sortBy('client')->values()->all();
    }

    /**
     * Confirmed meals and value per day (only days with meals).
     *
     * @param  callable(Builder<MealEntry>): void|null  $scope
     * @return list<array{date: string, lunch: int, dinner: int, meals: int, amount: string}>
     */
    private function mealsByDay(string $from, string $to, ?callable $scope = null): array
    {
        $query = MealEntry::query()->confirmed()->whereBetween('meal_date', [$from, $to]);

        if ($scope) {
            $scope($query);
        }

        $days = [];

        foreach ($query->selectRaw('meal_date, meal_type, COUNT(*) as meals, COALESCE(SUM(unit_price), 0) as amount')->groupBy('meal_date', 'meal_type')->toBase()->get() as $row) {
            $date = substr((string) $row->meal_date, 0, 10);
            $days[$date] ??= ['date' => $date, 'lunch' => 0, 'dinner' => 0, 'meals' => 0, 'amount' => '0.00'];
            $days[$date][$row->meal_type] = (int) $row->meals;
            $days[$date]['meals'] += (int) $row->meals;
            $days[$date]['amount'] = Money::add($days[$date]['amount'], Money::of($row->amount));
        }

        ksort($days);

        return array_values($days);
    }

    /**
     * Every day of the range (including days without meals) for charts.
     *
     * @return list<array{date: string, label: string, lunch: int, dinner: int}>
     */
    private function dailySeries(string $from, string $to, ?int $clientId = null): array
    {
        $byDate = collect($this->mealsByDay($from, $to, $clientId ? fn (Builder $query) => $query->where('client_id', $clientId) : null))->keyBy('date');
        $series = [];

        foreach (CarbonPeriod::create($from, $to) as $day) {
            $date = $day->toDateString();
            $series[] = [
                'date' => $date,
                'label' => $day->format('j'),
                'lunch' => $byDate[$date]['lunch'] ?? 0,
                'dinner' => $byDate[$date]['dinner'] ?? 0,
            ];
        }

        return $series;
    }

    /**
     * Confirmed meals per month for the twelve months ending with $month.
     *
     * @return list<array{month: string, label: string, meals: int}>
     */
    private function monthlySeries(CarbonImmutable $month): array
    {
        $first = $month->subMonths(11)->startOfMonth();
        $counts = MealEntry::query()
            ->confirmed()
            ->whereBetween('meal_date', [$first->toDateString(), $month->endOfMonth()->toDateString()])
            ->selectRaw('meal_date, COUNT(*) as meals')
            ->groupBy('meal_date')
            ->toBase()
            ->get()
            ->groupBy(fn ($row) => substr((string) $row->meal_date, 0, 7))
            ->map(fn ($days) => (int) $days->sum('meals'));

        $series = [];

        for ($current = $first; $current <= $month; $current = $current->addMonth()) {
            $series[] = [
                'month' => $current->format('Y-m'),
                'label' => $current->format('M'),
                'meals' => $counts[$current->format('Y-m')] ?? 0,
            ];
        }

        return $series;
    }

    private function activeEmployees(): int
    {
        return Employee::query()->active()->whereIn('client_id', Client::query()->active()->select('id'))->count();
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @param  list<string>  $counts
     * @param  list<string>  $amounts
     * @return array<string, int|string>
     */
    private function sumRows(array $rows, array $counts, array $amounts): array
    {
        $totals = [];

        foreach ($counts as $key) {
            $totals[$key] = array_sum(array_column($rows, $key));
        }

        foreach ($amounts as $key) {
            $totals[$key] = Money::add(...array_column($rows, $key));
        }

        return $totals;
    }

    /**
     * @return array{key: string, label: string, value: int|string, format: string, hint: string|null}
     */
    private function figure(string $key, string $label, int|string $value, string $format = 'number', ?string $hint = null): array
    {
        return compact('key', 'label', 'value', 'format', 'hint');
    }

    /**
     * @return array{key: string, label: string, format: string, align: string}
     */
    private function column(string $key, string $label, string $format = 'number'): array
    {
        return ['key' => $key, 'label' => $label, 'format' => $format, 'align' => in_array($format, ['number', 'currency'], true) ? 'right' : 'left'];
    }

    /**
     * Drop amounts the viewer may not see, and internal row keys.
     *
     * @param  array<string, mixed>  $report
     * @return array<string, mixed>
     */
    private function finish(array $report, bool $showMoney): array
    {
        $report['charts'] ??= [];

        if (! $showMoney) {
            $moneyKeys = array_column(array_filter($report['columns'], fn (array $column) => $column['format'] === 'currency'), 'key');
            $report['columns'] = array_values(array_filter($report['columns'], fn (array $column) => $column['format'] !== 'currency'));
            $report['summary'] = array_values(array_filter($report['summary'], fn (array $figure) => $figure['format'] !== 'currency'));
            $report['rows'] = array_map(fn (array $row) => array_diff_key($row, array_flip($moneyKeys)), $report['rows']);
            $report['totals'] = $report['totals'] ? array_diff_key($report['totals'], array_flip($moneyKeys)) : null;
        }

        $report['rows'] = array_map(fn (array $row) => array_diff_key($row, ['client_id' => true]), $report['rows']);
        $report['showMoney'] = $showMoney;

        return $report;
    }
}
