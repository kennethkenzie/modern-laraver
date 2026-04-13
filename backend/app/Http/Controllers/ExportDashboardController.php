<?php

namespace App\Http\Controllers;

class ExportDashboardController extends Controller
{
    public function index()
    {
        $profile = session('admin_profile');
        return view('admin.products.export', compact('profile'));
    }
}
