<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;

class CustomersDashboardController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');

        $query = Profile::orderByDesc('created_at');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->paginate(20)->withQueryString();

        $totalCustomers  = Profile::count();
        $newThisMonth    = Profile::where('created_at', '>=', now()->startOfMonth())->count();

        return view('admin.customers.index', compact('customers', 'totalCustomers', 'newThisMonth', 'search'));
    }
}
