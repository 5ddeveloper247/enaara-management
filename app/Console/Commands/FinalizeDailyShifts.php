<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ShiftRosterEntry;
use App\Models\LeaveType;
use App\Models\PublicHoliday;
use App\Models\EmployeeLeaveQuota;

class FinalizeDailyShifts extends Command
{
    protected $signature = 'shift-roster:finalize {--date= : The date to finalize (YYYY-MM-DD)}';
    protected $description = 'Finalize daily shifts and award compensatory leaves for holidays';

    public function handle()
    {
        $dateStr = $this->option('date') ?: now()->toDateString();
        $this->info("Finalizing shifts for {$dateStr}...");

        $entries = ShiftRosterEntry::where('roster_date', $dateStr)
            ->where('status', 'pending')
            ->with(['employee.department', 'employee.sbu'])
            ->get();

        if ($entries->isEmpty()) {
            $this->info("No pending shifts for today.");
            return;
        }

        $cplType = LeaveType::where('code', 'CPL')->first();
        if (!$cplType) {
            $this->error("Compensatory Leave (CPL) type not found!");
            return;
        }

        foreach ($entries as $entry) {
            $employee = $entry->employee;
            $this->info("Processing employee: " . $employee->full_name);
            
            $entry->status = 'used';
            $isHoliday = $this->isHolidayForEmployee($employee, $dateStr);
            $this->info("  Is Holiday/Blackout? " . ($isHoliday ? "YES" : "NO"));
            
            if ($isHoliday) {
                $this->awardCompensatoryLeave($employee, $cplType, $dateStr);
                $entry->is_compensatory_earned = true;
                $entry->status = 'holiday_worked';
                $this->info("  Awarded CPL.");
            }

            $entry->save();
        }

        $this->info("Finished finalizing " . $entries->count() . " shifts.");
    }

    protected function isHolidayForEmployee($employee, $date)
    {
        $holidays = PublicHoliday::whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->get();

        foreach ($holidays as $holiday) {
            // If all scopes are 'none', it's a global holiday
            if ($holiday->organization_scope === 'none' && 
                $holiday->department_scope === 'none' && 
                $holiday->sbu_scope === 'none') {
                return true;
            }

            // Check Organization scope
            if ($holiday->organization_scope === 'all') {
                return true;
            } elseif ($holiday->organization_scope === 'specific') {
                if ($holiday->organizations()->where('organizations.id', $employee->organization_id)->exists()) {
                    return true;
                }
            }

            // Check SBU scope
            if ($holiday->sbu_scope === 'all') {
                return true;
            } elseif ($holiday->sbu_scope === 'specific') {
                if ($holiday->sbus()->where('sbus.id', $employee->sbu_id)->exists()) {
                    return true;
                }
            }

            // Check Department scope
            if ($holiday->department_scope === 'all') {
                return true;
            } elseif ($holiday->department_scope === 'specific') {
                if ($holiday->departments()->where('departments.id', $employee->department_id)->exists()) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function awardCompensatoryLeave($employee, $leaveType, $date)
    {
        $year = date('Y', strtotime($date));
        $quota = EmployeeLeaveQuota::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
                'year' => $year
            ],
            [
                'department_id' => $employee->department_id,
                'quota' => 0,
                'used' => 0
            ]
        );

        $quota->increment('quota', 1.0);
    }
}
