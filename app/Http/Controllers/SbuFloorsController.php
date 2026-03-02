<?php

namespace App\Http\Controllers;

use App\Services\SbuFloorService;
use Illuminate\View\View;

class SbuFloorsController extends Controller
{
    public function __construct(
        private SbuFloorService $sbuFloorService
    ) {}

    public function index(): View
    {
        $sbuFloors = $this->sbuFloorService->getList();
        $counts = $this->sbuFloorService->getCounts();

        return view('admin.sbu.floor.index', [
            'sbuFloors' => $sbuFloors,
            'totalSbuFloors' => $counts['total'],
            'activeSbuFloors' => $counts['active'],
            'activePercentage' => $counts['active_percentage'],
        ]);
    }

    public function show(int $id): View
    {
        $sbuFloor = $this->sbuFloorService->findById($id);
        if (!$sbuFloor) {
            abort(404);
        }
        return view('admin.sbu.floor.show', ['sbuFloor' => $sbuFloor]);
    }
}
