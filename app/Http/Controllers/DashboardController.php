<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Services\MealCutoff;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardService $dashboard, MealCutoff $cutoff, ReportService $reports): Response
    {
        $user = $request->user();

        return Inertia::render('dashboard', [
            'stats' => $dashboard->statsFor($user),
            'today' => now()->toDateString(),
            'todaysOrders' => $user->isVendorUser() ? $dashboard->todaysOrders($user) : null,
            'clientToday' => $user->isClientAdmin() ? $dashboard->clientToday($user, $cutoff) : null,
            // Meal counts only, no amounts.
            'trend' => $user->isVendorUser() ? $reports->recentDays(14) : null,
        ]);
    }
}
