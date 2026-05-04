<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Profile;
use App\Models\Review;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Product::count();
        $publishedCount = Product::where('is_published', true)->count();
        $featuredCount = Product::where('is_featured_home', true)->count();
        $draftCount = max($totalProducts - $publishedCount, 0);

        $lowStockProducts = Product::with([
            'category',
            'variants' => fn ($query) => $query->where('is_active', true)->orderBy('stock_qty'),
        ])
            ->whereHas('variants', fn ($query) => $query->where('is_active', true)->where('stock_qty', '>', 0)->where('stock_qty', '<=', 5))
            ->latest()
            ->limit(5)
            ->get()
            ->map(function (Product $product) {
                $stock = (int) ($product->variants->min('stock_qty') ?? 0);

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category?->name ?? '—',
                    'stock' => $stock,
                    'status' => $stock <= 2 ? 'Critical' : 'Low',
                ];
            });

        $recentUploads = Product::with([
            'category',
            'variants' => fn ($query) => $query->where('is_active', true)->orderByDesc('is_default')->orderBy('sort_order'),
        ])
            ->latest()
            ->limit(6)
            ->get()
            ->map(function (Product $product) {
                $variantPrice = $product->variants->first()?->price;
                $price = $variantPrice ?? $product->sale_price ?? $product->list_price ?? 0;

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category?->name ?? '—',
                    'price' => 'UGX ' . number_format((float) $price, 0, '.', ','),
                    'status' => $product->is_published ? 'Published' : 'Draft',
                    'createdAt' => $product->created_at->format('M j, g:i A'),
                ];
            });

        $topCategories = Category::withCount('products')
            ->orderByDesc('products_count')
            ->limit(5)
            ->get()
            ->map(function (Category $category) {
                return [
                    'name' => $category->name,
                    'count' => (int) $category->products_count,
                ];
            });

        $maxCategoryCount = max(1, (int) ($topCategories->max('count') ?? 1));
        $topCategories = $topCategories->map(function (array $category) use ($maxCategoryCount) {
            $category['percent'] = (int) round(($category['count'] / $maxCategoryCount) * 100);
            return $category;
        });

        $recentCustomers = Profile::query()
            ->where('role', 'customer')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function (Profile $profile) {
                return [
                    'name' => $profile->full_name,
                    'email' => $profile->email ?: $profile->phone,
                    'joined' => $profile->created_at->diffForHumans(),
                ];
            });

        $recentMessages = ContactMessage::query()
            ->latest()
            ->limit(5)
            ->get()
            ->map(function (ContactMessage $message) {
                return [
                    'name' => $message->name,
                    'subject' => $message->subject ?: 'New message',
                    'status' => $message->status ?: 'unread',
                    'received' => $message->created_at->diffForHumans(),
                ];
            });

        $activeOffersCount = Offer::where('is_active', true)->count();
        $unreadMessagesCount = ContactMessage::where('status', 'unread')->count();
        $newCustomersThisMonth = Profile::where('role', 'customer')->where('created_at', '>=', now()->startOfMonth())->count();
        $pendingReviewsCount = Review::where('is_approved', false)->count();
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();

        $productsToReview = Product::where(function ($query) {
            $query->where(function ($priceQuery) {
                $priceQuery->whereNull('sale_price')
                    ->whereNull('list_price')
                    ->whereDoesntHave('variants', fn ($variantQuery) => $variantQuery->where('is_active', true)->where('price', '>', 0));
            })->orWhereDoesntHave('media');
        })->count();

        $profile = session('admin_profile');

        return view('admin.dashboard', compact(
            'totalProducts',
            'publishedCount',
            'draftCount',
            'featuredCount',
            'lowStockProducts',
            'recentUploads',
            'topCategories',
            'recentCustomers',
            'recentMessages',
            'activeOffersCount',
            'unreadMessagesCount',
            'newCustomersThisMonth',
            'pendingReviewsCount',
            'productsToReview',
            'totalOrders',
            'pendingOrders',
            'profile'
        ));
    }
}
