@extends('admin.layout')

@section('title', 'All Products')

@section('content')
{{-- Safe JSON data — avoids HTML-attribute encoding issues --}}
<script id="products-json" type="application/json">{!! json_encode($products->values()) !!}</script>

<div class="bg-[#f7f7f8] -mx-4 -mt-8 min-h-screen px-4 py-8 md:-mx-6 md:px-6 xl:-mx-10 xl:px-10"
     x-data="productsApp()">

    {{-- ── Page Header ── --}}
    <div class="mx-auto max-w-[1440px]">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-4">
                <span class="mt-3 h-3 w-10 shrink-0 rounded-full bg-[#0b63ce]"></span>
                <div>
                    <h1 class="text-[32px] font-bold tracking-tight text-gray-900">All Products</h1>
                    <p class="mt-1.5 text-[16px] font-medium text-gray-500">Manage your catalog, stock levels, and product availability.</p>
                </div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white px-5 py-3 shadow-sm">
                <div class="text-[12px] font-bold uppercase tracking-wider text-gray-400">Total Products</div>
                <div class="text-[24px] font-bold text-gray-900" x-text="allProducts.length"></div>
            </div>
        </div>

        <div class="space-y-6">

            {{-- ── Notice ── --}}
            <div x-show="notice" x-cloak x-transition
                 :class="notice?.tone === 'success' ? 'border-[#bbf7d0] bg-[#f0fdf4] text-[#166534]' : 'border-[#fecaca] bg-[#fef2f2] text-[#b91c1c]'"
                 class="rounded-2xl border px-4 py-3 text-sm font-medium">
                <span x-text="notice?.text"></span>
            </div>

            {{-- ── Search + Filter Bar ── --}}
            <section class="rounded-[28px] border border-[#e3e6ea] bg-white p-5 shadow-[0_18px_45px_rgba(15,23,42,0.05)] sm:p-6">
                <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                    <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_220px]">
                        {{-- Search --}}
                        <div class="relative flex items-center overflow-hidden rounded-full border border-[#d5d9d9] bg-[#f7f8fa] px-4 focus-within:border-[#f59e0b] focus-within:bg-white">
                            <i data-lucide="search" class="h-4 w-4 shrink-0 text-gray-400"></i>
                            <input type="text"
                                   x-model="search"
                                   @input="currentPage = 1"
                                   placeholder="Search by product name or slug"
                                   class="h-12 w-full bg-transparent px-3 text-[14px] text-gray-700 outline-none placeholder:text-gray-400" />
                        </div>
                        {{-- Category filter --}}
                        <select x-model="filterCategory"
                                @change="currentPage = 1"
                                class="h-12 rounded-full border border-[#d5d9d9] bg-white px-4 text-[14px] text-gray-700 outline-none hover:border-[#aab7c4] focus:border-[#f59e0b]">
                            <template x-for="cat in categories" :key="cat">
                                <option :value="cat" x-text="cat"></option>
                            </template>
                        </select>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('dashboard.products.add') }}"
                           class="inline-flex h-12 items-center gap-2 rounded-full bg-[#131921] px-5 text-[14px] font-bold text-white transition hover:bg-black">
                            <i data-lucide="plus" class="h-4 w-4"></i>
                            Add Product
                        </a>
                    </div>
                </div>
            </section>

            {{-- ── Stat Cards ── --}}
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-[24px] bg-gradient-to-br from-[#111827] to-[#1f2937] p-5 text-white shadow-[0_14px_32px_rgba(15,23,42,0.08)]">
                    <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-white/70">Catalog Items</div>
                    <div class="mt-3 text-[30px] font-bold tracking-tight" x-text="allProducts.length"></div>
                </div>
                <div class="rounded-[24px] bg-gradient-to-br from-[#065f46] to-[#10b981] p-5 text-white shadow-[0_14px_32px_rgba(15,23,42,0.08)]">
                    <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-white/70">Published</div>
                    <div class="mt-3 text-[30px] font-bold tracking-tight" x-text="stats.published"></div>
                </div>
                <div class="rounded-[24px] bg-gradient-to-br from-[#92400e] to-[#f59e0b] p-5 text-white shadow-[0_14px_32px_rgba(15,23,42,0.08)]">
                    <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-white/70">Featured</div>
                    <div class="mt-3 text-[30px] font-bold tracking-tight" x-text="stats.featured"></div>
                </div>
                <div class="rounded-[24px] bg-gradient-to-br from-[#7f1d1d] to-[#ef4444] p-5 text-white shadow-[0_14px_32px_rgba(15,23,42,0.08)]">
                    <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-white/70">Low Stock</div>
                    <div class="mt-3 text-[30px] font-bold tracking-tight" x-text="stats.lowStock"></div>
                </div>
            </section>

            {{-- ── Product Table / Cards ── --}}
            <section class="overflow-hidden rounded-[30px] border border-[#e3e6ea] bg-white shadow-[0_20px_50px_rgba(15,23,42,0.06)]">

                {{-- Table header --}}
                <div class="border-b border-[#edf0f2] bg-gradient-to-b from-white to-[#fafbfc] px-5 py-4 sm:px-6">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-[20px] font-bold text-[#111827]">Product inventory</h2>
                            <p class="text-[13px] text-gray-500">
                                Showing <span x-text="visibleStart"></span>–<span x-text="visibleEnd"></span>
                                of <span x-text="filtered.length"></span> matching products
                            </p>
                        </div>
                        <div class="inline-flex rounded-full bg-[#f3f4f6] px-3 py-1 text-[12px] font-semibold text-gray-600">
                            Page <span x-text="safePage" class="mx-1"></span> of <span x-text="totalPages"></span>
                        </div>
                    </div>
                </div>

                {{-- Empty state --}}
                <div x-show="paginated.length === 0" class="px-6 py-20 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#f3f4f6] text-gray-400">
                        <i data-lucide="search" class="h-7 w-7"></i>
                    </div>
                    <h3 class="mt-4 text-[18px] font-bold text-gray-900">No products found</h3>
                    <p class="mt-2 text-[14px] text-gray-500">Adjust the search or category filter to find products faster.</p>
                </div>

                {{-- Desktop table (xl+) --}}
                <div x-show="paginated.length > 0" class="hidden xl:block">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-[#edf0f2] bg-[#fafafa] text-[11px] font-bold uppercase tracking-[0.18em] text-gray-400">
                                <th class="px-6 py-4">Product</th>
                                <th class="px-6 py-4">Category</th>
                                <th class="px-6 py-4">Price</th>
                                <th class="px-6 py-4">Stock</th>
                                <th class="px-6 py-4">State</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(product, i) in paginated" :key="product.id">
                                <tr class="border-b border-[#f2f4f7] last:border-b-0 hover:bg-[#fcfcfd]">
                                    {{-- Product identity --}}
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-4">
                                            <div class="h-[72px] w-[72px] shrink-0 overflow-hidden rounded-2xl border border-[#e5e7eb] bg-white">
                                                <template x-if="product.image">
                                                    <img :src="product.image" :alt="product.name" class="h-full w-full object-contain p-2" />
                                                </template>
                                                <template x-if="!product.image">
                                                    <div class="flex h-full w-full items-center justify-center text-gray-300">
                                                        <i data-lucide="image" class="h-6 w-6"></i>
                                                    </div>
                                                </template>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="line-clamp-1 text-[15px] font-bold leading-6 text-[#111827]" x-text="product.name"></div>
                                                <div class="mt-1 text-[12px] text-gray-400" x-text="product.slug"></div>
                                            </div>
                                        </div>
                                    </td>
                                    {{-- Category --}}
                                    <td class="px-6 py-5">
                                        <span class="inline-flex rounded-full bg-[#f3f4f6] px-3 py-1 text-[12px] font-semibold text-gray-600" x-text="product.category"></span>
                                    </td>
                                    {{-- Price --}}
                                    <td class="px-6 py-5 text-[15px] font-bold text-[#111827]" x-text="formatCurrency(product.price)"></td>
                                    {{-- Stock --}}
                                    <td class="px-6 py-5">
                                        <span :class="{
                                                'bg-[#fef2f2] text-[#b91c1c]': product.stock <= 0,
                                                'bg-[#fff7ed] text-[#c2410c]': product.stock > 0 && product.stock <= 5,
                                                'bg-[#f0fdf4] text-[#166534]': product.stock > 5
                                              }"
                                              class="inline-flex rounded-full px-3 py-1 text-[12px] font-semibold">
                                            <span x-text="product.stock <= 0 ? 'Out of stock' : product.stock + ' in stock'"></span>
                                        </span>
                                    </td>
                                    {{-- Status pills --}}
                                    <td class="px-6 py-5">
                                        <div class="flex flex-wrap gap-2">
                                            <span :class="product.isPublished ? 'border-[#86efac] bg-[#f0fdf4] text-[#166534]' : 'border-[#e5e7eb] bg-[#f3f4f6] text-gray-500'"
                                                  class="inline-flex rounded-full border px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em]">
                                                <span x-text="product.isPublished ? 'Published' : 'Draft'"></span>
                                            </span>
                                            <span :class="product.isFeatured ? 'border-[#fcd34d] bg-[#fff7d6] text-[#92400e]' : 'border-[#e5e7eb] bg-[#f3f4f6] text-gray-500'"
                                                  class="inline-flex rounded-full border px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em]">
                                                <span x-text="product.isFeatured ? 'Featured' : 'Standard'"></span>
                                            </span>
                                        </div>
                                    </td>
                                    {{-- Actions --}}
                                    <td class="px-6 py-5">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <a :href="`/dashboard/products/${product.id}`"
                                               class="inline-flex h-10 items-center gap-1.5 rounded-full border border-[#d5d9d9] bg-white px-4 text-[13px] font-semibold text-gray-700 hover:bg-[#f8f9fb]">
                                                <i data-lucide="eye" class="h-4 w-4"></i> View
                                            </a>
                                            <a :href="`/dashboard/products/add?edit=${product.id}`"
                                               class="inline-flex h-10 items-center gap-1.5 rounded-full border border-[#d5d9d9] bg-white px-4 text-[13px] font-semibold text-gray-700 hover:bg-[#f8f9fb]">
                                                <i data-lucide="pencil" class="h-4 w-4"></i> Edit
                                            </a>
                                            <button type="button"
                                                    @click="toggleFeatured(product)"
                                                    :disabled="busy === `featured:${product.id}`"
                                                    :class="product.isFeatured ? 'border-[#fcd34d] bg-[#fff7d6] text-[#92400e]' : 'border-[#d5d9d9] bg-white text-gray-700 hover:bg-[#f8f9fb]'"
                                                    class="inline-flex h-10 items-center gap-1.5 rounded-full border px-4 text-[13px] font-semibold transition disabled:opacity-50">
                                                <i data-lucide="star" class="h-4 w-4"></i>
                                                <span x-text="product.isFeatured ? 'Unfeature' : 'Feature'"></span>
                                            </button>
                                            <button type="button"
                                                    @click="togglePublish(product)"
                                                    :disabled="busy === `publish:${product.id}`"
                                                    :class="product.isPublished ? 'border-[#fdba74] bg-[#fff7ed] text-[#c2410c]' : 'border-[#86efac] bg-[#f0fdf4] text-[#166534]'"
                                                    class="inline-flex h-10 items-center justify-center rounded-full border px-4 text-[13px] font-semibold transition disabled:opacity-50">
                                                <span x-text="product.isPublished ? 'Unpublish' : 'Publish'"></span>
                                            </button>
                                            <button type="button"
                                                    @click="deleteProduct(product)"
                                                    :disabled="busy === `delete:${product.id}`"
                                                    class="inline-flex h-10 items-center gap-1.5 rounded-full border border-[#fecaca] bg-[#fef2f2] px-4 text-[13px] font-semibold text-[#b91c1c] transition hover:bg-[#fee2e2] disabled:opacity-50">
                                                <i data-lucide="trash-2" class="h-4 w-4"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Mobile cards (below xl) --}}
                <div x-show="paginated.length > 0" class="grid gap-4 p-4 sm:p-5 xl:hidden">
                    <template x-for="product in paginated" :key="product.id">
                        <article class="rounded-[24px] border border-[#e7ebef] bg-[#fcfcfd] p-4 shadow-[0_10px_24px_rgba(15,23,42,0.04)]">
                            <div class="flex items-center gap-4">
                                <div class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl border border-[#e5e7eb] bg-white">
                                    <template x-if="product.image">
                                        <img :src="product.image" :alt="product.name" class="h-full w-full object-contain p-2" />
                                    </template>
                                    <template x-if="!product.image">
                                        <div class="flex h-full w-full items-center justify-center text-gray-300">
                                            <i data-lucide="image" class="h-5 w-5"></i>
                                        </div>
                                    </template>
                                </div>
                                <div class="min-w-0">
                                    <div class="line-clamp-1 text-[14px] font-bold text-[#111827]" x-text="product.name"></div>
                                    <div class="mt-1 text-[12px] text-gray-400" x-text="product.slug"></div>
                                </div>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-3 text-[13px]">
                                <div class="rounded-2xl bg-white px-4 py-3">
                                    <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-gray-400">Category</div>
                                    <div class="mt-1 font-semibold text-gray-700" x-text="product.category"></div>
                                </div>
                                <div class="rounded-2xl bg-white px-4 py-3">
                                    <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-gray-400">Price</div>
                                    <div class="mt-1 font-semibold text-gray-700" x-text="formatCurrency(product.price)"></div>
                                </div>
                                <div class="rounded-2xl bg-white px-4 py-3">
                                    <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-gray-400">Stock</div>
                                    <div class="mt-1 font-semibold text-gray-700" x-text="product.stock + ' units'"></div>
                                </div>
                                <div class="rounded-2xl bg-white px-4 py-3">
                                    <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-gray-400">Added</div>
                                    <div class="mt-1 font-semibold text-gray-700" x-text="product.createdAt"></div>
                                </div>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <span :class="product.isPublished ? 'border-[#86efac] bg-[#f0fdf4] text-[#166534]' : 'border-[#e5e7eb] bg-[#f3f4f6] text-gray-500'"
                                      class="inline-flex rounded-full border px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em]">
                                    <span x-text="product.isPublished ? 'Published' : 'Draft'"></span>
                                </span>
                                <span :class="product.isFeatured ? 'border-[#fcd34d] bg-[#fff7d6] text-[#92400e]' : 'border-[#e5e7eb] bg-[#f3f4f6] text-gray-500'"
                                      class="inline-flex rounded-full border px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em]">
                                    <span x-text="product.isFeatured ? 'Featured' : 'Standard'"></span>
                                </span>
                            </div>
                            <div class="mt-4 flex flex-col gap-2">
                                <div class="flex gap-2">
                                    <a :href="`/dashboard/products/${product.id}`"
                                       class="inline-flex h-10 flex-1 items-center justify-center gap-2 rounded-full border border-[#d5d9d9] bg-white text-[13px] font-semibold text-gray-700 hover:bg-[#f8f9fb]">
                                        <i data-lucide="eye" class="h-4 w-4"></i> View
                                    </a>
                                    <a :href="`/dashboard/products/add?edit=${product.id}`"
                                       class="inline-flex h-10 flex-1 items-center justify-center gap-2 rounded-full border border-[#d5d9d9] bg-white text-[13px] font-semibold text-gray-700 hover:bg-[#f8f9fb]">
                                        <i data-lucide="pencil" class="h-4 w-4"></i> Edit
                                    </a>
                                </div>
                                <div class="flex gap-2">
                                    <button @click="toggleFeatured(product)" :disabled="busy===`featured:${product.id}`"
                                            :class="product.isFeatured?'border-[#fcd34d] bg-[#fff7d6] text-[#92400e]':'border-[#d5d9d9] bg-white text-gray-700'"
                                            class="inline-flex h-10 flex-1 items-center justify-center gap-2 rounded-full border text-[13px] font-semibold disabled:opacity-50">
                                        <i data-lucide="star" class="h-4 w-4"></i>
                                        <span x-text="product.isFeatured?'Unfeature':'Feature'"></span>
                                    </button>
                                    <button @click="togglePublish(product)" :disabled="busy===`publish:${product.id}`"
                                            :class="product.isPublished?'border-[#fdba74] bg-[#fff7ed] text-[#c2410c]':'border-[#86efac] bg-[#f0fdf4] text-[#166534]'"
                                            class="inline-flex h-10 flex-1 items-center justify-center rounded-full border text-[13px] font-semibold disabled:opacity-50">
                                        <span x-text="product.isPublished?'Unpublish':'Publish'"></span>
                                    </button>
                                    <button @click="deleteProduct(product)" :disabled="busy===`delete:${product.id}`"
                                            class="inline-flex h-10 flex-1 items-center justify-center gap-2 rounded-full border border-[#fecaca] bg-[#fef2f2] text-[13px] font-semibold text-[#b91c1c] disabled:opacity-50">
                                        <i data-lucide="trash-2" class="h-4 w-4"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </article>
                    </template>
                </div>

                {{-- Pagination footer --}}
                <div class="flex flex-col gap-3 border-t border-[#edf0f2] bg-[#fafbfc] px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <p class="text-[13px] text-gray-500">
                        <template x-if="filtered.length === 0"><span>No products match the current filters.</span></template>
                        <template x-if="filtered.length > 0">
                            <span>Showing <span x-text="visibleStart"></span>–<span x-text="visibleEnd"></span> of <span x-text="filtered.length"></span> products</span>
                        </template>
                    </p>
                    <div class="flex flex-wrap items-center gap-2">
                        <button @click="currentPage = Math.max(1, safePage - 1)"
                                :disabled="safePage === 1"
                                class="inline-flex h-10 items-center justify-center rounded-full border border-[#d5d9d9] bg-white px-4 text-[13px] font-semibold text-gray-700 hover:bg-[#f8f9fb] disabled:opacity-40">
                            Previous
                        </button>
                        <template x-for="page in pages" :key="page">
                            <button @click="currentPage = page"
                                    :class="page === safePage ? 'bg-[#f59e0b] text-[#111827]' : 'border border-[#d5d9d9] bg-white text-gray-700 hover:bg-[#f8f9fb]'"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-full text-[13px] font-bold"
                                    x-text="page">
                            </button>
                        </template>
                        <button @click="currentPage = Math.min(totalPages, safePage + 1)"
                                :disabled="safePage === totalPages"
                                class="inline-flex h-10 items-center justify-center rounded-full border border-[#d5d9d9] bg-white px-4 text-[13px] font-semibold text-gray-700 hover:bg-[#f8f9fb] disabled:opacity-40">
                            Next
                        </button>
                    </div>
                </div>
            </section>

        </div>{{-- /space-y-6 --}}
    </div>{{-- /max-w --}}
</div>

@endsection

@push('scripts')
<script>
function productsApp() {
    const el = document.getElementById('products-json');
    const initialProducts = el ? JSON.parse(el.textContent) : [];
    return {
        allProducts: initialProducts,
        search: '',
        filterCategory: 'All',
        currentPage: 1,
        pageSize: 12,
        notice: null,
        busy: null,
        token: '{{ session('admin_token') }}',

        get categories() {
            const cats = new Set(this.allProducts.map(p => p.category));
            return ['All', ...cats];
        },

        get filtered() {
            return this.allProducts.filter(p => {
                const q = this.search.trim().toLowerCase();
                const matchSearch = !q || p.name.toLowerCase().includes(q) || p.slug.toLowerCase().includes(q);
                const matchCat = this.filterCategory === 'All' || p.category === this.filterCategory;
                return matchSearch && matchCat;
            });
        },

        get totalPages() { return Math.max(1, Math.ceil(this.filtered.length / this.pageSize)); },
        get safePage()   { return Math.min(this.currentPage, this.totalPages); },

        get paginated() {
            const start = (this.safePage - 1) * this.pageSize;
            return this.filtered.slice(start, start + this.pageSize);
        },

        get stats() {
            return {
                published: this.allProducts.filter(p => p.isPublished).length,
                featured:  this.allProducts.filter(p => p.isFeatured).length,
                lowStock:  this.allProducts.filter(p => p.stock > 0 && p.stock <= 5).length,
            };
        },

        get visibleStart() { return this.filtered.length === 0 ? 0 : (this.safePage - 1) * this.pageSize + 1; },
        get visibleEnd()   { return Math.min(this.filtered.length, this.safePage * this.pageSize); },

        get pages() {
            const start = Math.max(1, this.safePage - 2);
            const end   = Math.min(this.totalPages, start + 4);
            return Array.from({length: end - start + 1}, (_, i) => start + i);
        },

        formatCurrency(value) {
            return 'UGX ' + new Intl.NumberFormat('en-UG').format(value);
        },

        async apiCall(method, path, body) {
            const res = await fetch(`/api${path}`, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${this.token}`,
                },
                body: body ? JSON.stringify(body) : undefined,
            });
            if (!res.ok) throw new Error('Request failed');
            return res.json();
        },

        showNotice(tone, text) {
            this.notice = { tone, text };
            setTimeout(() => this.notice = null, 3500);
        },

        async togglePublish(product) {
            this.busy = `publish:${product.id}`;
            try {
                await this.apiCall('PATCH', `/admin/products/${product.id}`, {
                    action: 'set_publish', isPublished: !product.isPublished,
                });
                product.isPublished = !product.isPublished;
                this.showNotice('success', product.isPublished ? 'Product published.' : 'Product moved to draft.');
            } catch { this.showNotice('error', 'Failed to update status.'); }
            finally  { this.busy = null; }
        },

        async toggleFeatured(product) {
            this.busy = `featured:${product.id}`;
            try {
                await this.apiCall('PATCH', `/admin/products/${product.id}`, {
                    action: 'set_featured', isFeatured: !product.isFeatured,
                });
                product.isFeatured = !product.isFeatured;
                this.showNotice('success', product.isFeatured ? 'Product featured.' : 'Product unfeatured.');
            } catch { this.showNotice('error', 'Failed to update featured state.'); }
            finally  { this.busy = null; }
        },

        async deleteProduct(product) {
            if (!confirm(`Delete "${product.name}"?\nThis action cannot be undone.`)) return;
            this.busy = `delete:${product.id}`;
            try {
                await this.apiCall('DELETE', `/admin/products/${product.id}`);
                this.allProducts = this.allProducts.filter(p => p.id !== product.id);
                this.showNotice('success', 'Product deleted successfully.');
            } catch { this.showNotice('error', 'Failed to delete product.'); }
            finally  { this.busy = null; }
        },

        init() {
            // Re-run Lucide icons after Alpine renders dynamic content
            this.$nextTick(() => lucide.createIcons());
        },
    };
}
</script>
@endpush
