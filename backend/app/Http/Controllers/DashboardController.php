<?php

namespace App\Http\Controllers;

use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts  = Product::count();
        $publishedCount = Product::where('is_published', true)->count();
        $featuredCount  = Product::where('is_featured_home', true)->count();
        $draftCount     = $totalProducts - $publishedCount;

        // Products with at least one active variant that is low-on-stock
        $lowStockProducts = Product::with([
            'category',
            'variants' => fn ($q) => $q->where('is_active', true)->orderBy('stock_qty'),
        ])
        ->whereHas('variants', fn ($q) =>
            $q->where('is_active', true)->where('stock_qty', '>', 0)->where('stock_qty', '<=', 5)
        )
        ->orderByDesc('created_at')
        ->limit(4)
        ->get()
        ->map(function ($product) {
            $minStock = $product->variants->min('stock_qty') ?? 0;
            return [
                'id'       => $product->id,
                'name'     => $product->name,
                'category' => $product->category?->name ?? '—',
                'stock'    => $minStock,
                'status'   => $minStock <= 2 ? 'Critical' : 'Low',
            ];
        });

        $recentUploads = Product::with('category')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get()
            ->map(function ($product) {
                $price = $product->sale_price ?? $product->list_price ?? 0;
                return [
                    'id'        => $product->id,
                    'name'      => $product->name,
                    'category'  => $product->category?->name ?? '—',
                    'price'     => 'UGX ' . number_format((float) $price, 0, '.', ','),
                    'status'    => $product->is_published ? 'Published' : 'Draft',
                    'createdAt' => $product->created_at->format('M j, g:i A'),
                ];
            });

        // Products missing price or images
        $productsToReview = Product::where(function ($q) {
            $q->whereNull('sale_price')->whereNull('list_price');
        })->orWhereDoesntHave('media')->count();

        $profile = session('admin_profile');

        return view('admin.dashboard', compact(
            'totalProducts',
            'publishedCount',
            'draftCount',
            'featuredCount',
            'lowStockProducts',
            'recentUploads',
            'productsToReview',
            'profile'
        ));
    }
}
