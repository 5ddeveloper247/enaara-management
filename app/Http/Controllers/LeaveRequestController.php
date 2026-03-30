<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function index() { return view('admin.leave.request.index'); }
    public function create() { return view('admin.leave.request.create'); }
    public function store(Request $request) {}
    public function edit(int $id) {}
    public function update(Request $request, int $id) {}
    public function destroy(int $id) {}
}
