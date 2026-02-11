<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Authentication Routes
Route::get('/', function () {
    return view('admin.dashboard.index');
})->name('admin.dashboard.index');
Route::get('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin Panel Routes
Route::prefix('admin')->group(function () { 

    Route::get('/dashboard', function () {
        return view('admin.dashboard.index');
    })->name('admin.dashboard.index');
    
    Route::get('/users', function () {
        return view('admin.users.index'); // Placeholder - replace with actual users view
    })->name('admin.users.index');
    
    Route::get('/departments', function () {
        return view('admin.departments.index');
    })->name('admin.departments.index');

    Route::get('/sbu', function () {
        return view('admin.organization.index');
    })->name('admin.organization.index');
    
    Route::get('/settings', function () {
        return view('admin.dashboard'); // Placeholder - replace with actual settings view
    })->name('admin.settings');
    
    Route::get('/daily-logs', function () {
        return view('admin.daily-logs.index');
    })->name('admin.daily-logs.index');
    
    Route::get('/employee', function () {
        return view('admin.employee.index');
    })->name('admin.employee.index');
    
    Route::get('/shift-planner', function () {
        return view('admin.shift-planner.index');
    })->name('admin.shift-planner.index');
    
    Route::get('/regularization', function () {
        return view('admin.regularization.index');
    })->name('admin.regularization.index');
    
    Route::get('/geofencing', function () {
        return view('admin.geofencing.index');
    })->name('admin.geofencing.index');
    
    Route::get('/leave-requests', function () {
        return view('admin.leave-requests.index');
    })->name('admin.leave-requests.index');
    
    Route::get('/my-leaves', function () {
        return view('admin.my-leaves.index');
    })->name('admin.my-leaves.index');
    
    Route::get('/leave-calendar', function () {
        return view('admin.leave-calendar.index');
    })->name('admin.leave-calendar.index');
    
    Route::get('/balance-tracker', function () {
        return view('admin.balance-tracker.index');
    })->name('admin.balance-tracker.index');
    
    Route::get('/roles', function () {
        return view('admin.roles-permissions.index');
    })->name('admin.roles.index');
    
    Route::get('/monthly-summary', function () {
        return view('admin.monthly-summary.index');
    })->name('admin.monthly-summary.index');
    
    Route::get('/overtime', function () {
        return view('admin.overtime.index');
    })->name('admin.overtime.index');
    
    Route::get('/audit-trails', function () {
        return view('admin.audit-trails.index');
    })->name('admin.audit-trails.index');
    
    Route::get('/policies', function () {
        return view('admin.policies.index');
    })->name('admin.policies.index');
    
    Route::get('/workflows', function () {
        return view('admin.workflows.index');
    })->name('admin.workflows.index');
    
    // Route::get('/reports', function () {
    //     return view('admin.dashboard'); // Placeholder - replace with actual reports view
    // })->name('admin.reports');
    
    // Route::get('/analytics', function () {
    //     return view('admin.dashboard'); // Placeholder - replace with actual analytics view
    // })->name('admin.analytics');
});
