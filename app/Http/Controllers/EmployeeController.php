<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View
    {
        
        return view('admin.employee.index');
    }
}
