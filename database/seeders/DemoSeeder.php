<?php

namespace Database\Seeders;

use App\Enums\BillStatus;
use App\Enums\MealPreference;
use App\Enums\MealType;
use App\Enums\MenuCategory;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Enums\VehicleType;
use App\Models\Bill;
use App\Models\Client;
use App\Models\ClientMenuPrice;
use App\Models\DailyMenu;
use App\Models\Delivery;
use App\Models\Employee;
use App\Models\MealEntry;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Rider;
use App\Models\User;
use App\Services\BillingService;
use App\Services\PricingService;
use App\Support\Money;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * Sample data for local development and demos. Only runs when
 * APP_ENV=local. Each section only runs when its data is missing, so it
 * is safe to run again and never touches existing records.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! Client::withTrashed()->exists()) {
            $this->seedClients();
        }

        Client::query()->orderBy('id')->get()->each(function (Client $client, int $index) {
            if (! $client->employees()->exists()) {
                $this->seedEmployees($client, $index);
            }
        });

        if (! MenuItem::query()->exists()) {
            $this->seedMenus();
        }

        if (! ClientMenuPrice::query()->exists()) {
            $this->seedPrices();
        }

        if (! MealEntry::query()->exists()) {
            $this->seedMeals();
        }

        if (! Rider::query()->exists()) {
            $this->seedRiders();
        }

        if (! Order::query()->exists()) {
            $this->seedOrders();
        }

        if (! Bill::query()->exists()) {
            $this->seedBills();
        }
    }

    /**
     * Bills for last month's meals: the first client paid in full, the
     * second partly, the rest not yet. Written directly (not through
     * BillingService) so seeding sends no emails.
     */
    private function seedBills(): void
    {
        $billing = app(BillingService::class);
        $admin = User::query()->where('role', UserRole::VendorAdmin)->orderBy('id')->value('id');
        $start = now()->subMonthNoOverflow()->startOfMonth()->toDateString();
        $end = now()->subMonthNoOverflow()->endOfMonth()->toDateString();
        $issued = now()->toImmutable()->startOfMonth()->addDay();

        foreach (Client::query()->orderBy('id')->get() as $index => $client) {
            $totals = $billing->calculate($client->id, $start, $end);

            if ($totals['total_meals'] === 0) {
                continue;
            }

            $paid = match ($index) {
                0 => $totals['total_amount'],
                1 => Money::of(round((float) $totals['total_amount'] / 2)),
                default => '0.00',
            };
            $dueDate = $issued->addDays(15)->toDateString();

            $bill = Bill::create([
                ...$totals,
                'client_id' => $client->id,
                'period_start' => $start,
                'period_end' => $end,
                'paid_amount' => $paid,
                'due_amount' => Money::subtract($totals['total_amount'], $paid),
                'status' => BillStatus::resolve($totals['total_amount'], $paid, $dueDate, now()->toDateString()),
                'issue_date' => $issued->toDateString(),
                'due_date' => $dueDate,
                'created_by' => $admin,
            ]);
            $bill->update(['bill_number' => Bill::numberFor($bill->id, $issued->toDateString())]);

            if (Money::isPositive($paid)) {
                Payment::create([
                    'bill_id' => $bill->id,
                    'client_id' => $client->id,
                    'payment_date' => $issued->addDays(3)->toDateString(),
                    'amount' => $paid,
                    'method' => $index === 0 ? PaymentMethod::Bank : PaymentMethod::Bkash,
                    'reference' => $index === 0 ? 'BANK-TRX-1001' : 'BK8F3K2L9Q',
                    'received_by' => $admin,
                ]);
            }
        }
    }

    /**
     * Three riders; Rahim has a login for the rider app (01800000001 / password).
     */
    private function seedRiders(): void
    {
        $riders = [
            ['name' => 'Rahim Uddin', 'phone' => '01800000001', 'vehicle_type' => VehicleType::Motorcycle, 'vehicle_number' => 'DHA-METRO-HA-11-2233', 'login' => true],
            ['name' => 'Karim Mia', 'phone' => '01800000002', 'vehicle_type' => VehicleType::Bicycle, 'vehicle_number' => null, 'login' => false],
            ['name' => 'Sohel Rana', 'phone' => '01800000003', 'vehicle_type' => VehicleType::Van, 'vehicle_number' => 'DHA-METRO-CHA-15-4455', 'login' => false],
        ];

        foreach ($riders as $index => $row) {
            $rider = Rider::create([
                'name' => $row['name'],
                'phone' => $row['phone'],
                'address' => 'Mirpur '.(10 + $index).', Dhaka',
                'emergency_contact' => 'Family - 0171900000'.$index,
                'vehicle_type' => $row['vehicle_type'],
                'vehicle_number' => $row['vehicle_number'],
                'joining_date' => now()->subMonths(8 + $index)->toDateString(),
                'status' => RecordStatus::Active,
            ]);

            if ($row['login']) {
                $user = new User(['name' => $row['name'], 'phone' => $row['phone'], 'password' => 'password']);
                $user->forceFill(['role' => UserRole::Rider, 'status' => RecordStatus::Active])->save();
                $rider->user()->associate($user)->save();
            }
        }
    }

    /**
     * Orders for the existing meals, so delivery history, reports and billing
     * have data: past days delivered, today and later still in the kitchen.
     * Built from the meals themselves so totals always match.
     */
    private function seedOrders(): void
    {
        $riders = Rider::query()->orderBy('id')->pluck('id')->all();
        $admin = User::query()->where('role', UserRole::VendorAdmin)->orderBy('id')->value('id');

        if ($riders === []) {
            return;
        }

        $today = now()->toDateString();
        $groups = MealEntry::query()
            ->confirmed()
            ->selectRaw('client_id, meal_date, meal_type, COUNT(*) as quantity, SUM(unit_price) as amount')
            ->groupBy('client_id', 'meal_date', 'meal_type')
            ->toBase()
            ->get();

        $clients = Client::query()->get()->keyBy('id');

        foreach ($groups as $index => $group) {
            $date = substr((string) $group->meal_date, 0, 10);
            $client = $clients[$group->client_id];
            $deliveredAt = now()->setDateFrom($date)->setTime($group->meal_type === 'lunch' ? 12 : 19, 40);
            $past = $date < $today;

            $order = Order::create([
                'client_id' => $client->id,
                'order_date' => $date,
                'meal_type' => $group->meal_type,
                'quantity' => (int) $group->quantity,
                'amount' => Money::of($group->amount),
                'status' => $past ? OrderStatus::Delivered : OrderStatus::Confirmed,
                'delivery_address' => $client->deliveryAddress(),
                'delivery_time' => $group->meal_type === 'lunch' ? '13:00' : '20:00',
                'created_by' => $admin,
            ]);
            $order->update(['order_number' => Order::numberFor($order->id, $date)]);

            if (! $past) {
                continue;
            }

            Delivery::create([
                'order_id' => $order->id,
                'rider_id' => $riders[$index % count($riders)],
                'assigned_by' => $admin,
                'assigned_at' => $deliveredAt->copy()->subHours(2),
                'picked_up_at' => $deliveredAt->copy()->subMinutes(50),
                'on_the_way_at' => $deliveredAt->copy()->subMinutes(45),
                'delivered_at' => $deliveredAt,
            ]);
        }
    }

    /**
     * Meals for the past three weeks (weekdays), with the price each client
     * paid on the day. Today is left open so the meal sheet can be tried.
     */
    private function seedMeals(): void
    {
        $pricing = app(PricingService::class);
        $admin = User::query()->where('role', UserRole::VendorAdmin)->orderBy('id')->first();
        $rows = [];

        foreach (Client::query()->active()->with(['employees' => fn ($query) => $query->active()])->get() as $client) {
            foreach (range(21, 1) as $daysAgo) {
                $date = now()->subDays($daysAgo);

                if ($date->isFriday()) {
                    continue;
                }

                foreach ($client->mealTypes() as $type) {
                    $price = $pricing->priceFor($client, $type, $date->toDateString())['price'];

                    foreach ($client->employees as $index => $employee) {
                        // Most employees eat most days; vary it a little per day.
                        if (! $employee->takes($type) || ($index + $daysAgo) % 7 === 0) {
                            continue;
                        }

                        $rows[] = [
                            'client_id' => $client->id,
                            'employee_id' => $employee->id,
                            'meal_date' => $date->toDateString(),
                            'meal_type' => $type->value,
                            'status' => 'confirmed',
                            'unit_price' => $price,
                            'confirmed_by' => $admin?->id,
                            'confirmed_at' => $date->copy()->setTime(9, 30),
                            'created_at' => $date->copy()->setTime(9, 30),
                            'updated_at' => $date->copy()->setTime(9, 30),
                        ];
                    }
                }
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            MealEntry::insert($chunk);
        }
    }

    private function seedMenus(): void
    {
        $items = collect([
            ['Plain Rice', MenuCategory::MainCourse, '30.00', 'Steamed white rice'],
            ['Polao', MenuCategory::MainCourse, '45.00', 'Fragrant rice with ghee'],
            ['Chicken Biryani', MenuCategory::MainCourse, '120.00', 'Kacchi-style chicken biryani'],
            ['Chicken Curry', MenuCategory::Protein, '70.00', 'Home-style chicken curry'],
            ['Beef Bhuna', MenuCategory::Protein, '90.00', null],
            ['Rui Fish Curry', MenuCategory::Protein, '80.00', null],
            ['Egg Curry', MenuCategory::Protein, '35.00', null],
            ['Mixed Vegetable', MenuCategory::SideDish, '25.00', 'Seasonal vegetables'],
            ['Dal', MenuCategory::SideDish, '15.00', 'Yellow lentil soup'],
            ['Salad', MenuCategory::SideDish, '10.00', null],
            ['Borhani', MenuCategory::Beverage, '30.00', 'Spiced yoghurt drink'],
            ['Firni', MenuCategory::Dessert, '35.00', 'Rice pudding'],
        ])->mapWithKeys(fn (array $row) => [$row[0] => MenuItem::create([
            'name' => $row[0],
            'category' => $row[1],
            'default_price' => $row[2],
            'description' => $row[3],
            'status' => RecordStatus::Active,
        ])]);

        $lunchMenus = [
            ['Plain Rice', 'Chicken Curry', 'Dal', 'Mixed Vegetable', 'Salad'],
            ['Plain Rice', 'Rui Fish Curry', 'Dal', 'Mixed Vegetable'],
            ['Polao', 'Beef Bhuna', 'Salad', 'Firni'],
            ['Plain Rice', 'Egg Curry', 'Dal', 'Mixed Vegetable'],
            ['Chicken Biryani', 'Salad', 'Borhani'],
        ];
        $dinnerMenus = [
            ['Plain Rice', 'Chicken Curry', 'Dal'],
            ['Plain Rice', 'Egg Curry', 'Mixed Vegetable', 'Dal'],
        ];

        foreach (range(-20, 3) as $offset) {
            $date = now()->addDays($offset);

            if ($date->isFriday()) {
                continue;
            }

            $this->createMenu($date->toDateString(), MealType::Lunch, $lunchMenus[abs($offset) % count($lunchMenus)], $items, $date->isThursday() ? '150.00' : null);
            $this->createMenu($date->toDateString(), MealType::Dinner, $dinnerMenus[abs($offset) % count($dinnerMenus)], $items, null);
        }
    }

    /**
     * @param  list<string>  $names
     * @param  Collection<string, MenuItem>  $items
     */
    private function createMenu(string $date, MealType $type, array $names, $items, ?string $price): void
    {
        $menu = DailyMenu::create(['menu_date' => $date, 'meal_type' => $type, 'price' => $price]);

        foreach ($names as $position => $name) {
            $menu->items()->attach($items[$name]->id, ['sort_order' => $position]);
        }
    }

    private function seedPrices(): void
    {
        $prices = [
            'XYZ Software Ltd.' => ['lunch' => '120.00', 'dinner' => '140.00'],
            'ABC Bank Ltd.' => ['lunch' => '130.00'],
            'Tech Solutions Ltd.' => ['lunch' => '150.00', 'dinner' => '150.00'],
        ];

        foreach ($prices as $name => $mealPrices) {
            $client = Client::query()->where('name', $name)->first();

            foreach ($client ? $mealPrices : [] as $type => $price) {
                $record = new ClientMenuPrice([
                    'meal_type' => $type,
                    'price' => $price,
                    'effective_from' => now()->subMonths(2)->startOfMonth()->toDateString(),
                ]);
                $record->client_id = $client->id;
                $record->save();
            }
        }
    }

    private function seedClients(): void
    {
        $clients = [
            ['name' => 'XYZ Software Ltd.', 'contact_person' => 'Tanvir Ahmed', 'city' => 'Dhaka', 'dinner' => true, 'login' => 'xyz@example.com'],
            ['name' => 'ABC Bank Ltd.', 'contact_person' => 'Nusrat Jahan', 'city' => 'Dhaka', 'dinner' => false, 'login' => 'abcbank@example.com'],
            ['name' => 'Tech Solutions Ltd.', 'contact_person' => 'Rakib Hasan', 'city' => 'Dhaka', 'dinner' => true, 'login' => null],
            ['name' => 'Digital Bangladesh Ltd.', 'contact_person' => 'Farhana Akter', 'city' => 'Chattogram', 'dinner' => false, 'login' => null],
        ];

        foreach ($clients as $index => $row) {
            $client = Client::create([
                'name' => $row['name'],
                'contact_person' => $row['contact_person'],
                'phone' => '0171100000'.$index,
                'email' => 'office'.$index.'@example.com',
                'address' => 'House '.(10 + $index).', Road 5, Gulshan',
                'city' => $row['city'],
                'delivery_address' => $index === 0 ? '5th floor reception, House 10, Road 5, Gulshan' : null,
                'employee_count' => 20 + $index * 10,
                'lunch_enabled' => true,
                'dinner_enabled' => $row['dinner'],
                'office_start_time' => '09:00',
                'lunch_cutoff_time' => '10:00',
                'dinner_cutoff_time' => $row['dinner'] ? '16:00' : null,
                'special_instructions' => $index === 0 ? 'Call the reception on arrival.' : null,
                'status' => RecordStatus::Active,
            ]);

            if ($row['login']) {
                $user = new User(['name' => $row['contact_person'], 'email' => $row['login'], 'password' => 'password']);
                $user->forceFill([
                    'role' => UserRole::ClientAdmin,
                    'client_id' => $client->id,
                    'status' => RecordStatus::Active,
                    'email_verified_at' => now(),
                ])->save();
            }
        }
    }

    private function seedEmployees(Client $client, int $clientIndex): void
    {
        $names = [
            'Abdullah Al Mamun', 'Rahim Uddin', 'Karim Hossain', 'Hasan Mahmud', 'Nusrat Jahan', 'Farhana Akter',
            'Tanvir Ahmed', 'Sadia Islam', 'Rakib Hasan', 'Mehedi Hasan', 'Sumaiya Rahman', 'Arif Chowdhury',
            'Jannatul Ferdous', 'Imran Kabir', 'Shakil Ahmed', 'Taslima Begum', 'Nazmul Huda', 'Rifat Sultana',
        ];
        $departments = ['Engineering', 'Finance', 'Sales', 'Operations', 'HR'];
        $count = 10 + $clientIndex * 2;

        foreach (array_slice($names, 0, $count) as $i => $name) {
            $employee = new Employee([
                'employee_code' => sprintf('EMP-%03d', $i + 1),
                'name' => $name,
                'phone' => sprintf('0191%07d', $clientIndex * 1000 + $i),
                'department' => $departments[$i % count($departments)],
                'designation' => $i % 3 === 0 ? 'Manager' : 'Executive',
                'meal_preference' => $i % 7 === 3 ? MealPreference::Vegetarian : MealPreference::Regular,
                'lunch_enabled' => true,
                'dinner_enabled' => $client->dinner_enabled && $i % 3 !== 0,
                'status' => $i === $count - 1 ? RecordStatus::Inactive : RecordStatus::Active,
                'joining_date' => now()->subMonths(6 + $i)->toDateString(),
            ]);
            $employee->client_id = $client->id;
            $employee->save();
        }
    }
}
