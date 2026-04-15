<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Support\Facades\Schema;

class BrandsDashboardController extends Controller
{
    public function index()
    {
        $brandsEnabled = Schema::hasTable('brands');

        $brands = $brandsEnabled
            ? Brand::orderBy('sort_order')->orderBy('name')->get()
                ->map(fn ($b) => [
                    'id'         => $b->id,
                    'name'       => $b->name,
                    'slug'       => $b->slug,
                    'logoUrl'    => $b->logo_url,
                    'bannerUrl'  => $b->banner_url,
                    'isActive'   => (bool) $b->is_active,
                    'isFeatured' => (bool) $b->is_featured,
                    'createdAt'  => $b->created_at->format('Y-m-d'),
                ])
                ->values()
            : collect();

        $profile = session('admin_profile');

        return view('admin.products.brands', compact('brands', 'profile', 'brandsEnabled'));
    }
}
