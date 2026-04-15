<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminOfferController extends Controller
{
    public function index(): JsonResponse
    {
        $offers = Offer::with('products:id,name,slug', 'categories:id,name,slug')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($o) => $this->format($o));

        return response()->json(['offers' => $offers]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'                => ['required', 'string', 'max:191'],
            'headline'            => ['nullable', 'string', 'max:191'],
            'description'         => ['nullable', 'string'],
            'badge_text'          => ['nullable', 'string', 'max:64'],
            'banner_image'        => ['nullable', 'string', 'max:500'],
            'code'                => ['nullable', 'string', 'max:64', 'unique:offers,code'],
            'type'                => ['required', 'in:percentage,fixed,free_shipping'],
            'value'               => ['required_unless:type,free_shipping', 'nullable', 'numeric', 'min:0'],
            'min_order_amount'    => ['nullable', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'starts_at'           => ['nullable', 'date'],
            'expires_at'          => ['nullable', 'date'],
            'usage_limit'         => ['nullable', 'integer', 'min:1'],
            'is_active'           => ['boolean'],
            'is_featured'         => ['boolean'],
            'target_type'         => ['in:all,products,categories'],
            'target_ids'          => ['nullable', 'array'],
            'target_ids.*'        => ['uuid'],
        ]);

        $offer = Offer::create([
            'name'                => $data['name'],
            'headline'            => $data['headline'] ?? null,
            'description'         => $data['description'] ?? null,
            'badge_text'          => $data['badge_text'] ?? null,
            'banner_image'        => $data['banner_image'] ?? null,
            'code'                => isset($data['code']) ? strtoupper($data['code']) : null,
            'type'                => $data['type'],
            'value'               => $data['value'] ?? 0,
            'min_order_amount'    => $data['min_order_amount'] ?? null,
            'max_discount_amount' => $data['max_discount_amount'] ?? null,
            'starts_at'           => $data['starts_at'] ?? null,
            'expires_at'          => $data['expires_at'] ?? null,
            'usage_limit'         => $data['usage_limit'] ?? null,
            'is_active'           => $data['is_active'] ?? true,
            'is_featured'         => $data['is_featured'] ?? false,
            'target_type'         => $data['target_type'] ?? 'all',
        ]);

        $this->syncTargets($offer, $data['target_type'] ?? 'all', $data['target_ids'] ?? []);

        return response()->json(['offer' => $this->format($offer->load('products:id,name,slug', 'categories:id,name,slug'))], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $offer = Offer::findOrFail($id);

        $data = $request->validate([
            'name'                => ['sometimes', 'required', 'string', 'max:191'],
            'headline'            => ['nullable', 'string', 'max:191'],
            'description'         => ['nullable', 'string'],
            'badge_text'          => ['nullable', 'string', 'max:64'],
            'banner_image'        => ['nullable', 'string', 'max:500'],
            'code'                => ['nullable', 'string', 'max:64', 'unique:offers,code,' . $id],
            'type'                => ['sometimes', 'required', 'in:percentage,fixed,free_shipping'],
            'value'               => ['nullable', 'numeric', 'min:0'],
            'min_order_amount'    => ['nullable', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'starts_at'           => ['nullable', 'date'],
            'expires_at'          => ['nullable', 'date'],
            'usage_limit'         => ['nullable', 'integer', 'min:1'],
            'is_active'           => ['boolean'],
            'is_featured'         => ['boolean'],
            'target_type'         => ['in:all,products,categories'],
            'target_ids'          => ['nullable', 'array'],
            'target_ids.*'        => ['uuid'],
        ]);

        $offer->update([
            'name'                => $data['name']                ?? $offer->name,
            'headline'            => array_key_exists('headline', $data)     ? $data['headline']     : $offer->headline,
            'description'         => array_key_exists('description', $data)  ? $data['description']  : $offer->description,
            'badge_text'          => array_key_exists('badge_text', $data)   ? $data['badge_text']   : $offer->badge_text,
            'banner_image'        => array_key_exists('banner_image', $data) ? $data['banner_image'] : $offer->banner_image,
            'code'                => array_key_exists('code', $data) ? (isset($data['code']) ? strtoupper($data['code']) : null) : $offer->code,
            'type'                => $data['type']                ?? $offer->type,
            'value'               => $data['value']               ?? $offer->value,
            'min_order_amount'    => array_key_exists('min_order_amount', $data)    ? $data['min_order_amount']    : $offer->min_order_amount,
            'max_discount_amount' => array_key_exists('max_discount_amount', $data) ? $data['max_discount_amount'] : $offer->max_discount_amount,
            'starts_at'           => array_key_exists('starts_at', $data)           ? $data['starts_at']           : $offer->starts_at,
            'expires_at'          => array_key_exists('expires_at', $data)          ? $data['expires_at']          : $offer->expires_at,
            'usage_limit'         => array_key_exists('usage_limit', $data)         ? $data['usage_limit']         : $offer->usage_limit,
            'is_active'           => $data['is_active']           ?? $offer->is_active,
            'is_featured'         => $data['is_featured']         ?? $offer->is_featured,
            'target_type'         => $data['target_type']         ?? $offer->target_type,
        ]);

        $this->syncTargets($offer, $data['target_type'] ?? $offer->target_type, $data['target_ids'] ?? null);

        return response()->json(['message' => 'Offer updated.', 'offer' => $this->format($offer->fresh()->load('products:id,name,slug', 'categories:id,name,slug'))]);
    }

    public function toggle(string $id): JsonResponse
    {
        $offer = Offer::findOrFail($id);
        $offer->update(['is_active' => !$offer->is_active]);
        return response()->json(['offer' => $this->format($offer->fresh()->load('products:id,name,slug', 'categories:id,name,slug'))]);
    }

    public function destroy(string $id): JsonResponse
    {
        Offer::findOrFail($id)->delete();
        return response()->json(['message' => 'Offer deleted.']);
    }

    public function productSearch(Request $request): JsonResponse
    {
        $q = trim($request->query('q', ''));
        $products = Product::select('id', 'name', 'slug')
            ->when($q, fn ($query) => $query->where('name', 'like', "%{$q}%")->orWhere('slug', 'like', "%{$q}%"))
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json(['products' => $products]);
    }

    public function categoryList(): JsonResponse
    {
        $categories = Category::select('id', 'name', 'slug')
            ->orderBy('name')
            ->get();

        return response()->json(['categories' => $categories]);
    }

    private function syncTargets(Offer $offer, string $targetType, ?array $ids): void
    {
        if ($ids === null) return;

        if ($targetType === 'products') {
            $offer->products()->sync($ids);
            $offer->categories()->detach();
        } elseif ($targetType === 'categories') {
            $offer->categories()->sync($ids);
            $offer->products()->detach();
        } else {
            $offer->products()->detach();
            $offer->categories()->detach();
        }
    }

    private function format(Offer $o): array
    {
        return [
            'id'                => $o->id,
            'name'              => $o->name,
            'headline'          => $o->headline,
            'description'       => $o->description,
            'badgeText'         => $o->badge_text,
            'bannerImage'       => $o->banner_image,
            'code'              => $o->code,
            'type'              => $o->type,
            'value'             => (float) $o->value,
            'minOrderAmount'    => $o->min_order_amount ? (float) $o->min_order_amount : null,
            'maxDiscountAmount' => $o->max_discount_amount ? (float) $o->max_discount_amount : null,
            'startsAt'          => $o->starts_at?->format('Y-m-d'),
            'expiresAt'         => $o->expires_at?->format('Y-m-d'),
            'usageLimit'        => $o->usage_limit,
            'usageCount'        => $o->usage_count,
            'isActive'          => (bool) $o->is_active,
            'isFeatured'        => (bool) $o->is_featured,
            'targetType'        => $o->target_type ?? 'all',
            'targetProducts'    => $o->relationLoaded('products')
                ? $o->products->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'slug' => $p->slug])->values()
                : [],
            'targetCategories'  => $o->relationLoaded('categories')
                ? $o->categories->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug])->values()
                : [],
            'createdAt'         => $o->created_at->format('Y-m-d'),
        ];
    }
}
