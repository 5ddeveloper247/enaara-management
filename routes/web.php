<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\EmployeeTypeController;
use App\Http\Controllers\WorkTypeController;
use App\Http\Controllers\AttendanceModesController;
use App\Http\Controllers\ShiftTypesController;
use App\Http\Controllers\SbuController;
use App\Http\Controllers\SbuFloorsController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\ModuleCategoryController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\LeaveCalendarController;
use App\Http\Controllers\BalanceTrackerController;
use App\Http\Controllers\Admin\GeofenceController;
// Authentication Routes
Route::get('/', function () {
    return redirect()->route('login');
});
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->prefix('admin')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard.index');
    Route::get('/organization', [OrganizationController::class, 'index'])->name('admin.organization.index');

    Route::get('/sbu', [SbuController::class, 'index'])->name('admin.sbu.index');
    Route::get('/sbu/{id}/show', [SbuController::class, 'show'])->name('admin.sbu.show');

    Route::get('/sbu-floor', [SbuFloorsController::class, 'index'])->name('admin.sbu.floor.index');
    Route::get('/sbu-floor/{id}/show', [SbuFloorsController::class, 'show'])->name('admin.sbu.floor.show');

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


    Route::get('/leave-type', [LeaveTypeController::class, 'index'])->name('admin.leave.type.index');
    Route::get('/leave-type/add', [LeaveTypeController::class, 'create'])->name('admin.leave.type.add');
    Route::post('/leave-type/add', [LeaveTypeController::class, 'store'])->name('admin.leave.type.store');
    Route::get('/leave-type/edit/{id}', [LeaveTypeController::class, 'edit'])->name('admin.leave.type.edit');
    Route::post('/leave-type/edit/{id}', [LeaveTypeController::class, 'update'])->name('admin.leave.type.update');

    //Leave Requests 
    Route::get('/leave-request', [LeaveRequestController::class, 'index'])->name('admin.leave.request.index');
    Route::get('/leave-request/add', [LeaveRequestController::class, 'create'])->name('admin.leave.request.add');
    Route::post('/leave-request/add', [LeaveRequestController::class, 'store'])->name('admin.leave.request.store');
    Route::get('/leave-request/leave-types', [LeaveRequestController::class, 'leaveTypesForEmployee'])->name('admin.leave.request.leave-types');
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
    //new routes for dynamic dropdowns
    Route::get('/roles/departments-by-organization', [RoleController::class, 'getDepartmentsByOrganization'])
        ->name('admin.role.departmentsByOrganization');

    Route::get('/roles/parent-roles', [RoleController::class, 'getParentRoles'])
        ->name('admin.role.parentRoles');

    Route::get('/register', [RegistrationController::class, 'index'])->name('admin.register.index');

    Route::get('/employee', [EmployeeController::class, 'index'])->name('admin.employee.index');
    Route::get('/employee/add', [EmployeeController::class, 'create'])->name('admin.employee.add');
    Route::post('/employee/add', [EmployeeController::class, 'store'])->name('admin.employee.store');
    Route::get('/employee/edit/{id}', [EmployeeController::class, 'edit'])->name('admin.employee.edit');
    Route::post('/employee/edit/{id}', [EmployeeController::class, 'update'])->name('admin.employee.update');
    Route::delete('/employee/{id}/delete', [EmployeeController::class, 'destroy'])->name('admin.employee.destroy');

    // Notifications
    Route::get('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsReadAndRedirect'])->name('admin.notifications.read');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('admin.notifications.mark-all-read');

    //Leave Calendar
    Route::get('/leave-calendar', [LeaveCalendarController::class, 'index'])->name('admin.leave-calendar.index');
    Route::get('/leave-calendar/add', [LeaveCalendarController::class, 'create'])->name('admin.leave-calendar.add');
        Route::post('/leave-calendar/store', [LeaveCalendarController::class, 'store'])->name('admin.leave-calendar.store');
        Route::get('/leave-calendar/show/{id}', [LeaveCalendarController::class, 'show'])->name('admin.leave-calendar.show');
        Route::post('/leave-calendar/update/{id}', [LeaveCalendarController::class, 'update'])->name('admin.leave-calendar.update');
        Route::delete('/leave-calendar/destroy/{id}', [LeaveCalendarController::class, 'destroy'])->name('admin.leave-calendar.destroy');
        Route::get('/leave-calendar/fetch-department-employees', [LeaveCalendarController::class, 'fetchDepartmentLeaveEmployees'])->name('admin.leave-calendar.fetch-department-employees');


    //Leave Balance 
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

    Route::get('/monthly-logs', function () {
        return view('admin.monthly-logs.index');
    })->name('admin.monthly-logs.index');

    Route::get('/shift-planner', function () {
        return view('admin.shift-planner.index');
    })->name('admin.shift-planner.index');

    // Route::get('/regularization', function () {
    //     return view('admin.regularization.index');
    // })->name('admin.regularization.index');

    Route::get('/geofencing', [GeofenceController::class, 'index'])->name('admin.geofencing.index');
    Route::post('/geofencing', [GeofenceController::class, 'store'])->name('admin.geofencing.store');
    Route::get('/geofencing/{id}/edit', [GeofenceController::class, 'edit'])->name('admin.geofencing.edit');
    Route::post('/geofencing/{id}', [GeofenceController::class, 'update'])->name('admin.geofencing.update');
    Route::delete('/geofencing/{id}', [GeofenceController::class, 'destroy'])->name('admin.geofencing.destroy');
    Route::get('/my-leaves', [LeaveRequestController::class, 'myLeaves'])->name('admin.my.leaves.index');

 
    Route::get('/roles', function () {
        return view('admin.roles-permissions.index');
    })->name('admin.roles.index');

    // Route::get('/monthly-summary', function () {
    //     return view('admin.monthly-summary.index');
    // })->name('admin.monthly-summary.index');

    // Route::get('/overtime', function () {
    //     return view('admin.overtime.index');
    // })->name('admin.overtime.index');

    // Route::get('/audit-trails', function () {
    //     return view('admin.audit-trails.index');
    // })->name('admin.audit-trails.index');

    Route::get('/policies', function () {
        return view('admin.policies.index');
    })->name('admin.policies.index');

    // Route::get('/workflows', function () {
    //     return view('admin.workflows.index');
    // })->name('admin.workflows.index');

    // Route::get('/reports', function () {
    //     return view('admin.dashboard'); // Placeholder - replace with actual reports view
    // })->name('admin.reports');

    
});
