<?php

namespace App\Http\Controllers;

use App\Services\SbuService;
use Illuminate\View\View;

class SbuController extends Controller
{
    public function __construct(
        private SbuService $sbuService
    ) {}

    public function index(): View
    {
        $sbus = $this->sbuService->getList();
        $counts = $this->sbuService->getCounts();

        return view('admin.sbu.index', [
            'sbus' => $sbus,
            'totalSbus' => $counts['total'],
            'activeSbus' => $counts['active'],
            'activePercentage' => $counts['active_percentage'],
        ]);
    }

    public function show(int $id): View
    {
        $sbu = $this->sbuService->findById($id);
        if (!$sbu) {
            abort(404);
        }
        return view('admin.sbu.show', ['sbu' => $sbu]);
    }
}
