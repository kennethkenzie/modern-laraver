<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class ProductsDashboardController extends Controller
{
    public function index()
    {
        $products = Product::with([
            'category',
            'media'    => fn ($q) => $q->orderByDesc('is_primary')->orderBy('sort_order'),
            'variants' => fn ($q) => $q->where('is_active', true)->orderByDesc('is_default')->orderBy('sort_order'),
        ])
        ->orderByDesc('created_at')
        ->get()
        ->map(function ($p) {
            $variant  = $p->variants->first();
            $image    = $p->media->first()?->url ?? '';
            $stock    = $p->variants->sum('stock_qty');
            $price    = (float) ($variant?->price ?? $p->sale_price ?? $p->list_price ?? 0);
            return [
                'id'          => $p->id,
                'name'        => $p->name,
                'slug'        => $p->slug,
                'category'    => $p->category?->name ?? '—',
                'price'       => $price,
                'stock'       => (int) $stock,
                'image'       => $image,
                'isPublished' => (bool) $p->is_published,
                'isFeatured'  => (bool) $p->is_featured_home,
                'createdAt'   => $p->created_at->format('Y-m-d'),
            ];
        })
        ->values();

        $profile = session('admin_profile');

        return view('admin.products.index', compact('products', 'profile'));
    }

    public function add()
    {
        $categories = Category::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name]);

        $profile = session('admin_profile');

        return view('admin.products.add', compact('categories', 'profile'));
    }

    public function show(string $id)
    {
        $product = Product::with(['category', 'media', 'variants', 'specs', 'bullets'])
            ->findOrFail($id);

        $profile = session('admin_profile');

        return view('admin.products.show', compact('product', 'profile'));
    }
}
