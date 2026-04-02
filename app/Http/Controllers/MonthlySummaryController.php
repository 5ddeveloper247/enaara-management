<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MonthlySummaryService;

class MonthlySummaryController extends Controller
{
    protected MonthlySummaryService $monthlySummaryService;

    public function __construct(MonthlySummaryService $monthlySummaryService)
    {
        $this->monthlySummaryService = $monthlySummaryService;
    }

    public function index(Request $request)
    {
        return $this->monthlySummaryService->index($request);
    }
}