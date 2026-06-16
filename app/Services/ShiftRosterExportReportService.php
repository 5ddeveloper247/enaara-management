<?php

namespace App\Services;

use App\Models\Department;
use App\Models\EmployeeWorkAssignment;
use App\Models\EmployeLeaveEntity;
use App\Models\PublicHoliday;
use App\Models\ShiftPlanner;
use App\Models\ShiftRosterApprovalRequest;
use App\Models\ShiftRosterApprovalSegment;
use App\Models\ShiftRosterEntry;
use App\Models\ThirdParty;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ShiftRosterExportReportService
{
    public function __construct(
        private readonly PublicHolidayResolver $publicHolidayResolver,
    ) {}

    public function buildTabularReport(array $options): array
    {
        $context = $this->enrichExportContextWithHolidays($this->resolveExportContext($options));
        $rows = $this->collectExportRows($context);

        $grouped = $rows->groupBy('department_key');
        $departments = $grouped
            ->map(function ($groupRows) {
                $first = $groupRows->first();

                return [
                    'name' => $first['department_name'],
                    'rows' => $groupRows
                        ->sortBy(['date_sort', 'employee'])
                        ->values()
                        ->map(fn (array $row) => $this->stripDepartmentMeta($row))
                        ->all(),
                ];
            })
            ->sortBy(fn (array $department) => $department['name'], SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();

        if (! $context['include_department_grouping']) {
            $departments = [
                [
                    'name' => null,
                    'rows' => $rows
                        ->sortBy(['date_sort', 'employee'])
                        ->values()
                        ->map(fn (array $row) => $this->stripDepartmentMeta($row))
                        ->all(),
                ],
            ];
        }

        $exportRows = $rows->map(fn (array $row) => $this->stripDepartmentMeta($row));

        return array_merge($this->buildReportShell($context), [
            'stats' => $this->buildStatsFromRows($exportRows),
            'departments' => $departments,
        ]);
    }

    public function buildCalendarReport(array $options): array
    {
        $context = $this->enrichExportContextWithHolidays($this->resolveExportContext($options));
        $days = $this->buildCalendarDays($context['date_range']);
        $dateKeys = array_column($days, 'date_key');

        $entries = $this->fetchExportEntries($context)
            ->filter(fn (ShiftRosterEntry $entry) => $this->isExportableRosterEntry($entry));

        $exportRows = $entries
            ->map(fn (ShiftRosterEntry $entry) => $this->mapEntryToExportRow(
                $entry,
                $context['include_shift_times'],
                $context['holidays'] ?? null
            ))
            ->merge($this->buildLeaveExportRows($context, $entries))
            ->values();

        $employeeCount = $entries
            ->groupBy(fn (ShiftRosterEntry $entry) => $this->assigneeExportKey($entry))
            ->count();

        if (! $context['include_department_grouping']) {
            $departments = [
                [
                    'name' => null,
                    'employees' => $this->buildCalendarEmployees($entries, $dateKeys, $context),
                ],
            ];
        } else {
            $departments = $entries
                ->groupBy(fn (ShiftRosterEntry $entry) => $this->resolveExportDepartmentName($entry))
                ->map(function (Collection $departmentEntries, string $departmentName) use ($dateKeys, $context) {
                    return [
                        'name' => strtoupper($departmentName),
                        'employees' => $this->buildCalendarEmployees($departmentEntries, $dateKeys, $context),
                    ];
                })
                ->sortBy(fn (array $department) => $department['name'], SORT_NATURAL | SORT_FLAG_CASE)
                ->values()
                ->all();
        }

        return array_merge($this->buildReportShell($context), [
            'organization_name' => $this->resolveExportSbuName($entries),
            'report_title' => 'Shift Planner — Shift Roster Export',
            'days' => $days,
            'departments' => $departments,
            'stats' => $this->buildStatsFromRows($exportRows, $employeeCount),
        ]);
    }

    public function buildPerEmployeeReport(array $options): array
    {
        $context = $this->enrichExportContextWithHolidays($this->resolveExportContext($options));
        $entries = $this->fetchExportEntries($context);

        $employees = $entries
            ->filter(fn (ShiftRosterEntry $entry) => $this->isExportableRosterEntry($entry))
            ->groupBy(fn (ShiftRosterEntry $entry) => $this->assigneeExportKey($entry))
            ->map(function (Collection $group) use ($context) {
                $first = $group->first();
                $name = $this->resolveExportEmployeeDisplayName($first);

                $shifts = $group
                    ->sortBy(fn (ShiftRosterEntry $entry) => $entry->roster_date->format('Y-m-d'))
                    ->values()
                    ->map(fn (ShiftRosterEntry $entry) => $this->mapEntryToShiftRow(
                        $entry,
                        $context['include_shift_times'],
                        $context['holidays'] ?? null
                    ))
                    ->all();

                $totalHours = (int) array_sum(array_column($shifts, 'hours'));

                return [
                    'initials' => $this->makeInitials($name),
                    'name' => $name,
                    'designation' => $this->resolveExportEmployeeDesignation($first),
                    'department' => $this->resolveExportDepartmentName($first),
                    'shift_count' => count($shifts),
                    'total_hours' => $totalHours,
                    'shifts' => $shifts,
                ];
            })
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();

        $flatRows = collect($employees)->flatMap(fn (array $employee) => $employee['shifts']);

        return array_merge($this->buildReportShell($context), [
            'stats' => $this->buildStatsFromRows($flatRows, count($employees)),
            'employees' => $employees,
        ]);
    }

    private function resolveExportContext(array $options): array
    {
        $employeeGroup = $options['employee_group'] ?? 'internal';
        $includeDeleted = (bool) ($options['include_deleted'] ?? false);
        $includeDepartmentGrouping = (bool) ($options['include_department_grouping'] ?? true);
        $includeShiftTimes = (bool) ($options['include_shift_times'] ?? true);

        if (($options['export_period_type'] ?? 'month') === 'date_range') {
            $period = Carbon::parse($options['start_date'])->startOfDay();
            $periodEnd = Carbon::parse($options['end_date'])->endOfDay();
            $dateRange = [$period->toDateString(), $periodEnd->toDateString()];
            $periodLabel = $period->format('d M Y') . ' – ' . $periodEnd->format('d M Y');
            $periodSlug = $period->format('Y-m-d') . '_to_' . $periodEnd->format('Y-m-d');
        } else {
            $year = (int) $options['year'];
            $month = (int) $options['month'];
            $period = Carbon::createFromDate($year, $month, 1)->startOfDay();
            $periodEnd = $period->copy()->endOfMonth();
            $dateRange = [$period->toDateString(), $periodEnd->toDateString()];
            $periodLabel = $period->format('F Y');
            $periodSlug = $period->format('F-Y');
        }

        $departmentId = ! empty($options['department_id']) ? (int) $options['department_id'] : null;

        return [
            'employee_group' => $employeeGroup,
            'department_id' => $departmentId,
            'department_label' => $this->resolveDepartmentFilterLabel($employeeGroup, $departmentId),
            'include_deleted' => $includeDeleted,
            'include_department_grouping' => $includeDepartmentGrouping,
            'include_shift_times' => $includeShiftTimes,
            'date_range' => $dateRange,
            'period_label' => $periodLabel,
            'period_slug' => $periodSlug,
        ];
    }

    private function resolveDepartmentFilterLabel(string $employeeGroup, ?int $departmentId): ?string
    {
        if (! $departmentId) {
            return null;
        }

        if ($employeeGroup === 'third_party') {
            return ThirdParty::query()->find($departmentId)?->third_party_name;
        }

        return Department::query()->find($departmentId)?->name;
    }

    private function fetchExportEntries(array $context): Collection
    {
        $entryRelations = [
            'shift',
            'approvalRequest',
            'approvalSegment',
            'employee.department',
            'employee.assignedDesignation',
            'employee.role',
            'employee.sbu',
            'outsourcedEmployee.contractorCompany',
            'outsourcedEmployee.sbu',
        ];

        $entriesQuery = ShiftRosterEntry::with($entryRelations)
            ->whereBetween('roster_date', $context['date_range'])
            ->orderBy('roster_date')
            ->orderBy('id');

        if ($context['employee_group'] === 'third_party') {
            $entriesQuery->whereNotNull('outsourced_employee_id');

            if (! empty($context['department_id'])) {
                $entriesQuery->whereHas('outsourcedEmployee', function ($employeeQuery) use ($context) {
                    $employeeQuery->where('contractor_company_id', $context['department_id']);
                });
            }
        } else {
            $entriesQuery->whereNotNull('employee_id');

            if (! empty($context['department_id'])) {
                $entriesQuery->whereHas('employee', function ($employeeQuery) use ($context) {
                    $employeeQuery->where('department_id', $context['department_id']);
                });
            }
        }

        $entries = $entriesQuery->get();

        if ($context['include_deleted']) {
            $trashedQuery = ShiftRosterEntry::onlyTrashed()
                ->with($entryRelations)
                ->whereBetween('roster_date', $context['date_range'])
                ->orderBy('roster_date')
                ->orderBy('id');

            if ($context['employee_group'] === 'third_party') {
                $trashedQuery->whereNotNull('outsourced_employee_id');

                if (! empty($context['department_id'])) {
                    $trashedQuery->whereHas('outsourcedEmployee', function ($employeeQuery) use ($context) {
                        $employeeQuery->where('contractor_company_id', $context['department_id']);
                    });
                }
            } else {
                $trashedQuery->whereNotNull('employee_id');

                if (! empty($context['department_id'])) {
                    $trashedQuery->whereHas('employee', function ($employeeQuery) use ($context) {
                        $employeeQuery->where('department_id', $context['department_id']);
                    });
                }
            }

            $entries = $entries->merge($trashedQuery->get())->unique('id')->values();
        }

        return $entries;
    }

    private function collectExportRows(array $context): Collection
    {
        return $this->fetchExportEntries($context)
            ->filter(fn (ShiftRosterEntry $entry) => $this->isExportableRosterEntry($entry))
            ->map(fn (ShiftRosterEntry $entry) => $this->mapEntryToExportRow(
                $entry,
                $context['include_shift_times'],
                $context['holidays'] ?? null
            ))
            ->values();
    }

    private function enrichExportContextWithHolidays(array $context): array
    {
        $context['holidays'] = $this->publicHolidayResolver->loadHolidaysForRange(
            Carbon::parse($context['date_range'][0])->startOfDay(),
            Carbon::parse($context['date_range'][1])->endOfDay()
        );

        return $context;
    }

    private function buildReportShell(array $context): array
    {
        return [
            'organization_name' => config('app.name', 'Enaara Systems'),
            'report_title' => 'Shift Planner — Monthly Report',
            'period_label' => $context['period_label'],
            'period_slug' => $context['period_slug'],
            'generated_at' => now()->format('d M Y, h:i A'),
            'employee_group_label' => $context['employee_group'] === 'third_party'
                ? 'Third-party employees'
                : 'Internal employees',
            'department_label' => $context['department_label'] ?? null,
            'include_shift_times' => $context['include_shift_times'],
            'include_department_grouping' => $context['include_department_grouping'],
            'include_deleted' => $context['include_deleted'],
            'signatures' => $this->buildSignatureBlock($context),
            'print_info' => $this->buildPrintInfo(),
            'duty_roster_header_title' => $this->buildDutyRosterHeaderTitle($context),
        ];
    }

    private function resolveExportSbuName(Collection $entries): string
    {
        $sbuNames = $entries
            ->map(function (ShiftRosterEntry $entry) {
                if ($entry->employee_id && $entry->employee?->sbu) {
                    return trim((string) $entry->employee->sbu->name);
                }

                if ($entry->outsourced_employee_id && $entry->outsourcedEmployee?->sbu) {
                    return trim((string) $entry->outsourcedEmployee->sbu->name);
                }

                return null;
            })
            ->filter()
            ->unique()
            ->values();

        if ($sbuNames->isEmpty()) {
            return config('app.name', 'Enaara Systems');
        }

        return $sbuNames->implode(', ');
    }

    private function buildDutyRosterHeaderTitle(array $context): string
    {
        $start = Carbon::parse($context['date_range'][0]);
        $end = Carbon::parse($context['date_range'][1]);

        $monthName = $start->format('F');
        $year = $start->format('Y');

        if ($start->format('Y-m') === $end->format('Y-m')) {
            $dateRange = $start->format('d') . '-' . $end->format('d');
        } elseif ($start->format('Y') === $end->format('Y')) {
            $dateRange = $start->format('d') . '-' . $end->format('F') . ' ' . $end->format('d');
        } else {
            $dateRange = $start->format('d M Y') . '-' . $end->format('d M Y');

            return 'Duty Roster-' . $dateRange;
        }

        return sprintf('Duty Roster-%s %s, %s', $monthName, $dateRange, $year);
    }

    private function buildPrintInfo(): array
    {
        $user = auth()->user();
        $now = now();

        return [
            'printed_by_name' => trim((string) ($user?->name ?? '')),
            'printed_at_date' => $now->format('d M Y'),
            'printed_at_time' => $now->format('h:i A'),
        ];
    }

    private function buildSignatureBlock(array $context): array
    {
        $empty = [
            'applied_by_name' => '',
            'applied_by_designation' => '',
            'approved_by_name' => '',
            'approved_by_designation' => '',
        ];

        $query = ShiftRosterApprovalRequest::query()
            ->with([
                'requestedByUser.employee.assignedDesignation',
                'requestedByUser.employee.role',
                'approverEmployee.assignedDesignation',
                'approverEmployee.role',
                'segments.approverEmployee.assignedDesignation',
                'segments.approverEmployee.role',
                'segments.department',
            ])
            ->where('approval_status', 'approved')
            ->whereDate('start_date', '<=', $context['date_range'][1])
            ->whereDate('end_date', '>=', $context['date_range'][0]);

        $departmentId = ! empty($context['department_id']) ? (int) $context['department_id'] : null;

        if ($context['employee_group'] === 'third_party') {
            $query->where(function ($groupQuery) {
                $groupQuery->whereNotNull('outsourced_employee_id')
                    ->orWhere('request_type', 'roster');
            });

            if ($departmentId) {
                $query->where(function ($filterQuery) use ($departmentId) {
                    $filterQuery->whereHas('outsourcedEmployee', function ($employeeQuery) use ($departmentId) {
                        $employeeQuery->where('contractor_company_id', $departmentId);
                    })->orWhere(function ($rosterQuery) use ($departmentId) {
                        $rosterQuery->where('request_type', 'roster')
                            ->whereHas('segments.entries.outsourcedEmployee', function ($employeeQuery) use ($departmentId) {
                                $employeeQuery->where('contractor_company_id', $departmentId);
                            });
                    });
                });
            }
        } else {
            $query->where(function ($groupQuery) {
                $groupQuery->whereNotNull('employee_id')
                    ->orWhere('request_type', 'roster');
            });

            if ($departmentId) {
                $query->where(function ($filterQuery) use ($departmentId) {
                    $filterQuery->whereHas('employee', function ($employeeQuery) use ($departmentId) {
                        $employeeQuery->where('department_id', $departmentId);
                    })->orWhere(function ($rosterQuery) use ($departmentId) {
                        $rosterQuery->where('request_type', 'roster')
                            ->whereHas('segments', function ($segmentQuery) use ($departmentId) {
                                $segmentQuery->where('department_id', $departmentId);
                            });
                    });
                });
            }
        }

        $request = $query->orderByDesc('approved_at')->first();

        if ($request === null) {
            return $empty;
        }

        $requester = $request->requestedByUser;
        $requesterEmployee = $requester?->employee;
        $requesterDesignation = trim((string) (
            $requesterEmployee?->assignedDesignation?->name
            ?? $requesterEmployee?->designation
            ?? $requesterEmployee?->role?->name
            ?? ''
        ));

        $approver = $this->resolveSignatureApprover($request, $departmentId);
        $approverDesignation = trim((string) (
            $approver?->assignedDesignation?->name
            ?? $approver?->designation
            ?? $approver?->role?->name
            ?? ''
        ));

        return [
            'applied_by_name' => trim((string) ($requester?->name ?? '')),
            'applied_by_designation' => $requesterDesignation,
            'approved_by_name' => trim((string) ($approver?->full_name ?? $approver?->first_name ?? '')),
            'approved_by_designation' => $approverDesignation,
        ];
    }

    private function resolveSignatureApprover(
        ShiftRosterApprovalRequest $request,
        ?int $departmentId
    ): ?\App\Models\Employee {
        if ($request->request_type === 'roster') {
            $segments = $request->segments
                ->where('approval_status', 'approved')
                ->when($departmentId, fn (Collection $segments) => $segments->where('department_id', $departmentId));

            /** @var ShiftRosterApprovalSegment|null $segment */
            $segment = $segments
                ->sortByDesc(fn (ShiftRosterApprovalSegment $segment) => $segment->approved_at?->timestamp ?? 0)
                ->first();

            return $segment?->approverEmployee;
        }

        return $request->approverEmployee;
    }

    private function buildStatsFromRows(Collection $rows, ?int $employeeCount = null): array
    {
        $workingRows = $rows->reject(fn (array $row) => in_array($row['shift_type'] ?? '', ['off', 'holiday', 'leave', 'half_leave'], true));
        $employeeNames = $rows->pluck('employee')->filter()->unique();

        return [
            'total_employees' => $employeeCount ?? $employeeNames->count(),
            'shifts_scheduled' => $workingRows->count(),
            'morning' => $workingRows->where('shift_type', 'morning')->count(),
            'evening' => $workingRows->where('shift_type', 'evening')->count(),
            'night' => $workingRows->where('shift_type', 'night')->count(),
            'off_days' => $rows->where('shift_type', 'off')->count(),
            'public_holidays' => $rows->where('shift_type', 'holiday')->count(),
            'leaves' => $rows->whereIn('shift_type', ['leave', 'half_leave'])->count(),
            'total_hours' => (int) $workingRows->sum('hours'),
        ];
    }

    private function assigneeExportKey(ShiftRosterEntry $entry): string
    {
        if ($entry->employee_id) {
            return 'employee:' . $entry->employee_id;
        }

        return 'outsourced:' . $entry->outsourced_employee_id;
    }

    private function makeInitials(string $fullName): string
    {
        $parts = preg_split('/\s+/u', trim($fullName), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($parts) >= 2) {
            return strtoupper(
                mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts) - 1], 0, 1)
            );
        }

        return strtoupper(mb_substr(trim($fullName), 0, 2));
    }

    private function isExportableRosterEntry(ShiftRosterEntry $entry): bool
    {
        $status = strtolower((string) $entry->status);

        if ($status === 'cancelled') {
            return false;
        }

        if ($entry->shift_roster_approval_request_id && ! $this->isEntryGmApproved($entry)) {
            return false;
        }

        return true;
    }

    private function isEntryGmApproved(ShiftRosterEntry $entry): bool
    {
        if (is_array($entry->published_snapshot) && $entry->published_snapshot !== []) {
            return true;
        }

        if ($entry->approvalRequest?->request_type === 'roster') {
            return $entry->approvalSegment?->approval_status === 'approved';
        }

        return $entry->approvalRequest?->approval_status === 'approved';
    }

    private function resolvePublishedSnapshot(ShiftRosterEntry $entry): ?array
    {
        $snapshot = $entry->published_snapshot;

        return is_array($snapshot) && $snapshot !== [] ? $snapshot : null;
    }

    private function mapEntryToExportRow(
        ShiftRosterEntry $entry,
        bool $includeShiftTimes,
        ?Collection $holidays = null
    ): array {
        $snapshot = $this->resolvePublishedSnapshot($entry);
        $status = (string) ($snapshot['status'] ?? $entry->status);
        $isOffDay = strtolower($status) === 'off';
        $shiftPlannerId = $snapshot['shift_planner_id'] ?? $entry->shift_planner_id;
        $shift = $entry->shift;
        if ($snapshot && $shiftPlannerId && (int) $shiftPlannerId !== (int) $entry->shift_planner_id) {
            $shift = ShiftPlanner::query()->find($shiftPlannerId);
        }
        $shiftName = strtolower($shift?->name ?? '');
        $shiftCode = strtolower($shift?->code ?? '');
        $isCustomTime = (bool) ($snapshot['is_custom_time'] ?? $entry->is_custom_time);
        $startRaw = $isOffDay ? null : ($snapshot['start_time'] ?? $entry->start_time ?? $shift?->start_time);
        $endRaw = $isOffDay ? null : ($snapshot['end_time'] ?? $entry->end_time ?? $shift?->end_time);
        $shiftType = $this->resolveShiftType(
            $startRaw ? $this->formatShiftTime($startRaw) : null,
            $isOffDay,
            $isCustomTime,
            $shiftName,
            $shiftCode
        );

        $employeeName = $this->resolveExportEmployeeDisplayName($entry);

        $departmentName = $this->resolveExportDepartmentName($entry);
        $rosterDate = $entry->roster_date->copy();

        if ($isOffDay) {
            $publicHoliday = $this->resolvePublicHolidayForEntry(
                $entry,
                $rosterDate->toDateString(),
                $holidays
            );

            if ($publicHoliday !== null) {
                $shiftType = 'holiday';
                $shiftLabel = 'Public Holiday';
            } else {
                $shiftType = 'off';
                $shiftLabel = 'Off Day';
            }
        } else {
            $shiftLabel = $shiftType === 'general'
                ? ($entry->shift?->name ?? 'Custom')
                : ucfirst($shiftType);
        }

        return [
            'employee' => $employeeName,
            'designation' => $this->resolveExportEmployeeDesignation($entry),
            'date' => $rosterDate->format('d M'),
            'date_sort' => $rosterDate->format('Y-m-d'),
            'day' => $rosterDate->format('D'),
            'start_time' => $includeShiftTimes ? $this->formatDisplayTime($startRaw) : null,
            'end_time' => $includeShiftTimes ? $this->formatDisplayTime($endRaw) : null,
            'shift_type' => $shiftType,
            'shift_label' => $shiftLabel,
            'hours' => $this->calculateEntryHours($startRaw, $endRaw),
            'is_deleted' => $entry->trashed(),
            'department_key' => $departmentName,
            'department_name' => strtoupper($departmentName),
        ];
    }

    private function resolvePublicHolidayForEntry(
        ShiftRosterEntry $entry,
        string $dateString,
        ?Collection $holidays
    ): ?PublicHoliday {
        if ($holidays === null || $holidays->isEmpty()) {
            return null;
        }

        if ($entry->employee_id && $entry->employee) {
            return $this->publicHolidayResolver->resolveForAssigneeOnDate(
                $holidays,
                $entry->employee->organization_id ? (int) $entry->employee->organization_id : null,
                $entry->employee->department_id ? (int) $entry->employee->department_id : null,
                $entry->employee->sbu_id ? (int) $entry->employee->sbu_id : null,
                $dateString
            );
        }

        if ($entry->outsourced_employee_id && $entry->outsourcedEmployee) {
            return $this->publicHolidayResolver->resolveForAssigneeOnDate(
                $holidays,
                $entry->outsourcedEmployee->organization_id ? (int) $entry->outsourcedEmployee->organization_id : null,
                null,
                $entry->outsourcedEmployee->sbu_id ? (int) $entry->outsourcedEmployee->sbu_id : null,
                $dateString
            );
        }

        return null;
    }

    private function mapEntryToShiftRow(ShiftRosterEntry $entry, bool $includeShiftTimes, ?Collection $holidays = null): array
    {
        $row = $this->mapEntryToExportRow($entry, $includeShiftTimes, $holidays);
        unset($row['employee'], $row['department_key'], $row['department_name']);

        return $row;
    }

    private function buildCalendarDays(array $dateRange): array
    {
        $start = Carbon::parse($dateRange[0])->startOfDay();
        $end = Carbon::parse($dateRange[1])->startOfDay();
        $days = [];

        for ($date = $start->copy(); $date->lessThanOrEqualTo($end); $date->addDay()) {
            $days[] = [
                'day' => $date->format('d'),
                'dow' => strtoupper($date->format('D')),
                'date_key' => $date->format('Y-m-d'),
            ];
        }

        return $days;
    }

    private function buildCalendarEmployees(Collection $entries, array $dateKeys, array $context): array
    {
        $employeeIds = $entries
            ->pluck('employee_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $leavesByAssigneeDate = $this->loadApprovedLeavesByAssigneeDate($context, $employeeIds);
        $workAssignmentsByAssigneeDate = $this->loadWorkAssignmentsByAssigneeDate($context, $employeeIds);

        return $entries
            ->groupBy(fn (ShiftRosterEntry $entry) => $this->assigneeExportKey($entry))
            ->map(function (Collection $group) use ($dateKeys, $context, $leavesByAssigneeDate, $workAssignmentsByAssigneeDate) {
                $first = $group->first();
                $name = $this->resolveExportEmployeeDisplayName($first);
                $assigneeKey = $this->assigneeExportKey($first);

                $cellsByDate = [];

                foreach ($group->sortBy(fn (ShiftRosterEntry $entry) => $entry->roster_date->format('Y-m-d') . '-' . $entry->id) as $entry) {
                    $cellsByDate[$entry->roster_date->format('Y-m-d')] = $this->mapEntryToCalendarCell(
                        $entry,
                        $context['include_shift_times'],
                        $context['holidays'] ?? null
                    );
                }

                $cells = [];

                foreach ($dateKeys as $dateKey) {
                    $cell = $cellsByDate[$dateKey] ?? null;
                    $leaveEntity = $leavesByAssigneeDate[$assigneeKey][$dateKey] ?? null;

                    if ($leaveEntity !== null && ! $this->isWorkingExportCell($cell)) {
                        $cell = $this->mapLeaveToCalendarCell($leaveEntity);
                    }

                    $workAssignment = $workAssignmentsByAssigneeDate[$assigneeKey][$dateKey] ?? null;
                    if ($workAssignment !== null) {
                        $cell = $this->mapWorkAssignmentToCalendarCell($workAssignment);
                    }

                    $cells[] = $cell;
                }

                return [
                    'name' => $name,
                    'designation' => $this->resolveExportEmployeeDesignation($first),
                    'cells' => $cells,
                ];
            })
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    /**
     * @return array<string, array<string, EmployeLeaveEntity>>
     */
    private function loadApprovedLeavesByAssigneeDate(array $context, array $employeeIds): array
    {
        if ($employeeIds === []) {
            return [];
        }

        $leaves = EmployeLeaveEntity::query()
            ->with('leaveRequest.leaveType')
            ->whereIn('employee_id', $employeeIds)
            ->whereIn('status', [0, 1])
            ->whereBetween('leave_date', $context['date_range'])
            ->get();

        $map = [];

        foreach ($leaves as $entity) {
            $assigneeKey = 'employee:' . $entity->employee_id;
            $map[$assigneeKey][$entity->leave_date->toDateString()] = $entity;
        }

        return $map;
    }

    /**
     * @return array<string, array<string, EmployeeWorkAssignment>>
     */
    private function loadWorkAssignmentsByAssigneeDate(array $context, array $employeeIds): array
    {
        if ($employeeIds === []) {
            return [];
        }

        $assignments = EmployeeWorkAssignment::query()
            ->whereIn('employee_id', $employeeIds)
            ->whereIn('work_type', [
                EmployeeWorkAssignment::TYPE_ABSENT,
                EmployeeWorkAssignment::TYPE_WORK_FROM_HOME,
            ])
            ->whereBetween('assignment_date', $context['date_range'])
            ->get();

        $map = [];

        foreach ($assignments as $assignment) {
            $assigneeKey = 'employee:'.$assignment->employee_id;
            $map[$assigneeKey][$assignment->assignment_date->toDateString()] = $assignment;
        }

        return $map;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function buildLeaveExportRows(array $context, Collection $entries): Collection
    {
        $employeeIds = $entries
            ->pluck('employee_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($employeeIds === []) {
            return collect();
        }

        $workingCellsByAssigneeDate = [];

        foreach ($entries as $entry) {
            if (! $this->isExportableRosterEntry($entry)) {
                continue;
            }

            $assigneeKey = $this->assigneeExportKey($entry);
            $dateKey = $entry->roster_date->toDateString();
            $cell = $this->mapEntryToCalendarCell(
                $entry,
                $context['include_shift_times'],
                $context['holidays'] ?? null
            );

            if ($this->isWorkingExportCell($cell)) {
                $workingCellsByAssigneeDate[$assigneeKey][$dateKey] = true;
            }
        }

        $leavesByAssigneeDate = $this->loadApprovedLeavesByAssigneeDate($context, $employeeIds);
        $rows = collect();

        foreach ($leavesByAssigneeDate as $assigneeKey => $dates) {
            foreach ($dates as $dateKey => $entity) {
                if (isset($workingCellsByAssigneeDate[$assigneeKey][$dateKey])) {
                    continue;
                }

                $rows->push($this->mapLeaveToExportRow($entity));
            }
        }

        return $rows;
    }

    private function mapLeaveToExportRow(EmployeLeaveEntity $entity): array
    {
        $isHalfDayLeave = (float) $entity->duration < 1.0
            || (bool) ($entity->leaveRequest?->is_half_day ?? false);
        $leaveName = $entity->leaveRequest?->leaveType?->name ?? 'Leave';
        $shiftType = $isHalfDayLeave ? 'half_leave' : 'leave';

        return [
            'employee' => null,
            'designation' => null,
            'date' => $entity->leave_date->format('d M'),
            'date_sort' => $entity->leave_date->format('Y-m-d'),
            'day' => $entity->leave_date->format('D'),
            'start_time' => null,
            'end_time' => null,
            'shift_type' => $shiftType,
            'shift_label' => $isHalfDayLeave ? 'Short Leave' : $leaveName,
            'hours' => 0,
            'is_deleted' => false,
        ];
    }

    private function mapLeaveToCalendarCell(EmployeLeaveEntity $entity): array
    {
        $row = $this->mapLeaveToExportRow($entity);

        return [
            'shift_type' => $row['shift_type'],
            'shift_label' => $row['shift_label'],
            'shift_short' => $this->shiftTypeShortLabel($row['shift_type'], $row['shift_label']),
            'time_start' => null,
            'time_end' => null,
            'time_start_short' => null,
            'time_end_short' => null,
            'hours' => 0,
            'is_deleted' => false,
        ];
    }

    private function mapWorkAssignmentToCalendarCell(EmployeeWorkAssignment $assignment): array
    {
        $isAbsent = $assignment->work_type === EmployeeWorkAssignment::TYPE_ABSENT;
        $shiftType = $isAbsent ? 'absent' : 'work_from_home';
        $label = $isAbsent ? 'Absent' : 'Work from home';

        return [
            'shift_type' => $shiftType,
            'shift_label' => $label,
            'shift_short' => $this->shiftTypeShortLabel($shiftType, $label),
            'time_start' => null,
            'time_end' => null,
            'time_start_short' => null,
            'time_end_short' => null,
            'hours' => 0,
            'is_deleted' => false,
        ];
    }

    private function isWorkingExportCell(?array $cell): bool
    {
        if (! is_array($cell)) {
            return false;
        }

        return in_array($cell['shift_type'] ?? '', ['morning', 'evening', 'night', 'general'], true);
    }

    private function mapEntryToCalendarCell(
        ShiftRosterEntry $entry,
        bool $includeShiftTimes,
        ?Collection $holidays = null
    ): array {
        $row = $this->mapEntryToExportRow($entry, $includeShiftTimes, $holidays);

        return [
            'shift_type' => $row['shift_type'],
            'shift_label' => $row['shift_label'],
            'shift_short' => $this->shiftTypeShortLabel($row['shift_type'], $row['shift_label']),
            'time_start' => $row['start_time'],
            'time_end' => $row['end_time'],
            'time_start_short' => $includeShiftTimes
                ? $this->formatUltraCompactDisplayTime($row['start_time'])
                : null,
            'time_end_short' => $includeShiftTimes
                ? $this->formatUltraCompactDisplayTime($row['end_time'])
                : null,
            'hours' => $row['hours'],
            'is_deleted' => $row['is_deleted'],
        ];
    }

    private function resolveExportEmployeeDisplayName(ShiftRosterEntry $entry): string
    {
        if ($entry->employee_id && $entry->employee) {
            return $entry->employee->rosterDisplayName();
        }

        if ($entry->outsourced_employee_id && $entry->outsourcedEmployee) {
            return $this->firstTokenFromName($entry->outsourcedEmployee->full_name);
        }

        return 'Unknown';
    }

    private function resolveExportEmployeeDesignation(ShiftRosterEntry $entry): ?string
    {
        if (! $entry->employee_id || ! $entry->employee) {
            return null;
        }

        $designation = trim((string) (
            $entry->employee->assignedDesignation?->name
            ?? $entry->employee->designation
            ?? $entry->employee->role?->name
            ?? ''
        ));

        return $designation !== '' ? $designation : null;
    }

    private function firstTokenFromName(?string $name): string
    {
        $parts = preg_split('/\s+/u', trim((string) $name), 2, PREG_SPLIT_NO_EMPTY) ?: [];

        return $parts[0] ?? 'Unknown';
    }

    private function shiftTypeShortLabel(string $shiftType, string $fullLabel): string
    {
        return match ($shiftType) {
            'morning' => 'M',
            'evening' => 'E',
            'night' => 'N',
            'general' => 'G',
            'off' => 'OFF',
            'holiday' => 'PH',
            'leave' => 'LEAVE',
            'half_leave' => 'SL',
            'absent' => 'A',
            'work_from_home' => 'WFH',
            default => mb_strtoupper(mb_substr(trim($fullLabel), 0, 1)) ?: '•',
        };
    }

    private function formatUltraCompactDisplayTime(?string $displayTime): ?string
    {
        if (! $displayTime) {
            return null;
        }

        try {
            return Carbon::parse($displayTime)->format('H:i');
        } catch (\Throwable) {
            return $displayTime;
        }
    }

    private function resolveExportDepartmentName(ShiftRosterEntry $entry): string
    {
        if ($entry->employee_id) {
            return $entry->employee?->department?->name ?? 'Unassigned';
        }

        return $entry->outsourcedEmployee?->contractorCompany?->third_party_name ?? 'Unassigned';
    }

    private function stripDepartmentMeta(array $row): array
    {
        unset($row['department_key'], $row['department_name']);

        return $row;
    }

    private function formatShiftTime($value): string
    {
        if (! $value) {
            return '00:00';
        }

        return Carbon::parse($value)->format('H:i');
    }

    private function formatDisplayTime($value): ?string
    {
        if (! $value) {
            return null;
        }

        return Carbon::parse($value)->format('h:i A');
    }

    private function calculateEntryHours($startTime, $endTime): int
    {
        if (! $startTime || ! $endTime) {
            return 0;
        }

        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        $minutes = $start->diffInMinutes($end);

        return max(1, (int) round($minutes / 60));
    }

    private function resolveShiftType(
        ?string $startTime,
        bool $isOffDay,
        bool $isCustomTime,
        string $shiftName = '',
        string $shiftCode = ''
    ): string {
        if ($isOffDay) {
            return 'off';
        }

        $shiftCode = strtolower($shiftCode);
        $shiftName = strtolower($shiftName);

        if ($shiftCode === 'gen' || ($shiftName !== '' && str_contains($shiftName, 'general'))) {
            return 'general';
        }

        if ($shiftName !== '') {
            if (str_contains($shiftName, 'morning')) {
                return 'morning';
            }
            if (str_contains($shiftName, 'evening')) {
                return 'evening';
            }
            if (str_contains($shiftName, 'night')) {
                return 'night';
            }
        }

        if ($startTime) {
            $hour = (int) Carbon::parse($startTime)->format('H');
            if ($hour >= 4 && $hour < 12) {
                return 'morning';
            }
            if ($hour >= 12 && $hour < 18) {
                return 'evening';
            }
            if ($hour >= 18 || $hour < 4) {
                return 'night';
            }
        }

        return 'general';
    }
}
