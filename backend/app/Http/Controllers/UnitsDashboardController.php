<?php

namespace App\Http\Controllers;

use App\Models\Unit;

class UnitsDashboardController extends Controller
{
    public function index()
    {
        $units = Unit::orderBy('name')->get()
            ->map(fn ($u) => [
                'id'        => $u->id,
                'name'      => $u->name,
                'shortName' => $u->short_name,
                'isActive'  => (bool) $u->is_active,
                'createdAt' => $u->created_at->format('Y-m-d'),
            ])
            ->values();

        $profile = session('admin_profile');

        return view('admin.products.units', compact('units', 'profile'));
    }
}
