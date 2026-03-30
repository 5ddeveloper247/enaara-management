<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use App\Services\DashboardService;
class DashboardController extends Controller
{
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }
    public function index(): View
    {
        return $this->dashboardService->index();
    }
}
