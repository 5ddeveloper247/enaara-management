<?php

use App\Http\Controllers\AttendanceModesController;
use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BalanceTrackerController;
use App\Http\Controllers\BiometricDeviceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OutsourcedEmployeeController;
use App\Http\Controllers\EmployeeTypeController;
use App\Http\Controllers\GeofenceController;
use App\Http\Controllers\LeaveCalendarController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ModuleCategoryController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\MonthlySummaryController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PasswordFirstChangeController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RoleLevelController;
use App\Http\Controllers\SbuController;
use App\Http\Controllers\SbuFloorsController;
use App\Http\Controllers\ShiftPlannerController;
use App\Http\Controllers\ShiftRosterController;
use App\Http\Controllers\ShiftRosterApprovalController;
use App\Http\Controllers\ShiftRosterExcelExportController;
use App\Http\Controllers\ShiftTypesController;
use App\Http\Controllers\ThirdPartyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\WorkTypeController;
use App\Http\Middleware\EnsurePasswordIsNotTemporary;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::get('/', function () {
    return redirect()->route('login');
});
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/first-password', [PasswordFirstChangeController::class, 'show'])->name('password.first-change');
    Route::post('/first-password', [PasswordFirstChangeController::class, 'update'])->name('password.first-change.update');
});

Route::middleware(['auth', EnsurePasswordIsNotTemporary::class])->prefix('admin')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard.index');
    Route::get('/dashboard/attendance-chart', [DashboardController::class, 'attendanceChart'])->name('admin.dashboard.attendance-chart');
    Route::get('/dashboard/department-distribution', [DashboardController::class, 'departmentDistribution'])->name('admin.dashboard.department-distribution');
    Route::get('/dashboard/pending-approvals', [DashboardController::class, 'pendingApprovals'])->name('admin.dashboard.pending-approvals');
    Route::get('/dashboard/pending-roster-approvals', [DashboardController::class, 'pendingRosterApprovals'])->name('admin.dashboard.pending-roster-approvals');
    Route::get('/dashboard/upcoming-holidays', [DashboardController::class, 'upcomingHolidays'])->name('admin.dashboard.upcoming-holidays');
    Route::get('/dashboard/who-is-out', [DashboardController::class, 'whoIsOutToday'])->name('admin.dashboard.who-is-out');

    // Organization Management Routes
    Route::get('/organization', [OrganizationController::class, 'index'])->name('admin.organization.index');
    Route::get('/organization/add', [OrganizationController::class, 'create'])->name('admin.organization.create');
    Route::post('/organization/add', [OrganizationController::class, 'store'])->name('admin.organization.store');
    Route::get('/organization/edit/{id}', [OrganizationController::class, 'edit'])->name('admin.organization.edit');
    Route::post('/organization/edit/{id}', [OrganizationController::class, 'update'])->name('admin.organization.update');
    Route::delete('/organization/delete/{id}', [OrganizationController::class, 'destroy'])->name('admin.organization.destroy');
    // Sbu ROutes
    Route::get('/sbu', [SbuController::class, 'index'])->name('admin.sbu.index');
    Route::get('/sbu/add', [SbuController::class, 'create'])->name('admin.sbu.create');
    Route::post('/sbu/add', [SbuController::class, 'store'])->name('admin.sbu.store');
    Route::get('/sbu/edit/{id}', [SbuController::class, 'edit'])->name('admin.sbu.edit');
    Route::post('/sbu/edit/{id}', [SbuController::class, 'update'])->name('admin.sbu.update');
    Route::delete('/sbu/delete/{id}', [SbuController::class, 'destroy'])->name('admin.sbu.destroy');
    Route::get('/sbu/{id}/show', [SbuController::class, 'show'])->name('admin.sbu.show');
    Route::get('/third-party', [ThirdPartyController::class, 'index'])->name('admin.third-party.index');
    Route::get('/third-party/add', [ThirdPartyController::class, 'create'])->name('admin.third-party.create');
    Route::post('/third-party/add', [ThirdPartyController::class, 'store'])->name('admin.third-party.store');
    Route::get('/third-party/edit/{id}', [ThirdPartyController::class, 'edit'])->name('admin.third-party.edit');
    Route::post('/third-party/edit/{id}', [ThirdPartyController::class, 'update'])->name('admin.third-party.update');
    Route::delete('/third-party/delete/{id}', [ThirdPartyController::class, 'destroy'])->name('admin.third-party.destroy');
    Route::get('/third-party/{id}/show', [ThirdPartyController::class, 'show'])->name('admin.third-party.show');
    Route::get('/biometric-device', [BiometricDeviceController::class, 'index'])->name('admin.biometric-device.index');
    Route::get('/biometric-device/add', [BiometricDeviceController::class, 'create'])->name('admin.biometric-device.create');
    Route::post('/biometric-device/add', [BiometricDeviceController::class, 'store'])->name('admin.biometric-device.store');
    Route::get('/biometric-device/edit/{id}', [BiometricDeviceController::class, 'edit'])->name('admin.biometric-device.edit');
    Route::post('/biometric-device/edit/{id}', [BiometricDeviceController::class, 'update'])->name('admin.biometric-device.update');
    Route::delete('/biometric-device/delete/{id}', [BiometricDeviceController::class, 'destroy'])->name('admin.biometric-device.destroy');
    Route::get('/biometric-device/{id}/show', [BiometricDeviceController::class, 'show'])->name('admin.biometric-device.show');
    // Sbu Floor Routes
    Route::get('/sbu-floor', [SbuFloorsController::class, 'index'])->name('admin.sbu.floor.index');
    Route::get('/sbu-floor/add', [SbuFloorsController::class, 'create'])->name('admin.sbu.floor.create');
    Route::post('/sbu-floor/add', [SbuFloorsController::class, 'store'])->name('admin.sbu.floor.store');
    Route::get('/sbu-floor/edit/{id}', [SbuFloorsController::class, 'edit'])->name('admin.sbu.floor.edit');
    Route::post('/sbu-floor/edit/{id}', [SbuFloorsController::class, 'update'])->name('admin.sbu.floor.update');
    Route::delete('/sbu-floor/delete/{id}', [SbuFloorsController::class, 'destroy'])->name('admin.sbu.floor.destroy');
    Route::get('/sbu-floor/{id}/detail-json', [SbuFloorsController::class, 'detailJson'])->name('admin.sbu.floor.detail-json');
    Route::get('/sbu-floor/{id}/show', [SbuFloorsController::class, 'show'])->name('admin.sbu.floor.show');
    //
    Route::get('/employee-type', [EmployeeTypeController::class, 'index'])->name('admin.employee.type.index');
    Route::patch('/employee-type/{id}/status', [EmployeeTypeController::class, 'updateStatus'])->name('admin.employee.type.update.status');
    Route::delete('/employee-type/{id}/delete', [EmployeeTypeController::class, 'destroy'])->name('admin.employee.type.destroy');

    Route::get('/work-type', [WorkTypeController::class, 'index'])->name('admin.work.type.index');
    Route::patch('/work-type/{id}/status', [WorkTypeController::class, 'updateStatus'])->name('admin.work.type.update.status');
    Route::delete('/work-type/{id}/delete', [WorkTypeController::class, 'destroy'])->name('admin.work.type.destroy');

    Route::get('/attendance-modes', [AttendanceModesController::class, 'index'])->name('admin.attendance.modes.index');
    Route::patch('/attendance-modes/{id}/status', [AttendanceModesController::class, 'updateStatus'])->name('admin.attendance.modes.update.status');
    Route::delete('/attendance-modes/{id}/delete', [AttendanceModesController::class, 'destroy'])->name('admin.attendance.modes.destroy');

    Route::get('/shift-type', [ShiftTypesController::class, 'index'])->name('admin.shift.type.index');
    Route::patch('/shift-type/{id}/status', [ShiftTypesController::class, 'updateStatus'])->name('admin.shift.type.update.status');
    Route::delete('/shift-type/{id}/delete', [ShiftTypesController::class, 'destroy'])->name('admin.shift.type.destroy');

    Route::get('/department', [DepartmentController::class, 'index'])->name('admin.department.index');
    Route::get('/department/add', [DepartmentController::class, 'create'])->name('admin.department.add');
    Route::post('/department/add', [DepartmentController::class, 'store'])->name('admin.department.store');
    Route::get('/department/edit/{id}', [DepartmentController::class, 'edit'])->name('admin.department.edit');
    Route::post('/department/edit/{id}', [DepartmentController::class, 'update'])->name('admin.department.update');
    Route::delete('/department/{id}', [DepartmentController::class, 'destroy'])->name('admin.department.destroy');

    // Role Level Routes
    Route::get('/role-levels', [RoleLevelController::class, 'index'])->name('admin.role-levels.index');
    Route::post('/role-levels/add', [RoleLevelController::class, 'store'])->name('admin.role-levels.store');
    Route::get('/role-levels/edit/{id}', [RoleLevelController::class, 'edit'])->name('admin.role-levels.edit');
    Route::post('/role-levels/edit/{id}', [RoleLevelController::class, 'update'])->name('admin.role-levels.update');
    Route::delete('/role-levels/delete/{id}', [RoleLevelController::class, 'destroy'])->name('admin.role-levels.destroy');

    // Designation Routes
    Route::get('/designations', [DesignationController::class, 'index'])->name('admin.designations.index');
    Route::post('/designations/add', [DesignationController::class, 'store'])->name('admin.designations.store');
    Route::get('/designations/edit/{id}', [DesignationController::class, 'edit'])->name('admin.designations.edit');
    Route::post('/designations/edit/{id}', [DesignationController::class, 'update'])->name('admin.designations.update');
    Route::delete('/designations/delete/{id}', [DesignationController::class, 'destroy'])->name('admin.designations.destroy');

    Route::get('/leave-type', [LeaveTypeController::class, 'index'])->name('admin.leave.type.index');
    Route::get('/leave-type/entitlement-reference', [LeaveTypeController::class, 'entitlementReference'])->name('admin.leave.type.entitlement-reference');
    Route::get('/leave-type/add', [LeaveTypeController::class, 'create'])->name('admin.leave.type.add');
    Route::post('/leave-type/add', [LeaveTypeController::class, 'store'])->name('admin.leave.type.store');
    Route::get('/leave-type/edit/{id}', [LeaveTypeController::class, 'edit'])->name('admin.leave.type.edit');
    Route::post('/leave-type/edit/{id}', [LeaveTypeController::class, 'update'])->name('admin.leave.type.update');
    Route::delete('/leave-type/{id}', [LeaveTypeController::class, 'destroy'])->name('admin.leave.type.destroy');

    // Leave Requests
    Route::get('/leave-request', [LeaveRequestController::class, 'index'])->name('admin.leave.request.index');
    Route::get('/leave-request/add', [LeaveRequestController::class, 'create'])->name('admin.leave.request.add');
    Route::post('/leave-request/add', [LeaveRequestController::class, 'store'])->name('admin.leave.request.store');
    Route::get('/leave-request/leave-types', [LeaveRequestController::class, 'leaveTypesForEmployee'])->name('admin.leave.request.leave-types');
    Route::get('/leave-request/approval-workflow', [LeaveRequestController::class, 'approvalWorkflowPreview'])->name('admin.leave.request.approval-workflow');
    Route::get('/leave-request/employee-addresses', [LeaveRequestController::class, 'employeeAddresses'])->name('admin.leave.request.employee-addresses');
    Route::get('/leave-request/calculate-duration', [LeaveRequestController::class, 'calculateDuration'])->name('admin.leave.request.calculate-duration');
    Route::patch('/leave-request/{id}/status', [LeaveRequestController::class, 'updateStatus'])->name('admin.leave.request.status');
    // role categories

    Route::get('/module-categories', [ModuleCategoryController::class, 'index'])->name('admin.module.category.index');
    Route::get('/module-categories/add', [ModuleCategoryController::class, 'create'])->name('admin.module.category.add');
    Route::post('/module-categories/add', [ModuleCategoryController::class, 'store'])->name('admin.module.category.store');
    Route::get('/module-categories/show/{id}', [ModuleCategoryController::class, 'show'])->name('admin.module.category.show');
    Route::get('/module-categories/edit/{id}', [ModuleCategoryController::class, 'edit'])->name('admin.module.category.edit');
    Route::post('/module-categories/edit/{id}', [ModuleCategoryController::class, 'update'])->name('admin.module.category.update');
    Route::patch('/module-categories/{id}/status', [ModuleCategoryController::class, 'updateStatus'])->name('admin.module.category.update.status');
    Route::delete('/module-categories/{id}/delete', [ModuleCategoryController::class, 'destroy'])->name('admin.module.category.destroy');
    Route::get('/module-categories/search', [ModuleCategoryController::class, 'searchModuleCategory'])->name('admin.module.category.search');

    Route::get('/module', [ModuleController::class, 'index'])->name('admin.module.index');
    Route::get('/module/add', [ModuleController::class, 'create'])->name('admin.module.add');
    Route::post('/module/add', [ModuleController::class, 'store'])->name('admin.module.store');
    Route::get('/module/show/{id}', [ModuleController::class, 'show'])->name('admin.module.show');
    Route::get('/module/edit/{id}', [ModuleController::class, 'edit'])->name('admin.module.edit');
    Route::post('/module/edit/{id}', [ModuleController::class, 'update'])->name('admin.module.update');
    Route::patch('/module/{id}/status', [ModuleController::class, 'updateStatus'])->name('admin.module.update.status');
    Route::delete('/module/{id}/delete', [ModuleController::class, 'destroy'])->name('admin.module.destroy');
    Route::get('/module/search', [ModuleController::class, 'searchModule'])->name('admin.module.search');

    Route::get('/role', [RoleController::class, 'index'])->name('admin.role.index');
    Route::get('/role/add', [RoleController::class, 'create'])->name('admin.role.add');
    Route::post('/role/add', [RoleController::class, 'store'])->name('admin.role.store');
    Route::get('/role/show/{id}', [RoleController::class, 'show'])->name('admin.role.show');
    Route::get('/role/edit/{id}', [RoleController::class, 'edit'])->name('admin.role.edit');
    Route::post('/role/edit/{id}', [RoleController::class, 'update'])->name('admin.role.update');
    Route::patch('/role/{id}/status', [RoleController::class, 'updateStatus'])->name('admin.role.update.status');
    Route::delete('/role/{id}/delete', [RoleController::class, 'destroy'])->name('admin.role.destroy');
    Route::get('/role/search', [RoleController::class, 'searchRole'])->name('admin.role.search');
    // new routes for dynamic dropdowns
    Route::get('/roles/departments-by-organization', [RoleController::class, 'getDepartmentsByOrganization'])
        ->name('admin.role.departmentsByOrganization');

    Route::get('/roles/parent-roles', [RoleController::class, 'getParentRoles'])
        ->name('admin.role.parentRoles');

    Route::get('/register', [RegistrationController::class, 'index'])->name('admin.register.index');
    Route::post('/register/add-document-type', [RegistrationController::class, 'addDocumentType'])->name('admin.register.add_document_type');

    Route::get('/employees', [EmployeeController::class, 'index'])->name('admin.employee.index');
    Route::redirect('/employee', '/admin/employees');
    Route::get('/employee/{id}/edit', function ($id) {
        return redirect()->route('admin.employee.edit', ['id' => $id]);
    });

    Route::get('/employees/data', [EmployeeController::class, 'tableData'])->name('admin.employee.data');
    Route::get('/employees/universities', [EmployeeController::class, 'universities'])->name('admin.employee.universities');
    Route::get('/employees/stats', [EmployeeController::class, 'stats'])->name('admin.employee.stats');
    Route::get('/outsourced-employees/data', [OutsourcedEmployeeController::class, 'tableData'])->name('admin.outsourced_employee.data');
    Route::get('/outsourced-employees/{id}', [OutsourcedEmployeeController::class, 'show'])->name('admin.outsourced_employee.show');
    Route::post('/outsourced-employees/store', [OutsourcedEmployeeController::class, 'store'])->name('admin.outsourced_employee.store');
    Route::post('/outsourced-employees/{id}/update', [OutsourcedEmployeeController::class, 'update'])->name('admin.outsourced_employee.update');
    Route::get('/employees/preview-employee-code', [EmployeeController::class, 'previewEmployeeCode'])->name('admin.employee.preview_code');
    Route::get('/employees/check-line-manager', [EmployeeController::class, 'checkLineManagerAvailability'])->name('admin.employee.check_line_manager');
    Route::get('/employees/designations-for-employment', [EmployeeController::class, 'designationsForEmployment'])->name('admin.employee.designations_for_employment');
    Route::post('/employees/store', [EmployeeController::class, 'store'])->name('admin.employee.store');
    Route::post('/employees/save-step', [EmployeeController::class, 'saveStep'])->name('admin.employee.save_step');
    Route::post('/employees/save-subsection', [EmployeeController::class, 'saveSubsection'])->name('admin.employee.save_subsection');
    Route::post('/employees/delete-family', [EmployeeController::class, 'deleteFamily'])->name('admin.employee.delete_family');
    Route::post('/employees/delete-academic', [EmployeeController::class, 'deleteAcademic'])->name('admin.employee.delete_academic');
    Route::post('/employees/delete-certificate', [EmployeeController::class, 'deleteCertificate'])->name('admin.employee.delete_certificate');
    Route::post('/employees/delete-employment', [EmployeeController::class, 'deleteEmployment'])->name('admin.employee.delete_employment');
    Route::post('/employees/delete-bank-detail', [EmployeeController::class, 'deleteBankDetail'])->name('admin.employee.delete_bank_detail');
    Route::post('/employees/delete-photo', [EmployeeController::class, 'deletePhoto'])->name('admin.employee.delete_photo');
    Route::post('/employees/save-attachment', [EmployeeController::class, 'saveAttachment'])->name('admin.employee.save_attachment');
    Route::post('/employees/delete-attachment', [EmployeeController::class, 'deleteAttachment'])->name('admin.employee.delete_attachment');
    Route::get('/employees/{id}/attachments', [EmployeeController::class, 'attachments'])->name('admin.employee.attachments');
    Route::get('/employees/{id}/edit', [EmployeeController::class, 'edit'])->name('admin.employee.edit');
    Route::post('/employees/{id}/update', [EmployeeController::class, 'update'])->name('admin.employee.update');
    Route::delete('/employees/{id}/delete', [EmployeeController::class, 'destroy'])->name('admin.employee.destroy');

    // Location Routes for Registration
    Route::get('/locations/countries', [LocationController::class, 'getCountries'])->name('admin.locations.countries');
    Route::get('/locations/provinces/{countryName}', [LocationController::class, 'getProvinces'])->name('admin.locations.provinces');
    Route::get('/locations/districts/{countryName}/{provinceName}', [LocationController::class, 'getDistricts'])->name('admin.locations.districts');

    Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/users/data', [UserController::class, 'data'])->name('admin.users.data');
    Route::get('/users/stats', [UserController::class, 'stats'])->name('admin.users.stats');
    Route::post('/users/store', [UserController::class, 'store'])->name('admin.users.store');
    Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('admin.users.reset-password');
    Route::post('/users/{id}/update', [UserController::class, 'update'])->name('admin.users.update');
    Route::patch('/users/{id}/status', [UserController::class, 'updateStatus'])->name('admin.users.status');
    Route::delete('/users/{id}/delete', [UserController::class, 'destroy'])->name('admin.users.destroy');

    // Notifications
    Route::get('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsReadAndRedirect'])->name('admin.notifications.read');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('admin.notifications.mark-all-read');

    // Leave Calendar
    Route::get('/leave-calendar', [LeaveCalendarController::class, 'index'])->name('admin.leave-calendar.index');
    Route::get('/leave-calendar/add', [LeaveCalendarController::class, 'create'])->name('admin.leave-calendar.add');
    Route::post('/leave-calendar/store', [LeaveCalendarController::class, 'store'])->name('admin.leave-calendar.store');
    Route::get('/leave-calendar/show/{id}', [LeaveCalendarController::class, 'show'])->name('admin.leave-calendar.show');
    Route::post('/leave-calendar/update/{id}', [LeaveCalendarController::class, 'update'])->name('admin.leave-calendar.update');
    Route::delete('/leave-calendar/destroy/{id}', [LeaveCalendarController::class, 'destroy'])->name('admin.leave-calendar.destroy');
    Route::get('/leave-calendar/fetch-department-employees', [LeaveCalendarController::class, 'fetchDepartmentLeaveEmployees'])->name('admin.leave-calendar.fetch-department-employees');

    // Leave Balance
    Route::get('/balance-tracker', [BalanceTrackerController::class, 'index'])->name('admin.balance-tracker.index');
    Route::post('/balance-tracker', [BalanceTrackerController::class, 'adjustBalance'])->name('admin.balance-tracker.adjust');
    Route::get('/balance-tracker/export', [BalanceTrackerController::class, 'export'])->name('admin.balance-tracker.export');
    // Route::get('/users', function () {
    //     return view('admin.users.index'); // Placeholder - replace with actual users view
    // })->name('admin.users.index');

    // Route::get('/departments', function () {
    //     return view('admin.departments.index');
    // })->name('admin.departments.index');

    // Route::get('/sbu', function () {
    //     return view('admin.organization.index');
    // })->name('admin.organization.index');

    // Route::get('/settings', function () {
    //     return view('admin.dashboard'); // Placeholder - replace with actual settings view
    // })->name('admin.settings');

    Route::get('/daily-logs', function () {
        return redirect()->route('admin.dashboard.index');
    })->name('admin.monthly-logs.index');

    Route::get('/shift-planner', [ShiftPlannerController::class, 'index'])->name('admin.shift-planner.index');
    Route::post('/shift-planner', [ShiftPlannerController::class, 'store'])->name('admin.shift-planner.store');
    Route::get('/shift-planner/{id}', [ShiftPlannerController::class, 'show'])->name('admin.shift-planner.show');
    Route::post('/shift-planner/{id}', [ShiftPlannerController::class, 'update'])->name('admin.shift-planner.update');
    Route::delete('/shift-planner/{id}', [ShiftPlannerController::class, 'destroy'])->name('admin.shift-planner.destroy');

    // Shift Roster
    Route::get('/shift-roster', [ShiftRosterController::class, 'index'])->name('admin.shift-roster.index');
    Route::get('/shift-roster/grid', [ShiftRosterController::class, 'grid'])->name('admin.shift-roster.grid');
    Route::get('/shift-roster/floor-options', [ShiftRosterController::class, 'floorOptions'])->name('admin.shift-roster.floor-options');
    Route::post('/shift-roster/bulk-floor-options', [ShiftRosterController::class, 'bulkFloorOptions'])->name('admin.shift-roster.bulk-floor-options');
    Route::post('/shift-roster/export-pdf', [ShiftRosterController::class, 'exportPdf'])->name('admin.shift-roster.export-pdf');
    Route::get('/shift-roster/export-excel/departments', [ShiftRosterExcelExportController::class, 'departmentOptions'])
        ->name('admin.shift-roster.export-excel.departments');
    Route::post('/shift-roster/export-excel', ShiftRosterExcelExportController::class)->name('admin.shift-roster.export-excel');
    Route::post('/shift-roster', [ShiftRosterController::class, 'store'])->name('admin.shift-roster.store');
    Route::post('/shift-roster/bulk-assign', [ShiftRosterController::class, 'bulkAssign'])
        ->name('admin.shift-roster.bulk-assign');
    Route::post('/shift-roster/apply-for-approval', [ShiftRosterApprovalController::class, 'applyForApproval'])
        ->name('admin.shift-roster.apply-for-approval');
    Route::get('/shift-roster/approvals/pending', [ShiftRosterApprovalController::class, 'pending'])
        ->name('admin.shift-roster.approvals.pending');
    Route::get('/shift-roster/approvals/{id}', [ShiftRosterApprovalController::class, 'show'])
        ->name('admin.shift-roster.approvals.show');
    Route::post('/shift-roster/approvals/{id}/approve', [ShiftRosterApprovalController::class, 'approve'])
        ->name('admin.shift-roster.approvals.approve');
    Route::post('/shift-roster/approvals/{id}/reject', [ShiftRosterApprovalController::class, 'reject'])
        ->name('admin.shift-roster.approvals.reject');
    Route::get('/shift-roster/{id}/change-history', [ShiftRosterController::class, 'changeHistory'])
        ->name('admin.shift-roster.change-history');
    Route::get('/shift-roster/{id}', [ShiftRosterController::class, 'show'])->name('admin.shift-roster.show');
    Route::post('/shift-roster/{id}', [ShiftRosterController::class, 'update'])->name('admin.shift-roster.update');
    Route::delete('/shift-roster/{id}', [ShiftRosterController::class, 'destroy'])->name('admin.shift-roster.destroy');

    Route::get('/regularization', function () {
        return redirect()->route('admin.dashboard.index');
    })->name('admin.regularization.index');

    Route::get('/geofencing', [GeofenceController::class, 'index'])->name('admin.geofencing.index');
    Route::post('/geofencing', [GeofenceController::class, 'store'])->name('admin.geofencing.store');
    Route::get('/geofencing/{id}/edit', [GeofenceController::class, 'edit'])->name('admin.geofencing.edit');
    Route::post('/geofencing/{id}', [GeofenceController::class, 'update'])->name('admin.geofencing.update');
    Route::delete('/geofencing/{id}', [GeofenceController::class, 'destroy'])->name('admin.geofencing.destroy');
    Route::get('/my-leaves', [LeaveRequestController::class, 'myLeaves'])->name('admin.my.leaves.index');

    // Monthly Summary ROutes
    Route::get('/monthly-summary', [MonthlySummaryController::class, 'index'])->name('admin.monthly-summary.index');
    Route::get('/monthly-summary/employees/{employeeId}/calendar', [MonthlySummaryController::class, 'employeeCalendar'])->name('admin.monthly-summary.employee_calendar');

    // Route::get('/my-leaves', function () {
    //     return view('admin.my-leaves.index');
    // })->name('admin.my-leaves.index');

    // Route::get('/leave-calendar', function () {
    //     return view('admin.leave-calendar.index');
    // })->name('admin.leave-calendar.index');

    // Route::get('/balance-tracker', function () {
    //     return view('admin.balance-tracker.index');
    // })->name('admin.balance-tracker.index');

    Route::get('/roles', function () {
        return view('admin.roles-permissions.index');
    })->name('admin.roles.index');

    Route::get('/overtime-tracker', function () {
        return redirect()->route('admin.dashboard.index');
    })->name('admin.overtime.index');

    // Policies Routes
    Route::get('/policies', [PolicyController::class, 'index'])->name('admin.policies.index');
    Route::post('/policies', [PolicyController::class, 'store'])->name('admin.policies.store');
    Route::get('/policies/{id}', [PolicyController::class, 'show'])->name('admin.policies.show');
    Route::post('/policies/{id}', [PolicyController::class, 'update'])->name('admin.policies.update');
    Route::delete('/policies/{id}', [PolicyController::class, 'destroy'])->name('admin.policies.destroy');

    // Workflow Management Routes
    Route::get('/workflows', [WorkflowController::class, 'index'])->name('admin.workflows.index');
    Route::get('/workflows/data', [WorkflowController::class, 'data'])->name('admin.workflows.data');
    Route::get('/workflows/stats', [WorkflowController::class, 'stats'])->name('admin.workflows.stats');
    Route::post('/workflows/store', [WorkflowController::class, 'store'])->name('admin.workflows.store');
    Route::post('/workflows/{id}/update', [WorkflowController::class, 'update'])->name('admin.workflows.update');
    Route::patch('/workflows/{id}/status', [WorkflowController::class, 'updateStatus'])->name('admin.workflows.status');
    Route::delete('/workflows/{id}/delete', [WorkflowController::class, 'destroy'])->name('admin.workflows.destroy');

    // Route::get('/reports', function () {
    //     return view('admin.dashboard'); // Placeholder - replace with actual reports view
    // })->name('admin.reports');

    // Audit Trails Routes
    Route::get('/audit-trail', [AuditTrailController::class, 'index'])->name('admin.audit-trails.index');
    Route::get('/audit-trails/data', [AuditTrailController::class, 'data'])->name('admin.audit-trails.data');
    Route::get('/audit-trails/{auditTrail}', [AuditTrailController::class, 'show'])->name('admin.audit-trails.show');
});
