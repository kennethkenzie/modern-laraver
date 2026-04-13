<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminBrandController extends Controller
{
    public function index(): JsonResponse
    {
        $brands = Brand::orderBy('sort_order')->orderBy('name')->get()
            ->map(fn ($b) => $this->format($b));

        return response()->json(['brands' => $brands]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'slug'            => ['nullable', 'string', 'max:255'],
            'logoUrl'         => ['nullable', 'string', 'max:2048'],
            'bannerUrl'       => ['nullable', 'string', 'max:2048'],
            'metaTitle'       => ['nullable', 'string', 'max:255'],
            'metaDescription' => ['nullable', 'string'],
            'isActive'        => ['boolean'],
            'isFeatured'      => ['boolean'],
            'sortOrder'       => ['integer', 'min:0'],
        ]);

        $slug = $this->uniqueSlug($data['slug'] ?? $data['name']);

        $brand = Brand::create([
            'name'             => $data['name'],
            'slug'             => $slug,
            'logo_url'         => $data['logoUrl']         ?? null,
            'banner_url'       => $data['bannerUrl']       ?? null,
            'meta_title'       => $data['metaTitle']       ?? null,
            'meta_description' => $data['metaDescription'] ?? null,
            'is_active'        => $data['isActive']        ?? true,
            'is_featured'      => $data['isFeatured']      ?? false,
            'sort_order'       => $data['sortOrder']       ?? 0,
        ]);

        return response()->json(['brand' => $this->format($brand)], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $brand = Brand::findOrFail($id);

        $data = $request->validate([
            'name'            => ['sometimes', 'required', 'string', 'max:255'],
            'slug'            => ['nullable', 'string', 'max:255'],
            'logoUrl'         => ['nullable', 'string', 'max:2048'],
            'bannerUrl'       => ['nullable', 'string', 'max:2048'],
            'metaTitle'       => ['nullable', 'string', 'max:255'],
            'metaDescription' => ['nullable', 'string'],
            'isActive'        => ['boolean'],
            'isFeatured'      => ['boolean'],
            'sortOrder'       => ['integer', 'min:0'],
        ]);

        if (isset($data['name']) || isset($data['slug'])) {
            $newSlug = $this->uniqueSlug($data['slug'] ?? $data['name'] ?? $brand->name, $id);
            $data['slug'] = $newSlug;
        }

        $brand->update([
            'name'             => $data['name']            ?? $brand->name,
            'slug'             => $data['slug']            ?? $brand->slug,
            'logo_url'         => $data['logoUrl']         ?? $brand->logo_url,
            'banner_url'       => $data['bannerUrl']       ?? $brand->banner_url,
            'meta_title'       => $data['metaTitle']       ?? $brand->meta_title,
            'meta_description' => $data['metaDescription'] ?? $brand->meta_description,
            'is_active'        => $data['isActive']        ?? $brand->is_active,
            'is_featured'      => $data['isFeatured']      ?? $brand->is_featured,
            'sort_order'       => $data['sortOrder']       ?? $brand->sort_order,
        ]);

        return response()->json(['message' => 'Brand updated.', 'brand' => $this->format($brand->fresh())]);
    }

    public function destroy(string $id): JsonResponse
    {
        Brand::findOrFail($id)->delete();
        return response()->json(['message' => 'Brand deleted.']);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function format(Brand $b): array
    {
        return [
            'id'              => $b->id,
            'name'            => $b->name,
            'slug'            => $b->slug,
            'logoUrl'         => $b->logo_url,
            'bannerUrl'       => $b->banner_url,
            'metaTitle'       => $b->meta_title,
            'metaDescription' => $b->meta_description,
            'isActive'        => (bool) $b->is_active,
            'isFeatured'      => (bool) $b->is_featured,
            'sortOrder'       => (int)  $b->sort_order,
            'createdAt'       => $b->created_at->format('Y-m-d'),
        ];
    }

    private function uniqueSlug(string $base, ?string $exceptId = null): string
    {
        $slug = Str::slug($base);
        $orig = $slug;
        $i    = 1;
        while (Brand::where('slug', $slug)->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))->exists()) {
            $slug = $orig . '-' . $i++;
        }
        return $slug;
    }
}
