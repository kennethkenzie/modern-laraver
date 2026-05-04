<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    /**
     * GET /api/admin/products
     */
    public function index(): JsonResponse
    {
        $products = Product::with([
            'category',
            'media'    => fn ($q) => $q->orderByDesc('is_primary')->orderBy('sort_order'),
            'variants' => fn ($q) => $q->where('is_active', true)->orderByDesc('is_default')->orderBy('sort_order'),
        ])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($p) => $this->mapListProduct($p));

        return response()->json(['products' => $products]);
    }

    /**
     * POST /api/admin/products
     */
    public function store(Request $request): JsonResponse
    {
        $body = $request->json()->all();

        if (empty(trim($body['name'] ?? ''))) {
            return response()->json(['error' => 'Product name is required.'], 400);
        }
        if (empty(trim($body['slug'] ?? ''))) {
            return response()->json(['error' => 'Product slug is required.'], 400);
        }
        if (empty($body['variants'])) {
            return response()->json(['error' => 'At least one product variant is required.'], 400);
        }

        $validVariants = array_filter($body['variants'], fn ($v) => $this->parseDecimal($v['price'] ?? '') !== null);
        if (! $validVariants) {
            return response()->json(['error' => 'At least one variant must have a valid price.'], 400);
        }
        $validVariants = array_values($validVariants);
        $first         = $validVariants[0];

        $storeId    = $this->getDefaultStoreId();
        $categoryId = $this->getCategoryId($body['categoryName'] ?? null, $body['categoryId'] ?? null);

        $product = Product::create([
            'id'                    => (string) Str::uuid(),
            'store_id'              => $storeId,
            'category_id'           => $categoryId,
            'slug'                  => $this->slugify($body['slug']),
            'name'                  => trim($body['name']),
            'short_description'     => trim($body['shortDescription'] ?? '') ?: null,
            'description'           => trim($body['description'] ?? '') ?: null,
            'brand'                 => trim($body['brand'] ?? '') ?: null,
            'currency_code'         => $body['currencyCode'] ?? 'UGX',
            'list_price'            => $this->parseDecimal($first['compareAtPrice'] ?? '') ?? $this->parseDecimal($first['price'] ?? ''),
            'sale_price'            => $this->parseDecimal($first['price'] ?? ''),
            'shipping_label'        => trim($body['shippingLabel'] ?? '') ?: null,
            'delivery_label'        => trim($body['deliveryLabel'] ?? '') ?: null,
            'returns_label'         => trim($body['returnsLabel'] ?? '') ?: null,
            'payment_label'         => trim($body['paymentLabel'] ?? '') ?: 'Secure transaction',
            'bestseller_label'      => trim($body['bestsellerLabel'] ?? '') ?: null,
            'bestseller_category'   => trim($body['bestsellerCategory'] ?? '') ?: null,
            'bought_past_month_label' => trim($body['boughtPastMonthLabel'] ?? '') ?: null,
            'is_published'          => ($body['action'] ?? '') === 'publish',
            'is_featured_home'      => (bool) ($body['featured'] ?? false),
            'published_at'          => ($body['action'] ?? '') === 'publish' ? now() : null,
        ]);

        // Media
        foreach (array_values($body['media'] ?? []) as $i => $item) {
            if (empty($item['url'])) {
                continue;
            }
            $product->media()->create([
                'id'         => (string) Str::uuid(),
                'kind'       => $item['kind'] ?? 'image',
                'url'        => $item['url'],
                'alt_text'   => $item['altText'] ?? $body['name'],
                'is_primary' => $i === 0,
                'sort_order' => $i,
            ]);
        }

        // Variants
        foreach ($validVariants as $i => $v) {
            $product->variants()->create([
                'id'               => (string) Str::uuid(),
                'option_name'      => 'Variant',
                'option_value'     => trim($v['label'] ?? '') ?: "Option " . ($i + 1),
                'sku'              => trim($v['sku'] ?? '') ?: null,
                'price'            => $this->parseDecimal($v['price']),
                'compare_at_price' => $this->parseDecimal($v['compareAtPrice'] ?? ''),
                'stock_qty'        => $this->parseInt($v['stockQty'] ?? ''),
                'is_default'       => $i === 0,
                'is_active'        => true,
                'sort_order'       => $i,
            ]);
        }

        // Specs
        foreach (array_values($body['specs'] ?? []) as $i => $spec) {
            if (empty(trim($spec['label'] ?? '')) || empty(trim($spec['value'] ?? ''))) {
                continue;
            }
            $product->specs()->create([
                'id'         => (string) Str::uuid(),
                'spec_name'  => trim($spec['label']),
                'spec_value' => trim($spec['value']),
                'sort_order' => $i,
            ]);
        }

        // Bullets
        foreach (array_values($body['bullets'] ?? []) as $i => $bullet) {
            if (empty(trim($bullet ?? ''))) {
                continue;
            }
            $product->bullets()->create([
                'id'          => (string) Str::uuid(),
                'bullet_text' => trim($bullet),
                'sort_order'  => $i,
            ]);
        }

        return response()->json([
            'product' => ['id' => $product->id, 'slug' => $product->slug, 'isPublished' => $product->is_published],
        ]);
    }

    /**
     * GET /api/admin/products/{id}
     */
    public function show(string $id): JsonResponse
    {
        $product = Product::with(['category', 'media', 'variants', 'specs', 'bullets'])->find($id);

        if (! $product) {
            return response()->json(['error' => 'Product not found.'], 404);
        }

        return response()->json([
            'product' => [
                'id'                    => $product->id,
                'name'                  => $product->name,
                'slug'                  => $product->slug,
                'categoryId'            => $product->category_id,
                'category'              => $product->category?->name ?? 'Select Category',
                'brand'                 => $product->brand ?? 'Select Brand',
                'currencyCode'          => $product->currency_code,
                'shortDescription'      => $product->short_description ?? '',
                'description'           => $product->description ?? '',
                'featured'              => (bool) $product->is_featured_home,
                'shippingLabel'         => $product->shipping_label ?? '',
                'deliveryLabel'         => $product->delivery_label ?? '',
                'returnsLabel'          => $product->returns_label ?? '',
                'paymentLabel'          => $product->payment_label ?? '',
                'bestsellerLabel'       => $product->bestseller_label ?? '',
                'bestsellerCategory'    => $product->bestseller_category ?? '',
                'boughtPastMonthLabel'  => $product->bought_past_month_label ?? '',
                'isPublished'           => (bool) $product->is_published,
                'media'    => $product->media->map(fn ($m) => [
                    'id'   => $m->id,
                    'name' => $m->alt_text ?? "{$product->name}-{$m->sort_order}",
                    'url'  => $m->url,
                    'kind' => $m->kind,
                ])->values(),
                'variants' => $product->variants->map(fn ($v) => [
                    'id'             => $v->id,
                    'label'          => $v->option_value,
                    'sku'            => $v->sku ?? '',
                    'price'          => (string) (float) $v->price,
                    'compareAtPrice' => $v->compare_at_price ? (string) (float) $v->compare_at_price : '',
                    'stockQty'       => (string) $v->stock_qty,
                ])->values(),
                'specs'   => $product->specs->map(fn ($s) => [
                    'id'    => $s->id,
                    'label' => $s->spec_name,
                    'value' => $s->spec_value,
                ])->values(),
                'bullets' => $product->bullets->map(fn ($b) => $b->bullet_text)->values(),
            ],
        ]);
    }

    /**
     * PATCH /api/admin/products/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json(['error' => 'Product not found.'], 404);
        }

        $body   = $request->json()->all();
        $action = $body['action'] ?? 'update';

        if ($action === 'set_publish') {
            $product->update([
                'is_published' => (bool) ($body['isPublished'] ?? false),
                'published_at' => ($body['isPublished'] ?? false) ? now() : null,
            ]);
            return response()->json(['product' => ['id' => $product->id, 'isPublished' => $product->is_published]]);
        }

        if ($action === 'set_featured') {
            $product->update(['is_featured_home' => (bool) ($body['isFeatured'] ?? false)]);
            return response()->json(['product' => ['id' => $product->id, 'isFeaturedHome' => $product->is_featured_home]]);
        }

        // Full update
        $validVariants = array_filter($body['variants'] ?? [], fn ($v) => $this->parseDecimal($v['price'] ?? '') !== null);
        if (! $validVariants) {
            return response()->json(['error' => 'At least one variant must have a valid price.'], 400);
        }
        $validVariants = array_values($validVariants);
        $first         = $validVariants[0];

        $categoryId = $this->getCategoryId($body['categoryName'] ?? null, $body['categoryId'] ?? null);

        $product->update([
            'category_id'           => $categoryId,
            'slug'                  => $this->slugify($body['slug'] ?? $product->slug),
            'name'                  => trim($body['name'] ?? $product->name),
            'short_description'     => trim($body['shortDescription'] ?? '') ?: null,
            'description'           => trim($body['description'] ?? '') ?: null,
            'brand'                 => trim($body['brand'] ?? '') ?: null,
            'currency_code'         => $body['currencyCode'] ?? 'UGX',
            'list_price'            => $this->parseDecimal($first['compareAtPrice'] ?? '') ?? $this->parseDecimal($first['price'] ?? ''),
            'sale_price'            => $this->parseDecimal($first['price'] ?? ''),
            'shipping_label'        => trim($body['shippingLabel'] ?? '') ?: null,
            'delivery_label'        => trim($body['deliveryLabel'] ?? '') ?: null,
            'returns_label'         => trim($body['returnsLabel'] ?? '') ?: null,
            'payment_label'         => trim($body['paymentLabel'] ?? '') ?: 'Secure transaction',
            'bestseller_label'      => trim($body['bestsellerLabel'] ?? '') ?: null,
            'bestseller_category'   => trim($body['bestsellerCategory'] ?? '') ?: null,
            'bought_past_month_label' => trim($body['boughtPastMonthLabel'] ?? '') ?: null,
            'is_published'          => ($body['publishState'] ?? '') === 'publish',
            'is_featured_home'      => (bool) ($body['featured'] ?? false),
            'published_at'          => ($body['publishState'] ?? '') === 'publish' ? now() : null,
        ]);

        // Replace related rows
        $product->media()->delete();
        $product->variants()->delete();
        $product->specs()->delete();
        $product->bullets()->delete();

        foreach (array_values($body['media'] ?? []) as $i => $item) {
            if (empty($item['url'])) {
                continue;
            }
            $product->media()->create([
                'id'         => (string) Str::uuid(),
                'kind'       => $item['kind'] ?? 'image',
                'url'        => $item['url'],
                'alt_text'   => $item['altText'] ?? $product->name,
                'is_primary' => $i === 0,
                'sort_order' => $i,
            ]);
        }

        foreach ($validVariants as $i => $v) {
            $product->variants()->create([
                'id'               => (string) Str::uuid(),
                'option_name'      => 'Variant',
                'option_value'     => trim($v['label'] ?? '') ?: "Option " . ($i + 1),
                'sku'              => trim($v['sku'] ?? '') ?: null,
                'price'            => $this->parseDecimal($v['price']),
                'compare_at_price' => $this->parseDecimal($v['compareAtPrice'] ?? ''),
                'stock_qty'        => $this->parseInt($v['stockQty'] ?? ''),
                'is_default'       => $i === 0,
                'is_active'        => true,
                'sort_order'       => $i,
            ]);
        }

        foreach (array_values($body['specs'] ?? []) as $i => $spec) {
            if (empty(trim($spec['label'] ?? '')) || empty(trim($spec['value'] ?? ''))) {
                continue;
            }
            $product->specs()->create([
                'id'         => (string) Str::uuid(),
                'spec_name'  => trim($spec['label']),
                'spec_value' => trim($spec['value']),
                'sort_order' => $i,
            ]);
        }

        foreach (array_values($body['bullets'] ?? []) as $i => $bullet) {
            if (empty(trim($bullet ?? ''))) {
                continue;
            }
            $product->bullets()->create([
                'id'          => (string) Str::uuid(),
                'bullet_text' => trim($bullet),
                'sort_order'  => $i,
            ]);
        }

        return response()->json([
            'product' => ['id' => $product->id, 'slug' => $product->slug, 'isPublished' => $product->is_published],
        ]);
    }

    /**
     * DELETE /api/admin/products/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json(['error' => 'Product not found.'], 404);
        }

        $product->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * GET /api/admin/products/export
     * Export all products as CSV.
     */
    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $products = Product::with(['category', 'variants', 'media'])->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="products-export.csv"',
        ];

        $columns = [
            'id', 'name', 'slug', 'brand', 'category', 'currency_code',
            'list_price', 'sale_price', 'is_published', 'is_featured_home',
            'shipping_label', 'delivery_label', 'returns_label', 'payment_label',
            'short_description', 'description', 'created_at',
        ];

        return response()->streamDownload(function () use ($products, $columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);

            foreach ($products as $p) {
                fputcsv($out, [
                    $p->id,
                    $p->name,
                    $p->slug,
                    $p->brand,
                    $p->category?->name,
                    $p->currency_code,
                    $p->list_price,
                    $p->sale_price,
                    $p->is_published ? '1' : '0',
                    $p->is_featured_home ? '1' : '0',
                    $p->shipping_label,
                    $p->delivery_label,
                    $p->returns_label,
                    $p->payment_label,
                    $p->short_description,
                    $p->description,
                    $p->created_at,
                ]);
            }

            fclose($out);
        }, 'products-export.csv', $headers);
    }

    /**
     * POST /api/admin/products/import
     * Import products from a CSV file.
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt']);

        $file    = $request->file('file');
        $handle  = fopen($file->getRealPath(), 'r');
        $headers = fgetcsv($handle);

        if (! $headers) {
            return response()->json(['error' => 'Empty or invalid CSV file.'], 400);
        }

        $storeId = $this->getDefaultStoreId();
        $created = 0;
        $errors  = [];

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($headers, $row);

            $name = trim($data['name'] ?? '');
            $slug = trim($data['slug'] ?? '');

            if (! $name || ! $slug) {
                $errors[] = "Row skipped (missing name or slug): " . json_encode($data);
                continue;
            }

            $slug = $this->slugify($slug);

            if (Product::where('slug', $slug)->exists()) {
                $errors[] = "Slug '{$slug}' already exists, skipped.";
                continue;
            }

            $categoryId = $this->getCategoryId($data['category'] ?? null);

            Product::create([
                'id'               => (string) Str::uuid(),
                'store_id'         => $storeId,
                'category_id'      => $categoryId,
                'slug'             => $slug,
                'name'             => $name,
                'brand'            => trim($data['brand'] ?? '') ?: null,
                'currency_code'    => trim($data['currency_code'] ?? '') ?: 'UGX',
                'list_price'       => $this->parseDecimal($data['list_price'] ?? ''),
                'sale_price'       => $this->parseDecimal($data['sale_price'] ?? ''),
                'is_published'     => ($data['is_published'] ?? '0') === '1',
                'is_featured_home' => ($data['is_featured_home'] ?? '0') === '1',
                'shipping_label'   => trim($data['shipping_label'] ?? '') ?: null,
                'delivery_label'   => trim($data['delivery_label'] ?? '') ?: null,
                'returns_label'    => trim($data['returns_label'] ?? '') ?: null,
                'payment_label'    => trim($data['payment_label'] ?? '') ?: 'Secure transaction',
                'short_description'=> trim($data['short_description'] ?? '') ?: null,
                'description'      => trim($data['description'] ?? '') ?: null,
            ]);

            $created++;
        }

        fclose($handle);

        return response()->json(['ok' => true, 'created' => $created, 'errors' => $errors]);
    }

    // ─── helpers ──────────────────────────────────────────────

    private function slugify(string $value): string
    {
        return preg_replace('/^-+|-+$/', '', preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($value))));
    }

    private function parseDecimal(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        $parsed = (float) $value;
        return is_finite($parsed) ? number_format($parsed, 2, '.', '') : null;
    }

    private function parseInt(?string $value): int
    {
        if ($value === null) {
            return 0;
        }
        $parsed = (int) $value;
        return is_finite($parsed) ? $parsed : 0;
    }

    private function getDefaultStoreId(): string
    {
        $store = Store::where('is_active', true)->orderBy('created_at')->first();

        if ($store) {
            return $store->id;
        }

        $store = Store::create([
            'id'            => (string) Str::uuid(),
            'name'          => 'Modern Electronics Ltd',
            'slug'          => 'modern-electronics',
            'support_email' => 'admin@modern.co.ug',
            'is_active'     => true,
        ]);

        return $store->id;
    }

    private function getCategoryId(?string $name, ?string $id = null): ?string
    {
        if ($id && Category::where('id', $id)->exists()) {
            return $id;
        }

        if (! $name || $name === 'Select Category') {
            return null;
        }

        $slug     = $this->slugify($name);
        $category = Category::where('slug', $slug)->first();

        if ($category) {
            return $category->id;
        }

        $category = Category::create([
            'id'        => (string) Str::uuid(),
            'name'      => $name,
            'slug'      => $slug,
            'is_active' => true,
        ]);

        return $category->id;
    }

    private function mapListProduct(Product $p): array
    {
        $variant  = $p->variants->first();
        $media    = $p->media->first();
        $price    = $variant ? (float) $variant->price : (float) ($p->sale_price ?? $p->list_price ?? 0);

        return [
            'id'            => $p->id,
            'name'          => $p->name,
            'slug'          => $p->slug,
            'brand'         => $p->brand,
            'category'      => $p->category?->name,
            'currencyCode'  => $p->currency_code,
            'price'         => $price,
            'isPublished'   => (bool) $p->is_published,
            'isFeaturedHome'=> (bool) $p->is_featured_home,
            'image'         => $media?->url ?? '',
            'createdAt'     => $p->created_at,
        ];
    }
}
