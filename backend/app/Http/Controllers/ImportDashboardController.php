<?php

namespace App\Http\Controllers;

class ImportDashboardController extends Controller
{
    public function index()
    {
        $profile = session('admin_profile');
        return view('admin.products.import', compact('profile'));
    }
}
