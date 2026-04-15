@extends('admin.layout')

@section('title', 'Brands')

@section('content')
<script id="brands-json" type="application/json">{!! json_encode($brands->values()) !!}</script>

<div class="-mx-4 -mt-4 sm:-mx-6 sm:-mt-6 min-h-screen bg-[#eaeded] px-4 pt-6 pb-16 sm:px-6 sm:pt-8 lg:px-8"
     x-data="brandsApp('{{ session('admin_token') }}')"
     x-init="init()">

    {{-- ── Page Header ── --}}
    <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-[22px] font-bold text-[#0f1111]">Brands</h1>
            <p class="mt-0.5 text-[13px] text-[#565959]">Manage product brands and manufacturer details.</p>
        </div>
        <button type="button" @click="openForm(null)"
            class="inline-flex h-10 items-center gap-2 rounded-lg border border-[#fcd200] bg-[#ffd814] px-5 text-[14px] font-medium text-[#0f1111] shadow-[inset_0_-1px_0_rgba(0,0,0,0.15)] transition hover:bg-[#f7ca00] active:scale-[.99] flex-shrink-0">
            <i data-lucide="plus" class="h-4 w-4"></i> Add Brand
        </button>
    </div>

    {{-- ── Stat Cards ── --}}
    <div class="mb-5 grid gap-3 sm:grid-cols-3">
        <div class="rounded-lg border border-[#d5d9d9] bg-white p-4 shadow-[0_1px_3px_rgba(15,17,17,0.08)]">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-bold uppercase tracking-wider text-[#565959]">Total Brands</span>
                <span class="flex h-7 w-7 items-center justify-center rounded-md bg-[#f0f2f2] text-[#565959]">
                    <i data-lucide="tag" class="h-3.5 w-3.5"></i>
                </span>
            </div>
            <div class="mt-2.5 text-[30px] font-bold leading-none text-[#0f1111]" x-text="brands.length"></div>
            <div class="mt-1 text-[12px] text-[#565959]" x-text="brands.length === 1 ? '1 brand registered' : brands.length + ' brands registered'"></div>
        </div>
        <div class="rounded-lg border border-[#d5d9d9] bg-white p-4 shadow-[0_1px_3px_rgba(15,17,17,0.08)]">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-bold uppercase tracking-wider text-[#565959]">Active</span>
                <span class="flex h-7 w-7 items-center justify-center rounded-md bg-[#eaf5f5] text-[#007185]">
                    <i data-lucide="circle-check" class="h-3.5 w-3.5"></i>
                </span>
            </div>
            <div class="mt-2.5 text-[30px] font-bold leading-none text-[#007185]" x-text="brands.filter(b => b.isActive).length"></div>
            <div class="mt-1 text-[12px] text-[#565959]">Visible to shoppers</div>
        </div>
        <div class="rounded-lg border border-[#d5d9d9] bg-white p-4 shadow-[0_1px_3px_rgba(15,17,17,0.08)]">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-bold uppercase tracking-wider text-[#565959]">Featured</span>
                <span class="flex h-7 w-7 items-center justify-center rounded-md bg-[#fef9e7] text-[#c45500]">
                    <i data-lucide="star" class="h-3.5 w-3.5"></i>
                </span>
            </div>
            <div class="mt-2.5 text-[30px] font-bold leading-none text-[#c45500]" x-text="brands.filter(b => b.isFeatured).length"></div>
            <div class="mt-1 text-[12px] text-[#565959]">Highlighted on storefront</div>
        </div>
    </div>

    {{-- ── Table Card ── --}}
    <div class="rounded-lg border border-[#d5d9d9] bg-white shadow-[0_1px_3px_rgba(15,17,17,0.08)]">

        {{-- Search + filter bar --}}
        <div class="flex flex-col gap-3 border-b border-[#d5d9d9] px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-1 items-center gap-2.5 rounded-md border border-[#a6a6a6] px-3 py-2 shadow-[inset_0_1px_2px_rgba(15,17,17,0.06)] focus-within:border-[#007185] focus-within:shadow-[inset_0_1px_2px_rgba(15,17,17,0.06),0_0_0_3px_rgba(0,113,133,0.1)] transition-shadow">
                <i data-lucide="search" class="h-4 w-4 flex-shrink-0 text-[#565959]"></i>
                <input x-model="search" @input="currentPage = 1" type="text" placeholder="Search brands by name or slug…"
                    class="flex-1 bg-transparent text-[14px] text-[#0f1111] outline-none placeholder:text-[#8a8f98]" />
            </div>
            <div class="flex items-center gap-2">
                <select x-model="filterStatus" @change="currentPage = 1"
                    class="h-9 rounded-md border border-[#d5d9d9] bg-[#f0f2f2] px-3 text-[13px] text-[#0f1111] outline-none focus:border-[#007185] transition">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <select x-model="filterFeatured" @change="currentPage = 1"
                    class="h-9 rounded-md border border-[#d5d9d9] bg-[#f0f2f2] px-3 text-[13px] text-[#0f1111] outline-none focus:border-[#007185] transition">
                    <option value="">All Types</option>
                    <option value="yes">Featured</option>
                    <option value="no">Not Featured</option>
                </select>
                <span class="whitespace-nowrap rounded-md border border-[#d5d9d9] bg-[#f0f2f2] px-3 py-1.5 text-[12px] font-bold text-[#565959]"
                    x-text="filtered.length + ' result' + (filtered.length !== 1 ? 's' : '')"></span>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-[14px]">
                <thead>
                    <tr class="border-b border-[#d5d9d9] bg-[#f7fafa]">
                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#565959]">Brand</th>
                        <th class="hidden px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#565959] lg:table-cell">Slug</th>
                        <th class="hidden px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#565959] md:table-cell">Banner</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#565959]">Status</th>
                        <th class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-[#565959]">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0f2f2]">
                    <template x-for="brand in paginated" :key="brand.id">
                        <tr class="group transition hover:bg-[#f7fafa]">

                            {{-- Brand (logo + name) --}}
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3">
                                    <template x-if="brand.logoUrl">
                                        <img :src="brand.logoUrl" :alt="brand.name"
                                            class="h-9 w-9 rounded-md border border-[#d5d9d9] bg-white object-contain p-0.5 flex-shrink-0" />
                                    </template>
                                    <template x-if="!brand.logoUrl">
                                        <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-md border border-[#d5d9d9] bg-[#f0f2f2] text-[#565959]">
                                            <i data-lucide="image" class="h-4 w-4"></i>
                                        </div>
                                    </template>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-[#0f1111] truncate" x-text="brand.name"></div>
                                        <div class="text-[12px] text-[#565959] font-mono lg:hidden truncate" x-text="brand.slug"></div>
                                    </div>
                                </div>
                            </td>

                            {{-- Slug (desktop only) --}}
                            <td class="hidden px-4 py-3.5 lg:table-cell">
                                <span class="font-mono text-[12px] text-[#565959]" x-text="brand.slug"></span>
                            </td>

                            {{-- Banner thumbnail --}}
                            <td class="hidden px-4 py-3.5 md:table-cell">
                                <template x-if="brand.bannerUrl">
                                    <img :src="brand.bannerUrl" :alt="brand.name + ' banner'"
                                        class="h-7 w-20 rounded border border-[#d5d9d9] object-cover" />
                                </template>
                                <template x-if="!brand.bannerUrl">
                                    <span class="text-[12px] text-[#a6a6a6]">—</span>
                                </template>
                            </td>

                            {{-- Status badges --}}
                            <td class="px-4 py-3.5">
                                <div class="flex flex-col gap-1.5">
                                    <button @click="toggleActive(brand)"
                                        :class="brand.isActive
                                            ? 'border-[#c3e6cb] bg-[#d4edda] text-[#155724] hover:bg-[#c3e6cb]'
                                            : 'border-[#d5d9d9] bg-[#f0f2f2] text-[#565959] hover:bg-[#e3e6e6]'"
                                        class="inline-flex w-fit items-center gap-1 rounded border px-2 py-0.5 text-[12px] font-bold transition">
                                        <span :class="brand.isActive ? 'bg-[#28a745]' : 'bg-[#a6a6a6]'"
                                            class="h-1.5 w-1.5 rounded-full flex-shrink-0"></span>
                                        <span x-text="brand.isActive ? 'Active' : 'Inactive'"></span>
                                    </button>
                                    <button x-show="brand.isFeatured" @click="toggleFeatured(brand)"
                                        class="inline-flex w-fit items-center gap-1 rounded border border-[#f5cba7] bg-[#fef5ec] px-2 py-0.5 text-[12px] font-bold text-[#c45500] transition hover:bg-[#fde8d1]">
                                        <i data-lucide="star" class="h-3 w-3"></i>Featured
                                    </button>
                                    <button x-show="!brand.isFeatured" @click="toggleFeatured(brand)"
                                        class="inline-flex w-fit items-center gap-1 rounded border border-[#d5d9d9] bg-[#f0f2f2] px-2 py-0.5 text-[12px] font-bold text-[#565959] transition hover:border-[#f5cba7] hover:bg-[#fef5ec] hover:text-[#c45500]">
                                        <i data-lucide="star" class="h-3 w-3"></i>Feature
                                    </button>
                                </div>
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3.5 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" @click="openForm(brand)"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-[#d5d9d9] bg-white text-[#565959] transition hover:border-[#007185] hover:text-[#007185]">
                                        <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                    </button>
                                    <button type="button" @click="confirmDelete(brand)"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-[#d5d9d9] bg-white text-[#565959] transition hover:border-[#b12704] hover:text-[#b12704]">
                                        <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <tr x-show="filtered.length === 0">
                        <td colspan="5" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center gap-3 text-[#565959]">
                                <div class="flex h-12 w-12 items-center justify-center rounded-lg border border-[#d5d9d9] bg-[#f0f2f2]">
                                    <i data-lucide="tag" class="h-5 w-5 opacity-50"></i>
                                </div>
                                <div>
                                    <p class="text-[14px] font-semibold text-[#0f1111]" x-text="search ? 'No brands match your search' : 'No brands yet'"></p>
                                    <p class="mt-0.5 text-[13px] text-[#565959]" x-text="search ? 'Try a different keyword or clear filters' : 'Click Add Brand to create your first brand'"></p>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div x-show="totalPages > 1" class="flex items-center justify-between border-t border-[#d5d9d9] px-4 py-3">
            <span class="text-[13px] text-[#565959]">
                Page <span class="font-bold text-[#0f1111]" x-text="currentPage"></span> of <span x-text="totalPages"></span>
                &nbsp;·&nbsp; <span x-text="filtered.length"></span> brands
            </span>
            <div class="flex gap-1.5">
                <button @click="currentPage = Math.max(1, currentPage - 1)" :disabled="currentPage === 1"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-[#d5d9d9] bg-white text-[#565959] transition hover:bg-[#f0f2f2] disabled:opacity-40">
                    <i data-lucide="chevron-left" class="h-4 w-4"></i>
                </button>
                <template x-for="p in pageNumbers" :key="p">
                    <button @click="currentPage = p"
                        :class="p === currentPage
                            ? 'border-[#fcd200] bg-[#ffd814] text-[#0f1111]'
                            : 'border-[#d5d9d9] bg-white text-[#565959] hover:bg-[#f0f2f2]'"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-md border text-[13px] font-bold transition"
                        x-text="p">
                    </button>
                </template>
                <button @click="currentPage = Math.min(totalPages, currentPage + 1)" :disabled="currentPage === totalPages"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-[#d5d9d9] bg-white text-[#565959] transition hover:bg-[#f0f2f2] disabled:opacity-40">
                    <i data-lucide="chevron-right" class="h-4 w-4"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ── Backdrop ── --}}
    <div x-show="showForm" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 bg-black/40"
        @click="showForm = false">
    </div>

    {{-- ── Slide-over Form Panel ── --}}
    <div x-show="showForm" x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 z-50 flex w-full max-w-[460px] flex-col border-l border-[#d5d9d9] bg-white shadow-2xl">

        {{-- Form header --}}
        <div class="flex items-center justify-between border-b border-[#d5d9d9] bg-[#f7fafa] px-5 py-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-[#565959]">Brand Management</p>
                <h2 class="text-[16px] font-bold text-[#0f1111]" x-text="editingId ? 'Edit Brand' : 'Add New Brand'"></h2>
            </div>
            <button @click="showForm = false"
                class="flex h-8 w-8 items-center justify-center rounded-md border border-[#d5d9d9] bg-white text-[#565959] transition hover:text-[#0f1111]">
                <i data-lucide="x" class="h-4 w-4"></i>
            </button>
        </div>

        {{-- Form body --}}
        <div class="flex-1 overflow-y-auto px-5 py-5 space-y-4">

            {{-- Name --}}
            <div>
                <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Name <span class="text-[#b12704]">*</span></label>
                <input x-model="form.name" @input="autoSlug()" type="text" placeholder="e.g. Samsung"
                    class="h-10 w-full rounded-md border border-[#a6a6a6] px-3 text-[14px] text-[#0f1111] outline-none shadow-[inset_0_1px_2px_rgba(15,17,17,0.06)] placeholder:text-[#8a8f98] focus:border-[#007185] focus:shadow-[inset_0_1px_2px_rgba(15,17,17,0.06),0_0_0_3px_rgba(0,113,133,0.1)] transition-shadow" />
            </div>

            {{-- Slug --}}
            <div>
                <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Slug</label>
                <input x-model="form.slug" type="text" placeholder="samsung"
                    class="h-10 w-full rounded-md border border-[#a6a6a6] px-3 font-mono text-[13px] text-[#0f1111] outline-none shadow-[inset_0_1px_2px_rgba(15,17,17,0.06)] placeholder:text-[#8a8f98] focus:border-[#007185] focus:shadow-[inset_0_1px_2px_rgba(15,17,17,0.06),0_0_0_3px_rgba(0,113,133,0.1)] transition-shadow" />
            </div>

            {{-- Logo --}}
            <div>
                <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Logo</label>
                <div x-show="form.logoUrl" class="mb-2 flex items-center gap-3">
                    <img :src="form.logoUrl" alt="logo preview"
                        class="h-12 w-12 rounded-md border border-[#d5d9d9] bg-white object-contain p-1" />
                    <button type="button" @click="form.logoUrl = ''"
                        class="inline-flex h-7 items-center gap-1 rounded border border-[#d5d9d9] bg-[#f0f2f2] px-2.5 text-[12px] font-bold text-[#565959] hover:border-[#b12704] hover:text-[#b12704] transition">
                        <i data-lucide="x" class="h-3 w-3"></i> Remove
                    </button>
                </div>
                <div class="flex gap-2">
                    <label class="flex h-10 cursor-pointer items-center gap-2 rounded-md border border-[#d5d9d9] bg-[#f0f2f2] px-3 text-[13px] font-bold text-[#0f1111] transition hover:bg-[#e3e6e6]">
                        <template x-if="uploadingLogo">
                            <i data-lucide="loader-2" class="h-3.5 w-3.5 animate-spin"></i>
                        </template>
                        <template x-if="!uploadingLogo">
                            <i data-lucide="upload" class="h-3.5 w-3.5"></i>
                        </template>
                        <span x-text="uploadingLogo ? 'Uploading…' : 'Upload'"></span>
                        <input type="file" accept="image/*" class="hidden"
                            @change="uploadImage($event, 'logoUrl')" :disabled="uploadingLogo" />
                    </label>
                    <input x-model="form.logoUrl" type="text" placeholder="or paste URL"
                        class="h-10 flex-1 rounded-md border border-[#a6a6a6] px-3 text-[13px] text-[#0f1111] outline-none shadow-[inset_0_1px_2px_rgba(15,17,17,0.06)] placeholder:text-[#8a8f98] focus:border-[#007185] focus:shadow-[inset_0_1px_2px_rgba(15,17,17,0.06),0_0_0_3px_rgba(0,113,133,0.1)] transition-shadow" />
                </div>
            </div>

            {{-- Banner --}}
            <div>
                <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Banner</label>
                <div x-show="form.bannerUrl" class="mb-2">
                    <img :src="form.bannerUrl" alt="banner preview"
                        class="h-16 w-full rounded-md border border-[#d5d9d9] object-cover" />
                    <button type="button" @click="form.bannerUrl = ''"
                        class="mt-1 inline-flex h-7 items-center gap-1 rounded border border-[#d5d9d9] bg-[#f0f2f2] px-2.5 text-[12px] font-bold text-[#565959] hover:border-[#b12704] hover:text-[#b12704] transition">
                        <i data-lucide="x" class="h-3 w-3"></i> Remove
                    </button>
                </div>
                <div class="flex gap-2">
                    <label class="flex h-10 cursor-pointer items-center gap-2 rounded-md border border-[#d5d9d9] bg-[#f0f2f2] px-3 text-[13px] font-bold text-[#0f1111] transition hover:bg-[#e3e6e6]">
                        <template x-if="uploadingBanner">
                            <i data-lucide="loader-2" class="h-3.5 w-3.5 animate-spin"></i>
                        </template>
                        <template x-if="!uploadingBanner">
                            <i data-lucide="upload" class="h-3.5 w-3.5"></i>
                        </template>
                        <span x-text="uploadingBanner ? 'Uploading…' : 'Upload'"></span>
                        <input type="file" accept="image/*" class="hidden"
                            @change="uploadImage($event, 'bannerUrl')" :disabled="uploadingBanner" />
                    </label>
                    <input x-model="form.bannerUrl" type="text" placeholder="or paste URL"
                        class="h-10 flex-1 rounded-md border border-[#a6a6a6] px-3 text-[13px] text-[#0f1111] outline-none shadow-[inset_0_1px_2px_rgba(15,17,17,0.06)] placeholder:text-[#8a8f98] focus:border-[#007185] focus:shadow-[inset_0_1px_2px_rgba(15,17,17,0.06),0_0_0_3px_rgba(0,113,133,0.1)] transition-shadow" />
                </div>
            </div>

            {{-- Meta Title --}}
            <div>
                <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Meta Title</label>
                <input x-model="form.metaTitle" type="text" placeholder="SEO title for search engines"
                    class="h-10 w-full rounded-md border border-[#a6a6a6] px-3 text-[14px] text-[#0f1111] outline-none shadow-[inset_0_1px_2px_rgba(15,17,17,0.06)] placeholder:text-[#8a8f98] focus:border-[#007185] focus:shadow-[inset_0_1px_2px_rgba(15,17,17,0.06),0_0_0_3px_rgba(0,113,133,0.1)] transition-shadow" />
            </div>

            {{-- Meta Description --}}
            <div>
                <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Meta Description</label>
                <textarea x-model="form.metaDescription" rows="3" placeholder="Brief summary shown in search results…"
                    class="w-full rounded-md border border-[#a6a6a6] px-3 py-2.5 text-[14px] text-[#0f1111] outline-none shadow-[inset_0_1px_2px_rgba(15,17,17,0.06)] placeholder:text-[#8a8f98] focus:border-[#007185] focus:shadow-[inset_0_1px_2px_rgba(15,17,17,0.06),0_0_0_3px_rgba(0,113,133,0.1)] transition-shadow resize-none"></textarea>
            </div>

            {{-- Divider --}}
            <div class="border-t border-[#d5d9d9]"></div>

            {{-- Active toggle --}}
            <div class="flex items-center justify-between rounded-md border border-[#d5d9d9] bg-[#f7fafa] px-4 py-3">
                <div>
                    <div class="text-[13px] font-bold text-[#0f1111]">Active</div>
                    <div class="text-[12px] text-[#565959]">Visible to shoppers on the storefront</div>
                </div>
                <button type="button" @click="form.isActive = !form.isActive"
                    :class="form.isActive ? 'bg-[#007185]' : 'bg-[#d5d9d9]'"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200">
                    <span :class="form.isActive ? 'translate-x-5' : 'translate-x-0'"
                        class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200"></span>
                </button>
            </div>

            {{-- Featured toggle --}}
            <div class="flex items-center justify-between rounded-md border border-[#d5d9d9] bg-[#f7fafa] px-4 py-3">
                <div>
                    <div class="text-[13px] font-bold text-[#0f1111]">Featured</div>
                    <div class="text-[12px] text-[#565959]">Highlight this brand on the homepage</div>
                </div>
                <button type="button" @click="form.isFeatured = !form.isFeatured"
                    :class="form.isFeatured ? 'bg-[#007185]' : 'bg-[#d5d9d9]'"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200">
                    <span :class="form.isFeatured ? 'translate-x-5' : 'translate-x-0'"
                        class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200"></span>
                </button>
            </div>

            {{-- Error message --}}
            <div x-show="error"
                class="rounded-md border border-[#f5c6cb] bg-[#fef2f2] px-4 py-3 text-[13px] font-medium text-[#b12704]"
                x-text="error">
            </div>
        </div>

        {{-- Form footer --}}
        <div class="border-t border-[#d5d9d9] bg-[#f7fafa] px-5 py-4">
            <div class="flex gap-2">
                <button type="button" @click="showForm = false"
                    class="flex-1 h-10 rounded-md border border-[#d5d9d9] bg-white text-[13px] font-bold text-[#565959] transition hover:bg-[#f0f2f2]">
                    Cancel
                </button>
                <button type="button" @click="saveBrand()" :disabled="saving"
                    class="flex-1 h-10 rounded-md border border-[#fcd200] bg-[#ffd814] text-[13px] font-medium text-[#0f1111] shadow-[inset_0_-1px_0_rgba(0,0,0,0.15)] transition hover:bg-[#f7ca00] disabled:opacity-50 inline-flex items-center justify-center gap-2">
                    <template x-if="saving">
                        <i data-lucide="loader-2" class="h-4 w-4 animate-spin"></i>
                    </template>
                    <template x-if="!saving">
                        <i data-lucide="save" class="h-4 w-4"></i>
                    </template>
                    <span x-text="saving ? 'Saving…' : (editingId ? 'Save Changes' : 'Add Brand')"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ── Delete Confirm Modal ── --}}
    <div x-show="showDeleteModal" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40 px-4"
        @click.self="showDeleteModal = false">

        <div class="w-full max-w-sm rounded-lg border border-[#d5d9d9] bg-white p-6 shadow-[0_8px_24px_rgba(15,17,17,0.18)]"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">
            <div class="flex h-10 w-10 items-center justify-center rounded-md border border-[#f5c6cb] bg-[#fef2f2] text-[#b12704] mb-4">
                <i data-lucide="trash-2" class="h-4 w-4"></i>
            </div>
            <h3 class="text-[15px] font-bold text-[#0f1111]">Delete Brand</h3>
            <p class="mt-1 text-[13px] text-[#565959]">
                Are you sure you want to delete <strong class="text-[#0f1111]" x-text="deleteTarget?.name"></strong>? This cannot be undone.
            </p>
            <div class="mt-5 flex gap-2">
                <button @click="showDeleteModal = false"
                    class="flex-1 h-10 rounded-md border border-[#d5d9d9] bg-white text-[13px] font-bold text-[#565959] transition hover:bg-[#f0f2f2]">
                    Cancel
                </button>
                <button @click="deleteBrand()"
                    class="flex-1 h-10 rounded-md border border-[#b12704] bg-[#b12704] text-[13px] font-bold text-white transition hover:bg-[#9b2401] inline-flex items-center justify-center gap-2">
                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                    Delete
                </button>
            </div>
        </div>
    </div>

    {{-- ── Toast ── --}}
    <div x-show="toast" x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="fixed bottom-6 right-6 z-[70] flex items-center gap-3 rounded-md border border-[#d5d9d9] bg-white px-4 py-3 shadow-[0_4px_16px_rgba(15,17,17,0.15)]">
        <div class="flex h-6 w-6 items-center justify-center rounded border border-[#c3e6cb] bg-[#d4edda] text-[#155724]">
            <i data-lucide="check" class="h-3.5 w-3.5"></i>
        </div>
        <span class="text-[13px] font-bold text-[#0f1111]" x-text="toast"></span>
    </div>
</div>
@endsection

@push('scripts')
<script>
function brandsApp(token) {
    return {
        brands: [],
        search: '',
        filterStatus: '',
        filterFeatured: '',
        currentPage: 1,
        perPage: 15,
        showForm: false,
        editingId: null,
        saving: false,
        uploadingLogo: false,
        uploadingBanner: false,
        error: null,
        toast: null,
        showDeleteModal: false,
        deleteTarget: null,
        form: {
            name: '', slug: '', logoUrl: '', bannerUrl: '',
            metaTitle: '', metaDescription: '', isActive: true, isFeatured: false,
        },

        get filtered() {
            const q = this.search.toLowerCase();
            return this.brands.filter(b => {
                const matchSearch = !q || b.name.toLowerCase().includes(q) || b.slug.toLowerCase().includes(q);
                const matchStatus = !this.filterStatus
                    || (this.filterStatus === 'active' && b.isActive)
                    || (this.filterStatus === 'inactive' && !b.isActive);
                const matchFeatured = !this.filterFeatured
                    || (this.filterFeatured === 'yes' && b.isFeatured)
                    || (this.filterFeatured === 'no' && !b.isFeatured);
                return matchSearch && matchStatus && matchFeatured;
            });
        },

        get totalPages() {
            return Math.max(1, Math.ceil(this.filtered.length / this.perPage));
        },

        get paginated() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filtered.slice(start, start + this.perPage);
        },

        get pageNumbers() {
            const pages = [];
            const total = this.totalPages;
            const cur = this.currentPage;
            let from = Math.max(1, cur - 2);
            let to = Math.min(total, cur + 2);
            if (to - from < 4) {
                from = Math.max(1, to - 4);
                to = Math.min(total, from + 4);
            }
            for (let i = from; i <= to; i++) pages.push(i);
            return pages;
        },

        init() {
            const raw = document.getElementById('brands-json')?.textContent;
            this.brands = raw ? JSON.parse(raw) : [];
            this.$nextTick(() => lucide.createIcons());
        },

        openForm(brand) {
            this.error = null;
            if (brand) {
                this.editingId = brand.id;
                this.form = {
                    name: brand.name,
                    slug: brand.slug,
                    logoUrl: brand.logoUrl || '',
                    bannerUrl: brand.bannerUrl || '',
                    metaTitle: brand.metaTitle || '',
                    metaDescription: brand.metaDescription || '',
                    isActive: brand.isActive,
                    isFeatured: brand.isFeatured,
                };
            } else {
                this.editingId = null;
                this.form = { name: '', slug: '', logoUrl: '', bannerUrl: '', metaTitle: '', metaDescription: '', isActive: true, isFeatured: false };
            }
            this.showForm = true;
            this.$nextTick(() => lucide.createIcons());
        },

        autoSlug() {
            if (!this.editingId) {
                this.form.slug = this.form.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
            }
        },

        async uploadImage(event, field) {
            const file = event.target.files[0];
            if (!file) return;
            if (field === 'logoUrl') this.uploadingLogo = true;
            else this.uploadingBanner = true;
            try {
                this.form[field] = await uploadFile(file, token);
            } catch (e) {
                this.error = e.message;
            } finally {
                if (field === 'logoUrl') this.uploadingLogo = false;
                else this.uploadingBanner = false;
            }
        },

        async saveBrand() {
            if (!this.form.name.trim()) { this.error = 'Name is required.'; return; }
            this.saving = true;
            this.error = null;
            try {
                const url = this.editingId
                    ? `${window.API_BASE}/api/admin/brands/${this.editingId}`
                    : `${window.API_BASE}/api/admin/brands`;
                const method = this.editingId ? 'PATCH' : 'POST';
                const res = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
                    body: JSON.stringify(this.form),
                });
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || 'Save failed.');
                if (this.editingId) {
                    this.brands = this.brands.map(b => b.id === this.editingId ? json.brand : b);
                } else {
                    this.brands.unshift(json.brand);
                }
                this.showForm = false;
                this.showToast(this.editingId ? 'Brand updated successfully.' : 'Brand added successfully.');
            } catch (e) {
                this.error = e.message;
            } finally {
                this.saving = false;
            }
        },

        async toggleActive(brand) {
            const prev = brand.isActive;
            brand.isActive = !brand.isActive;
            const res = await fetch(`${window.API_BASE}/api/admin/brands/${brand.id}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
                body: JSON.stringify({ isActive: brand.isActive }),
            });
            if (!res.ok) { brand.isActive = prev; return; }
            this.showToast(brand.isActive ? 'Brand activated.' : 'Brand deactivated.');
        },

        async toggleFeatured(brand) {
            const prev = brand.isFeatured;
            brand.isFeatured = !brand.isFeatured;
            const res = await fetch(`${window.API_BASE}/api/admin/brands/${brand.id}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
                body: JSON.stringify({ isFeatured: brand.isFeatured }),
            });
            if (!res.ok) { brand.isFeatured = prev; return; }
            this.showToast(brand.isFeatured ? 'Brand featured.' : 'Brand unfeatured.');
        },

        confirmDelete(brand) {
            this.deleteTarget = brand;
            this.showDeleteModal = true;
            this.$nextTick(() => lucide.createIcons());
        },

        async deleteBrand() {
            if (!this.deleteTarget) return;
            const id = this.deleteTarget.id;
            this.showDeleteModal = false;
            const res = await fetch(`${window.API_BASE}/api/admin/brands/${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': 'Bearer ' + token },
            });
            if (res.ok) {
                this.brands = this.brands.filter(b => b.id !== id);
                this.showToast('Brand deleted.');
            }
            this.deleteTarget = null;
        },

        showToast(msg) {
            this.toast = msg;
            this.$nextTick(() => lucide.createIcons());
            setTimeout(() => { this.toast = null; }, 3000);
        },
    };
}
</script>
@endpush
