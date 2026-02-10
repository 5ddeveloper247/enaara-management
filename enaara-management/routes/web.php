<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/admin/dashboard');
    }

    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return redirect('/admin/dashboard');
})->middleware(['auth'])->name('dashboard');

// Admin Panel Routes (protected)
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard.index');
    })->name('admin.dashboard.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/users', function () {
        return view('admin.users.index');
    })->name('admin.users.index');

    Route::get('/departments', function () {
        return view('admin.departments.index');
    })->name('admin.departments.index');

    Route::get('/organization', function () {
        return view('admin.organization.index');
    })->name('admin.organization.index');

    Route::get('/settings', function () {
        return view('admin.dashboard.index');
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
});

require __DIR__.'/auth.php';
