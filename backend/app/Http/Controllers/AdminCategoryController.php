<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::with('parent')
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => [
                'id'              => $c->id,
                'name'            => $c->name,
                'slug'            => $c->slug,
                'description'     => $c->description,
                'imageUrl'        => $c->image_url,
                'isActive'        => (bool) $c->is_active,
                'featuredOnHome'  => (bool) $c->featured_on_home,
                'sortOrder'       => (int) $c->featured_sort_order,
                'parentId'        => $c->parent_id,
                'parentName'      => $c->parent?->name,
            ]);

        return response()->json(['categories' => $categories]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'slug'            => ['nullable', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'imageUrl'        => ['nullable', 'string', 'max:2048'],
            'parentId'        => ['nullable', 'string', 'exists:categories,id'],
            'isActive'        => ['boolean'],
            'featuredOnHome'  => ['boolean'],
            'sortOrder'       => ['integer', 'min:0'],
        ]);

        $slug = $validated['slug']
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        // Ensure slug uniqueness
        $base = $slug;
        $i    = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        $category = Category::create([
            'name'                => $validated['name'],
            'slug'                => $slug,
            'description'         => $validated['description'] ?? null,
            'image_url'           => $validated['imageUrl'] ?? null,
            'parent_id'           => $validated['parentId'] ?? null,
            'is_active'           => $validated['isActive'] ?? true,
            'featured_on_home'    => $validated['featuredOnHome'] ?? false,
            'featured_sort_order' => $validated['sortOrder'] ?? 0,
        ]);

        return response()->json([
            'category' => [
                'id'             => $category->id,
                'name'           => $category->name,
                'slug'           => $category->slug,
                'description'    => $category->description,
                'imageUrl'       => $category->image_url,
                'isActive'       => (bool) $category->is_active,
                'featuredOnHome' => (bool) $category->featured_on_home,
                'sortOrder'      => (int) $category->featured_sort_order,
                'parentId'       => $category->parent_id,
                'parentName'     => null,
            ],
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name'            => ['sometimes', 'required', 'string', 'max:255'],
            'slug'            => ['nullable', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'imageUrl'        => ['nullable', 'string', 'max:2048'],
            'parentId'        => ['nullable', 'string', 'exists:categories,id'],
            'isActive'        => ['boolean'],
            'featuredOnHome'  => ['boolean'],
            'sortOrder'       => ['integer', 'min:0'],
        ]);

        if (isset($validated['name']) || isset($validated['slug'])) {
            $newSlug = isset($validated['slug'])
                ? Str::slug($validated['slug'])
                : Str::slug($validated['name'] ?? $category->name);

            if ($newSlug !== $category->slug) {
                $base = $newSlug;
                $i    = 1;
                while (Category::where('slug', $newSlug)->where('id', '!=', $id)->exists()) {
                    $newSlug = $base . '-' . $i++;
                }
                $validated['slug'] = $newSlug;
            } else {
                unset($validated['slug']);
            }
        }

        $category->update([
            'name'                => $validated['name']           ?? $category->name,
            'slug'                => $validated['slug']           ?? $category->slug,
            'description'         => $validated['description']    ?? $category->description,
            'image_url'           => $validated['imageUrl']       ?? $category->image_url,
            'parent_id'           => array_key_exists('parentId', $validated) ? $validated['parentId'] : $category->parent_id,
            'is_active'           => $validated['isActive']       ?? $category->is_active,
            'featured_on_home'    => $validated['featuredOnHome'] ?? $category->featured_on_home,
            'featured_sort_order' => $validated['sortOrder']      ?? $category->featured_sort_order,
        ]);

        return response()->json(['message' => 'Category updated.']);
    }

    public function destroy(string $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        // Re-parent children to this category's parent (or null)
        Category::where('parent_id', $id)->update(['parent_id' => $category->parent_id]);

        $category->delete();

        return response()->json(['message' => 'Category deleted.']);
    }
}
