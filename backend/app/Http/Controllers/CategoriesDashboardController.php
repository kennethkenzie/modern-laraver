<?php

namespace App\Http\Controllers;

use App\Models\Category;

class CategoriesDashboardController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent')
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => [
                'id'             => $c->id,
                'name'           => $c->name,
                'slug'           => $c->slug,
                'description'    => $c->description,
                'imageUrl'       => $c->image_url,
                'isActive'       => (bool) $c->is_active,
                'featuredOnHome' => (bool) $c->featured_on_home,
                'sortOrder'      => (int) $c->featured_sort_order,
                'parentId'       => $c->parent_id,
                'parentName'     => $c->parent?->name,
            ])
            ->values();

        $profile = session('admin_profile');

        return view('admin.products.categories', compact('categories', 'profile'));
    }
}
