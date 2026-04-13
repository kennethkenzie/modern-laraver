<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductRelation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * GET /api/products/latest?offset=0&limit=10
     * Returns published products for the storefront "Latest" section.
     */
    public function latest(Request $request): JsonResponse
    {
        $offset = max(0, (int) $request->query('offset', 0));
        $limit  = min(20, max(1, (int) $request->query('limit', 10)));

        $total = Product::where('is_published', true)->count();

        $products = Product::where('is_published', true)
            ->with([
                'media'    => fn ($q) => $q->orderByDesc('is_primary')->orderBy('sort_order'),
                'variants' => fn ($q) => $q->where('is_active', true)->orderByDesc('is_default')->orderBy('sort_order'),
            ])
            ->orderByDesc('is_featured_home')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->skip($offset)
            ->take($limit)
            ->get();

        return response()->json([
            'products'   => $products->map(fn ($p) => $this->mapLatestProduct($p)),
            'nextOffset' => ($offset + $limit < $total) ? $offset + $limit : null,
            'total'      => $total,
        ]);
    }

    /**
     * GET /api/products/featured
     * Returns featured products for the sidebar.
     */
    public function featured(): JsonResponse
    {
        $products = Product::where('is_published', true)
            ->with([
                'media'    => fn ($q) => $q->orderByDesc('is_primary')->orderBy('sort_order'),
                'variants' => fn ($q) => $q->where('is_active', true)->orderByDesc('is_default')->orderBy('sort_order'),
            ])
            ->orderByDesc('is_featured_home')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $result = $products->filter(fn ($p) => $this->effectivePrice($p) > 0)
            ->map(fn ($p) => $this->mapFeaturedProduct($p))
            ->values();

        return response()->json(['products' => $result]);
    }

    /**
     * GET /api/products/offer-targets?slugs[]=slug1&slugs[]=slug2
     */
    public function offerTargets(Request $request): JsonResponse
    {
        $slugs = array_unique(array_filter((array) $request->query('slugs', [])));

        if (empty($slugs)) {
            return response()->json(['products' => []]);
        }

        $products = Product::whereIn('slug', $slugs)
            ->where('is_published', true)
            ->with(['media' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('sort_order')])
            ->get()
            ->keyBy('slug');

        $result = collect($slugs)->map(function ($slug) use ($products) {
            $p = $products->get($slug);
            if (! $p) {
                return null;
            }
            return [
                'id'               => $p->id,
                'slug'             => $p->slug,
                'title'            => $p->name,
                'image'            => $p->media->first()?->url ?? '',
                'shortDescription' => $this->stripHtml($p->short_description ?? $p->description ?? 'Product offer now available.'),
                'href'             => "/product/{$p->slug}",
            ];
        })->filter()->values();

        return response()->json(['products' => $result]);
    }

    /**
     * GET /api/products/{slug}
     * Public product page data.
     */
    public function show(string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)
            ->where('is_published', true)
            ->with([
                'store',
                'category.parent',
                'media'    => fn ($q) => $q->orderByDesc('is_primary')->orderBy('sort_order'),
                'variants' => fn ($q) => $q->where('is_active', true)->orderByDesc('is_default')->orderBy('sort_order'),
                'specs'    => fn ($q) => $q->orderBy('sort_order'),
                'bullets'  => fn ($q) => $q->orderBy('sort_order'),
            ])
            ->first();

        if (! $product) {
            return response()->json(['error' => 'Product not found.'], 404);
        }

        $hiddenSpecs = ['Tax Class', 'Low Stock Alert', 'Product Type', 'Visibility', 'Tags'];

        $specs = [];
        if ($product->brand) {
            $specs[] = ['label' => 'Brand', 'value' => $product->brand];
        }
        if ($product->category) {
            $specs[] = ['label' => 'Category', 'value' => $product->category->name];
        }
        foreach ($product->specs as $spec) {
            if (! in_array($spec->spec_name, $hiddenSpecs)) {
                $specs[] = ['label' => $spec->spec_name, 'value' => $spec->spec_value];
            }
        }

        $gallery = $product->media->map(fn ($m) => [
            'id'      => $m->id,
            'image'   => $m->url,
            'alt'     => $m->alt_text ?? $product->name,
            'isVideo' => $m->kind === 'video',
        ])->values()->all();

        $variants = $product->variants->map(fn ($v) => [
            'id'         => $v->id,
            'label'      => $v->option_value,
            'price'      => (float) $v->price,
            'priceLabel' => $this->formatMoney($product->currency_code, (float) $v->price),
            'oldPrice'   => $v->compare_at_price > 0 ? $this->formatMoney($product->currency_code, (float) $v->compare_at_price) : null,
            'stockQty'   => $v->stock_qty,
            'sku'        => $v->sku,
            'isDefault'  => (bool) $v->is_default,
        ])->values()->all();

        return response()->json([
            'product' => [
                'id'                 => $product->id,
                'categoryId'         => $product->category_id,
                'slug'               => $product->slug,
                'name'               => $product->name,
                'shortDescription'   => $product->short_description ?? '',
                'description'        => $product->description ?? '',
                'brand'              => $product->brand,
                'currencyCode'       => $product->currency_code,
                'storeName'          => $product->store->name,
                'storeLabel'         => 'Visit ' . $product->store->name,
                'rating'             => (float) $product->average_rating,
                'ratingsLabel'       => number_format($product->rating_count) . ' ratings',
                'bestsellerLabel'    => $product->bestseller_label,
                'bestsellerCategory' => $product->bestseller_category,
                'boughtLabel'        => $product->bought_past_month_label,
                'shippingLabel'      => $product->shipping_label ?? 'Shipping calculated at checkout.',
                'inStockLabel'       => $product->in_stock_label ?? 'In Stock',
                'deliveryLabel'      => $product->delivery_label ?? 'Delivery details available at checkout',
                'returnsLabel'       => $product->returns_label ?? '7-day refund / replacement',
                'paymentLabel'       => $product->payment_label ?? 'Secure transaction',
                'category'           => $product->category ? [
                    'name'   => $product->category->name,
                    'slug'   => $product->category->slug,
                    'parent' => $product->category->parent ? [
                        'name' => $product->category->parent->name,
                        'slug' => $product->category->parent->slug,
                    ] : null,
                ] : null,
                'gallery'      => $gallery,
                'variants'     => $variants,
                'specs'        => $specs,
                'aboutItems'   => $this->buildAboutItems($product),
            ],
        ]);
    }

    /**
     * GET /api/products/{slug}/related
     */
    public function related(string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)->where('is_published', true)->first();

        if (! $product) {
            return response()->json(['error' => 'Product not found.'], 404);
        }

        $limit = 8;

        $explicit = ProductRelation::where('product_id', $product->id)
            ->where('relation_kind', 'related')
            ->whereHas('relatedProduct', fn ($q) => $q->where('is_published', true))
            ->with(['relatedProduct.media', 'relatedProduct.variants'])
            ->orderBy('sort_order')
            ->take($limit)
            ->get();

        $related = $explicit->map(fn ($r) => $this->mapRelatedProduct($r->relatedProduct, $r->badge_text));
        $seen    = $related->pluck('id')->all();

        if ($related->count() < $limit) {
            $fallback = Product::where('id', '!=', $product->id)
                ->whereNotIn('id', $seen)
                ->where('is_published', true)
                ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
                ->with(['media', 'variants'])
                ->orderByDesc('is_featured_home')
                ->orderByDesc('published_at')
                ->take($limit - $related->count())
                ->get();

            foreach ($fallback as $p) {
                $related->push($this->mapRelatedProduct($p));
            }
        }

        return response()->json(['products' => $related->take($limit)->values()]);
    }

    /**
     * GET /api/categories/{slug}/products
     */
    public function byCategory(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)
            ->with(['parent', 'products' => function ($q) {
                $q->where('is_published', true)
                    ->with([
                        'media'    => fn ($mq) => $mq->orderByDesc('is_primary')->orderBy('sort_order'),
                        'variants' => fn ($vq) => $vq->where('is_active', true)->orderByDesc('is_default')->orderBy('sort_order'),
                    ])
                    ->orderByDesc('created_at');
            }])
            ->first();

        if (! $category) {
            return response()->json(['error' => 'Category not found.'], 404);
        }

        $products = $category->products
            ->map(function ($p) {
                $variant  = $p->variants->first();
                $media    = $p->media->first();
                $price    = $variant ? (float) $variant->price : (float) ($p->sale_price ?? $p->list_price ?? 0);
                $oldPrice = $variant?->compare_at_price ? (float) $variant->compare_at_price : (float) ($p->list_price ?? 0);

                return [
                    'id'         => $p->id,
                    'name'       => $p->name,
                    'shortDesc'  => $this->stripHtml($p->short_description ?? $p->description ?? ''),
                    'image'      => $media?->url ?? '',
                    'price'      => $price,
                    'oldPrice'   => $oldPrice > $price ? $oldPrice : null,
                    'rating'     => (float) $p->average_rating,
                    'isFeatured' => (bool) $p->is_featured_home,
                    'href'       => "/product/{$p->slug}",
                ];
            })
            ->filter(fn ($p) => $p['price'] > 0)
            ->values();

        return response()->json([
            'categoryId'  => $category->id,
            'slug'        => $category->slug,
            'title'       => $category->name,
            'description' => $category->description ?? "Browse our collection of {$category->name}.",
            'image'       => $category->image_url ?? '',
            'rootCategory'=> $category->parent?->name ?? '',
            'products'    => $products,
        ]);
    }

    // ─── helpers ──────────────────────────────────────────────

    private function effectivePrice(Product $p): float
    {
        $variant = $p->variants->first();
        if ($variant) {
            return (float) $variant->price;
        }
        return (float) ($p->sale_price ?? $p->list_price ?? 0);
    }

    private function formatMoney(string $currency, float $amount): string
    {
        return $currency . ' ' . number_format(round($amount));
    }

    private function stripHtml(?string $value): string
    {
        if (! $value) {
            return '';
        }
        $value = preg_replace('/<br\s*\/?>/i', "\n", $value);
        $value = preg_replace('/<\/p>/i', "\n", $value);
        $value = strip_tags($value);
        $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
        return trim(preg_replace('/\s+/', ' ', $value));
    }

    private function buildAboutItems(Product $p): array
    {
        $bullets = $p->bullets->map(fn ($b) => trim($b->bullet_text))->filter()->values()->all();

        if ($bullets) {
            return $bullets;
        }

        $text = implode('. ', array_filter([
            $this->stripHtml($p->short_description),
            $this->stripHtml($p->description),
        ]));

        return collect(preg_split('/\r?\n|[.]\s+/', $text))
            ->map(fn ($s) => trim($s))
            ->filter(fn ($s) => strlen($s) > 0)
            ->take(6)
            ->values()
            ->all();
    }

    private function mapLatestProduct(Product $p): array
    {
        $variant  = $p->variants->first();
        $media    = $p->media->first();
        $price    = $this->effectivePrice($p);
        $oldPrice = $variant?->compare_at_price ? (float) $variant->compare_at_price : (float) ($p->list_price ?? 0);
        $discount = ($oldPrice > $price && $oldPrice > 0) ? round((($oldPrice - $price) / $oldPrice) * 100) : null;

        return [
            'id'              => $p->id,
            'name'            => $p->name,
            'shortDesc'       => $this->stripHtml($p->short_description ?? $p->description ?? ''),
            'image'           => $media?->url ?? '',
            'price'           => $price,
            'oldPrice'        => $oldPrice > $price ? $oldPrice : null,
            'discountPercent' => $discount,
            'rating'          => (float) $p->average_rating,
            'href'            => "/product/{$p->slug}",
        ];
    }

    private function mapFeaturedProduct(Product $p): array
    {
        $price   = $this->effectivePrice($p);
        $variant = $p->variants->first();
        $media   = $p->media->first();
        [$whole, $decimal] = explode('.', number_format($price, 2, '.', ''));
        $listPrice = $variant?->compare_at_price ? (float) $variant->compare_at_price : (float) ($p->list_price ?? 0);

        return [
            'id'           => $p->id,
            'title'        => $p->name,
            'image'        => $media?->url ?? '',
            'href'         => "/product/{$p->slug}",
            'rating'       => max(0, min(5, round((float) $p->average_rating))),
            'reviews'      => $p->rating_count > 0 ? number_format($p->rating_count) : '0',
            'currencyCode' => $p->currency_code,
            'priceWhole'   => $whole,
            'priceDecimal' => $decimal,
            'extraPrice'   => $listPrice > $price ? 'Was ' . $this->formatMoney($p->currency_code, $listPrice) : null,
            'shipping'     => $p->shipping_label ?? 'Shipping calculated at checkout',
            'delivery'     => $p->delivery_label,
            'price'        => $price,
        ];
    }

    private function mapRelatedProduct(Product $p, ?string $badgeText = null): array
    {
        $variant   = $p->variants->first();
        $price     = $this->effectivePrice($p);
        $oldPrice  = $variant?->compare_at_price ? (float) $variant->compare_at_price : (float) ($p->list_price ?? 0);
        $media     = $p->media->first();

        return [
            'id'       => $p->id,
            'title'    => $p->name,
            'image'    => $media?->url ?? '',
            'href'     => "/product/{$p->slug}",
            'rating'   => (float) $p->average_rating,
            'reviews'  => $p->rating_count > 0 ? number_format($p->rating_count) : null,
            'price'    => $this->formatMoney($p->currency_code, $price),
            'oldPrice' => $oldPrice > $price ? $this->formatMoney($p->currency_code, $oldPrice) : null,
            'tag'      => $badgeText,
        ];
    }
}
