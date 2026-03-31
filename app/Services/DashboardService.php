<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeLeaveRequest;
use App\Models\Geofence;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function index()
    {
        $geofences   = Geofence::with('sbu')->orderBy('name')->get();
        $counterStats = $this->getCounterStats();

        return view('admin.dashboard.index', compact('geofences', 'counterStats'));
    }

    public function getAttendanceChartData(int $days): array
    {
        $totalEmployees = Employee::where('is_active', true)->whereNull('deleted_at')->count();
        $labels  = [];
        $present = [];
        $absent  = [];
        $onLeave = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date   = now()->subDays($i)->toDateString();
            $label  = now()->subDays($i)->format('D');

            $onLeaveCount = EmployeLeaveRequest::where('status', 3)
                ->whereIn('action_type', [0, 2])
                ->where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->distinct('from_employee_id')
                ->count('from_employee_id');

            $presentCount = DB::table('shift_rosters')
                ->whereDate('roster_date', $date)
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->count();

            $absentCount = DB::table('shift_rosters')
                ->whereDate('roster_date', $date)
                ->where('status', 3)
                ->whereNull('deleted_at')
                ->count();

            if ($presentCount === 0 && $absentCount === 0) {
                $presentCount = max(0, $totalEmployees - $onLeaveCount);
                $absentCount  = 0;
            }

            $labels[]  = $label;
            $present[] = $presentCount;
            $absent[]  = $absentCount;
            $onLeave[] = $onLeaveCount;
        }

        return compact('labels', 'present', 'absent', 'onLeave');
    }

    private function getCounterStats(): array
    {
        $today     = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // ── Total Employees ──────────────────────────────────────────────
        $totalToday     = Employee::whereNull('deleted_at')->count();
        $totalYesterday = Employee::whereNull('deleted_at')
            ->whereDate('created_at', '<=', $yesterday)
            ->count();
        $totalDelta     = $this->percentDelta($totalToday, $totalYesterday);

        // ── Active / Workforce ────────────────────────────────────────────
        $activeEmployees   = Employee::where('is_active', true)->whereNull('deleted_at')->count();
        $workforcePercent  = $totalToday > 0
            ? round(($activeEmployees / $totalToday) * 100)
            : 0;

        // ── Absent / On Leave (approved leaves covering today) ────────────
        $absentToday = EmployeLeaveRequest::where('status', 3)
            ->whereIn('action_type', [0, 2])
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->distinct('from_employee_id')
            ->count('from_employee_id');

        $absentYesterday = EmployeLeaveRequest::where('status', 3)
            ->whereIn('action_type', [0, 2])
            ->where('start_date', '<=', $yesterday)
            ->where('end_date', '>=', $yesterday)
            ->distinct('from_employee_id')
            ->count('from_employee_id');

        $absentDelta = $this->percentDelta($absentToday, $absentYesterday);

        // ── Present Today (total − on-leave fallback) ─────────────────────
        $rosterPresent = DB::table('shift_rosters')
            ->whereDate('roster_date', $today)
            ->where('status', 1)       // 1 = present
            ->whereNull('deleted_at')
            ->count();

        $rosterYesterdayPresent = DB::table('shift_rosters')
            ->whereDate('roster_date', $yesterday)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->count();

        // Use roster data if available, otherwise fall back to total − absent
        $presentToday     = $rosterPresent > 0
            ? $rosterPresent
            : max(0, $totalToday - $absentToday);

        $presentYesterday = $rosterYesterdayPresent > 0
            ? $rosterYesterdayPresent
            : max(0, $totalYesterday - $absentYesterday);

        $presentDelta = $this->percentDelta($presentToday, $presentYesterday);

        // ── Late Arrivals (from shift_rosters status=2 for late) ──────────
        $lateToday = DB::table('shift_rosters')
            ->whereDate('roster_date', $today)
            ->where('status', 2)       // 2 = late
            ->whereNull('deleted_at')
            ->count();

        $lateYesterday = DB::table('shift_rosters')
            ->whereDate('roster_date', $yesterday)
            ->where('status', 2)
            ->whereNull('deleted_at')
            ->count();

        $lateDelta = $this->percentDelta($lateToday, $lateYesterday);

        return [
            'totalEmployees'    => $totalToday,
            'totalDelta'        => $totalDelta,
            'presentToday'      => $presentToday,
            'presentDelta'      => $presentDelta,
            'absentOnLeave'     => $absentToday,
            'absentDelta'       => $absentDelta,
            'lateArrivals'      => $lateToday,
            'lateDelta'         => $lateDelta,
            'activeEmployees'   => $activeEmployees,
            'workforcePercent'  => $workforcePercent,
        ];
    }

    /**
     * Returns a signed percentage delta string like "+5%" or "-3%" or "0%"
     */
    private function percentDelta(int $current, int $previous): string
    {
        if ($previous === 0) {
            return $current > 0 ? '+100%' : '0%';
        }
        $delta = round((($current - $previous) / $previous) * 100);
        return ($delta >= 0 ? '+' : '') . $delta . '%';
    }
}
