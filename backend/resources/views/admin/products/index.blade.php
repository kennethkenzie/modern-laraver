@extends('admin.layout')

@section('title', 'All Products')

@section('content')
<script id="products-json" type="application/json">{!! json_encode($products->values()) !!}</script>

<div class="bg-[#eaeded] -mx-4 -mt-8 min-h-screen px-4 py-6 md:-mx-6 md:px-6 xl:-mx-10 xl:px-10"
     x-data="productsApp()">

    {{-- ── Page Header ── --}}
    <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-[22px] font-bold text-[#0f1111]">All Products</h1>
            <p class="mt-0.5 text-[13px] text-[#565959]">Manage your catalog, stock levels, and product availability.</p>
        </div>
        <a href="{{ route('dashboard.products.add') }}"
           class="inline-flex h-10 items-center gap-2 rounded-lg border border-[#fcd200] bg-[#ffd814] px-5 text-[14px] font-medium text-[#0f1111] shadow-[inset_0_-1px_0_rgba(0,0,0,0.15)] transition hover:bg-[#f7ca00] active:scale-[.99] flex-shrink-0">
            <i data-lucide="plus" class="h-4 w-4"></i> Add Product
        </a>
    </div>

    {{-- ── Notice ── --}}
    <div x-show="notice" x-cloak x-transition
         :class="notice?.tone === 'success'
             ? 'border-[#c3e6cb] bg-[#d4edda] text-[#155724]'
             : 'border-[#f5c6cb] bg-[#fef2f2] text-[#b12704]'"
         class="mb-4 rounded-md border px-4 py-3 text-[13px] font-medium">
        <span x-text="notice?.text"></span>
    </div>

    {{-- ── Stat Cards ── --}}
    <div class="mb-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-lg border border-[#d5d9d9] bg-white p-4 shadow-[0_1px_3px_rgba(15,17,17,0.08)]">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[#565959]">Catalog Items</span>
                <span class="flex h-7 w-7 items-center justify-center rounded-md bg-[#f0f2f2] text-[#565959]">
                    <i data-lucide="package" class="h-3.5 w-3.5"></i>
                </span>
            </div>
            <div class="mt-2.5 text-[28px] font-bold leading-none text-[#0f1111]" x-text="allProducts.length"></div>
        </div>
        <div class="rounded-lg border border-[#d5d9d9] bg-white p-4 shadow-[0_1px_3px_rgba(15,17,17,0.08)]">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[#565959]">Published</span>
                <span class="flex h-7 w-7 items-center justify-center rounded-md bg-[#eaf5f5] text-[#007185]">
                    <i data-lucide="circle-check" class="h-3.5 w-3.5"></i>
                </span>
            </div>
            <div class="mt-2.5 text-[28px] font-bold leading-none text-[#007185]" x-text="stats.published"></div>
        </div>
        <div class="rounded-lg border border-[#d5d9d9] bg-white p-4 shadow-[0_1px_3px_rgba(15,17,17,0.08)]">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[#565959]">Featured</span>
                <span class="flex h-7 w-7 items-center justify-center rounded-md bg-[#fef9e7] text-[#c45500]">
                    <i data-lucide="star" class="h-3.5 w-3.5"></i>
                </span>
            </div>
            <div class="mt-2.5 text-[28px] font-bold leading-none text-[#c45500]" x-text="stats.featured"></div>
        </div>
        <div class="rounded-lg border border-[#d5d9d9] bg-white p-4 shadow-[0_1px_3px_rgba(15,17,17,0.08)]">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[#565959]">Low Stock</span>
                <span class="flex h-7 w-7 items-center justify-center rounded-md bg-[#fef2f2] text-[#b12704]">
                    <i data-lucide="alert-triangle" class="h-3.5 w-3.5"></i>
                </span>
            </div>
            <div class="mt-2.5 text-[28px] font-bold leading-none text-[#b12704]" x-text="stats.lowStock"></div>
        </div>
    </div>

    {{-- ── Search + Filter Bar ── --}}
    <div class="mb-4 flex flex-col gap-3 rounded-lg border border-[#d5d9d9] bg-white px-4 py-3 shadow-[0_1px_3px_rgba(15,17,17,0.08)] sm:flex-row sm:items-center">
        <div class="flex flex-1 items-center gap-2.5 rounded-md border border-[#a6a6a6] px-3 py-2 shadow-[inset_0_1px_2px_rgba(15,17,17,0.06)] focus-within:border-[#007185] focus-within:shadow-[inset_0_1px_2px_rgba(15,17,17,0.06),0_0_0_3px_rgba(0,113,133,0.1)] transition-shadow">
            <i data-lucide="search" class="h-4 w-4 shrink-0 text-[#565959]"></i>
            <input type="text" x-model="search" @input="currentPage = 1"
                   placeholder="Search by product name or slug"
                   class="flex-1 bg-transparent text-[14px] text-[#0f1111] outline-none placeholder:text-[#8a8f98]" />
        </div>
        <div class="flex items-center gap-2">
            <select x-model="filterCategory" @change="currentPage = 1"
                    class="h-9 rounded-md border border-[#d5d9d9] bg-[#f0f2f2] px-3 text-[13px] text-[#0f1111] outline-none focus:border-[#007185] transition">
                <template x-for="cat in categories" :key="cat">
                    <option :value="cat" x-text="cat"></option>
                </template>
            </select>
            <span class="whitespace-nowrap rounded-md border border-[#d5d9d9] bg-[#f0f2f2] px-3 py-1.5 text-[12px] font-bold text-[#565959]"
                x-text="filtered.length + ' product' + (filtered.length !== 1 ? 's' : '')"></span>
        </div>
    </div>

    {{-- ── Product Table ── --}}
    <div class="rounded-lg border border-[#d5d9d9] bg-white shadow-[0_1px_3px_rgba(15,17,17,0.08)]">

        {{-- Empty state --}}
        <div x-show="paginated.length === 0" class="px-6 py-16 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg border border-[#d5d9d9] bg-[#f0f2f2] text-[#565959]">
                <i data-lucide="search" class="h-5 w-5"></i>
            </div>
            <h3 class="mt-4 text-[15px] font-bold text-[#0f1111]">No products found</h3>
            <p class="mt-1 text-[13px] text-[#565959]">Adjust the search or category filter to find products.</p>
        </div>

        {{-- Desktop table --}}
        <div x-show="paginated.length > 0" class="hidden overflow-x-auto xl:block">
            <table class="w-full text-left text-[14px]">
                <thead>
                    <tr class="border-b border-[#d5d9d9] bg-[#f7fafa]">
                        <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-[#565959]">Product</th>
                        <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-[#565959]">Category</th>
                        <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-[#565959]">Price</th>
                        <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-[#565959]">Stock</th>
                        <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-[#565959]">State</th>
                        <th class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-[#565959]">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0f2f2]">
                    <template x-for="product in paginated" :key="product.id">
                        <tr class="group transition hover:bg-[#f7fafa]">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-14 w-14 shrink-0 overflow-hidden rounded-md border border-[#d5d9d9] bg-white">
                                        <template x-if="product.image">
                                            <img :src="product.image" :alt="product.name" class="h-full w-full object-contain p-1.5" />
                                        </template>
                                        <template x-if="!product.image">
                                            <div class="flex h-full w-full items-center justify-center text-[#8a8f98]">
                                                <i data-lucide="image" class="h-5 w-5"></i>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="line-clamp-1 text-[14px] font-semibold text-[#0f1111]" x-text="product.name"></div>
                                        <div class="mt-0.5 text-[12px] font-mono text-[#8a8f98]" x-text="product.slug"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex rounded border border-[#d5d9d9] bg-[#f0f2f2] px-2 py-0.5 text-[12px] font-bold text-[#565959]" x-text="product.category"></span>
                            </td>
                            <td class="px-4 py-4 text-[14px] font-bold text-[#0f1111]" x-text="formatCurrency(product.price)"></td>
                            <td class="px-4 py-4">
                                <span :class="{
                                    'border-[#f5c6cb] bg-[#fef2f2] text-[#b12704]': product.stock <= 0,
                                    'border-[#f5cba7] bg-[#fef5ec] text-[#c45500]': product.stock > 0 && product.stock <= 5,
                                    'border-[#c3e6cb] bg-[#d4edda] text-[#155724]': product.stock > 5
                                  }"
                                  class="inline-flex rounded border px-2 py-0.5 text-[12px] font-bold">
                                    <span x-text="product.stock <= 0 ? 'Out of stock' : product.stock + ' in stock'"></span>
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-col gap-1">
                                    <span :class="product.isPublished
                                            ? 'border-[#c3e6cb] bg-[#d4edda] text-[#155724]'
                                            : 'border-[#d5d9d9] bg-[#f0f2f2] text-[#565959]'"
                                          class="inline-flex w-fit rounded border px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide">
                                        <span x-text="product.isPublished ? 'Published' : 'Draft'"></span>
                                    </span>
                                    <span x-show="product.isFeatured"
                                          class="inline-flex w-fit items-center gap-1 rounded border border-[#f5cba7] bg-[#fef5ec] px-2 py-0.5 text-[11px] font-bold text-[#c45500]">
                                        <i data-lucide="star" class="h-3 w-3"></i> Featured
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap justify-end gap-1.5">
                                    <a :href="`/dashboard/products/${product.id}`"
                                       class="inline-flex h-8 items-center gap-1 rounded-md border border-[#d5d9d9] bg-white px-3 text-[12px] font-bold text-[#565959] transition hover:border-[#007185] hover:text-[#007185]">
                                        <i data-lucide="eye" class="h-3.5 w-3.5"></i> View
                                    </a>
                                    <a :href="`/dashboard/products/add?edit=${product.id}`"
                                       class="inline-flex h-8 items-center gap-1 rounded-md border border-[#d5d9d9] bg-white px-3 text-[12px] font-bold text-[#565959] transition hover:border-[#007185] hover:text-[#007185]">
                                        <i data-lucide="pencil" class="h-3.5 w-3.5"></i> Edit
                                    </a>
                                    <button type="button" @click="toggleFeatured(product)"
                                            :disabled="busy === `featured:${product.id}`"
                                            :class="product.isFeatured
                                                ? 'border-[#f5cba7] bg-[#fef5ec] text-[#c45500]'
                                                : 'border-[#d5d9d9] bg-white text-[#565959] hover:border-[#f5cba7] hover:text-[#c45500]'"
                                            class="inline-flex h-8 items-center gap-1 rounded-md border px-3 text-[12px] font-bold transition disabled:opacity-50">
                                        <i data-lucide="star" class="h-3.5 w-3.5"></i>
                                        <span x-text="product.isFeatured ? 'Unfeature' : 'Feature'"></span>
                                    </button>
                                    <button type="button" @click="togglePublish(product)"
                                            :disabled="busy === `publish:${product.id}`"
                                            :class="product.isPublished
                                                ? 'border-[#d5d9d9] bg-white text-[#565959] hover:border-[#007185] hover:text-[#007185]'
                                                : 'border-[#c3e6cb] bg-[#d4edda] text-[#155724] hover:bg-[#c3e6cb]'"
                                            class="inline-flex h-8 items-center px-3 rounded-md border text-[12px] font-bold transition disabled:opacity-50">
                                        <span x-text="product.isPublished ? 'Unpublish' : 'Publish'"></span>
                                    </button>
                                    <button type="button" @click="deleteProduct(product)"
                                            :disabled="busy === `delete:${product.id}`"
                                            class="inline-flex h-8 items-center gap-1 rounded-md border border-[#d5d9d9] bg-white px-3 text-[12px] font-bold text-[#565959] transition hover:border-[#b12704] hover:text-[#b12704] disabled:opacity-50">
                                        <i data-lucide="trash-2" class="h-3.5 w-3.5"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div x-show="paginated.length > 0" class="divide-y divide-[#f0f2f2] xl:hidden">
            <template x-for="product in paginated" :key="product.id">
                <div class="p-4">
                    <div class="flex items-center gap-3">
                        <div class="h-14 w-14 shrink-0 overflow-hidden rounded-md border border-[#d5d9d9] bg-white">
                            <template x-if="product.image">
                                <img :src="product.image" :alt="product.name" class="h-full w-full object-contain p-1.5" />
                            </template>
                            <template x-if="!product.image">
                                <div class="flex h-full w-full items-center justify-center text-[#8a8f98]">
                                    <i data-lucide="image" class="h-5 w-5"></i>
                                </div>
                            </template>
                        </div>
                        <div class="min-w-0">
                            <div class="line-clamp-1 text-[14px] font-semibold text-[#0f1111]" x-text="product.name"></div>
                            <div class="mt-0.5 text-[12px] font-mono text-[#8a8f98]" x-text="product.slug"></div>
                        </div>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-2 text-[13px]">
                        <div class="rounded-md border border-[#d5d9d9] bg-[#f7fafa] px-3 py-2.5">
                            <div class="text-[11px] font-bold uppercase tracking-wide text-[#565959]">Category</div>
                            <div class="mt-0.5 font-semibold text-[#0f1111]" x-text="product.category"></div>
                        </div>
                        <div class="rounded-md border border-[#d5d9d9] bg-[#f7fafa] px-3 py-2.5">
                            <div class="text-[11px] font-bold uppercase tracking-wide text-[#565959]">Price</div>
                            <div class="mt-0.5 font-semibold text-[#0f1111]" x-text="formatCurrency(product.price)"></div>
                        </div>
                        <div class="rounded-md border border-[#d5d9d9] bg-[#f7fafa] px-3 py-2.5">
                            <div class="text-[11px] font-bold uppercase tracking-wide text-[#565959]">Stock</div>
                            <div class="mt-0.5 font-semibold text-[#0f1111]" x-text="product.stock + ' units'"></div>
                        </div>
                        <div class="rounded-md border border-[#d5d9d9] bg-[#f7fafa] px-3 py-2.5">
                            <div class="text-[11px] font-bold uppercase tracking-wide text-[#565959]">Added</div>
                            <div class="mt-0.5 font-semibold text-[#0f1111]" x-text="product.createdAt"></div>
                        </div>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        <span :class="product.isPublished ? 'border-[#c3e6cb] bg-[#d4edda] text-[#155724]' : 'border-[#d5d9d9] bg-[#f0f2f2] text-[#565959]'"
                              class="inline-flex rounded border px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide">
                            <span x-text="product.isPublished ? 'Published' : 'Draft'"></span>
                        </span>
                        <span x-show="product.isFeatured"
                              class="inline-flex items-center gap-1 rounded border border-[#f5cba7] bg-[#fef5ec] px-2 py-0.5 text-[11px] font-bold text-[#c45500]">
                            <i data-lucide="star" class="h-3 w-3"></i> Featured
                        </span>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <a :href="`/dashboard/products/${product.id}`"
                           class="inline-flex h-9 items-center justify-center gap-1 rounded-md border border-[#d5d9d9] bg-white text-[13px] font-bold text-[#565959] transition hover:bg-[#f0f2f2]">
                            <i data-lucide="eye" class="h-4 w-4"></i> View
                        </a>
                        <a :href="`/dashboard/products/add?edit=${product.id}`"
                           class="inline-flex h-9 items-center justify-center gap-1 rounded-md border border-[#d5d9d9] bg-white text-[13px] font-bold text-[#565959] transition hover:bg-[#f0f2f2]">
                            <i data-lucide="pencil" class="h-4 w-4"></i> Edit
                        </a>
                        <button @click="toggleFeatured(product)" :disabled="busy===`featured:${product.id}`"
                                :class="product.isFeatured ? 'border-[#f5cba7] bg-[#fef5ec] text-[#c45500]' : 'border-[#d5d9d9] bg-white text-[#565959]'"
                                class="inline-flex h-9 items-center justify-center gap-1 rounded-md border text-[13px] font-bold disabled:opacity-50 transition">
                            <i data-lucide="star" class="h-4 w-4"></i>
                            <span x-text="product.isFeatured ? 'Unfeature' : 'Feature'"></span>
                        </button>
                        <button @click="togglePublish(product)" :disabled="busy===`publish:${product.id}`"
                                :class="product.isPublished ? 'border-[#d5d9d9] bg-white text-[#565959]' : 'border-[#c3e6cb] bg-[#d4edda] text-[#155724]'"
                                class="inline-flex h-9 items-center justify-center rounded-md border text-[13px] font-bold disabled:opacity-50 transition">
                            <span x-text="product.isPublished ? 'Unpublish' : 'Publish'"></span>
                        </button>
                        <button @click="deleteProduct(product)" :disabled="busy===`delete:${product.id}`"
                                class="col-span-2 inline-flex h-9 items-center justify-center gap-1 rounded-md border border-[#d5d9d9] bg-white text-[13px] font-bold text-[#565959] transition hover:border-[#b12704] hover:text-[#b12704] disabled:opacity-50">
                            <i data-lucide="trash-2" class="h-4 w-4"></i> Delete
                        </button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Pagination --}}
        <div class="flex flex-col gap-3 border-t border-[#d5d9d9] bg-[#f7fafa] px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-[13px] text-[#565959]">
                <template x-if="filtered.length === 0"><span>No products match the current filters.</span></template>
                <template x-if="filtered.length > 0">
                    <span>Showing <span class="font-bold text-[#0f1111]" x-text="visibleStart"></span>–<span x-text="visibleEnd"></span> of <span x-text="filtered.length"></span> products</span>
                </template>
            </p>
            <div class="flex items-center gap-1.5">
                <button @click="currentPage = Math.max(1, safePage - 1)" :disabled="safePage === 1"
                        class="inline-flex h-8 items-center justify-center rounded-md border border-[#d5d9d9] bg-white px-3 text-[13px] font-bold text-[#565959] transition hover:bg-[#f0f2f2] disabled:opacity-40">
                    Previous
                </button>
                <template x-for="page in pages" :key="page">
                    <button @click="currentPage = page"
                            :class="page === safePage
                                ? 'border-[#fcd200] bg-[#ffd814] text-[#0f1111]'
                                : 'border-[#d5d9d9] bg-white text-[#565959] hover:bg-[#f0f2f2]'"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border text-[13px] font-bold transition"
                            x-text="page">
                    </button>
                </template>
                <button @click="currentPage = Math.min(totalPages, safePage + 1)" :disabled="safePage === totalPages"
                        class="inline-flex h-8 items-center justify-center rounded-md border border-[#d5d9d9] bg-white px-3 text-[13px] font-bold text-[#565959] transition hover:bg-[#f0f2f2] disabled:opacity-40">
                    Next
                </button>
            </div>
        </div>
    </div>
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
            this.$nextTick(() => lucide.createIcons());
        },
    };
}
</script>
@endpush
