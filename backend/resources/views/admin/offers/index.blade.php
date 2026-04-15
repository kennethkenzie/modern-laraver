@extends('admin.layout')

@section('title', 'Offers & Promotions')

@section('content')
<div class="-mx-4 -mt-4 sm:-mx-6 sm:-mt-6 min-h-screen bg-[#eaeded] px-4 pt-6 pb-16 sm:px-6 sm:pt-8 lg:px-8"
     x-data="offersApp({{ $offers->toJson() }}, '{{ session('admin_token') }}')"
     x-init="init()"
     x-cloak>

    {{-- Slide-over backdrop --}}
    <div x-show="panelOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closePanel()"
         class="fixed inset-0 z-30 bg-black/40"></div>

    {{-- Slide-over panel --}}
    <div x-show="panelOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed inset-y-0 right-0 z-40 flex w-full max-w-lg flex-col bg-white shadow-2xl">

        <div class="flex items-center justify-between border-b border-[#d5d9d9] px-6 py-4">
            <h2 class="text-[16px] font-bold text-[#0f1111]" x-text="editTarget ? 'Edit Offer' : 'Create Offer'"></h2>
            <button @click="closePanel()" class="flex h-8 w-8 items-center justify-center rounded-md border border-[#d5d9d9] text-[#565959] hover:border-[#a6a6a6] hover:text-[#0f1111]">
                <i data-lucide="x" class="h-4 w-4"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-4">

            {{-- Name --}}
            <div>
                <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Offer Name <span class="text-[#b12704]">*</span></label>
                <input type="text" x-model="form.name" placeholder="e.g. Summer Sale 20% Off"
                    class="w-full h-10 rounded-md border border-[#a6a6a6] px-3 text-[14px] text-[#0f1111] placeholder:text-[#a6a6a6] focus:border-[#007185] focus:outline-none focus:ring-2 focus:ring-[rgba(0,113,133,0.15)]" />
            </div>

            {{-- Headline --}}
            <div>
                <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Card Headline</label>
                <input type="text" x-model="form.headline" placeholder="e.g. Save Big This Summer"
                    class="w-full h-10 rounded-md border border-[#a6a6a6] px-3 text-[14px] text-[#0f1111] placeholder:text-[#a6a6a6] focus:border-[#007185] focus:outline-none focus:ring-2 focus:ring-[rgba(0,113,133,0.15)]" />
                <p class="mt-1 text-[12px] text-[#565959]">Shown as the large title on the homepage offer card.</p>
            </div>

            {{-- Description --}}
            <div>
                <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Card Description</label>
                <textarea x-model="form.description" rows="2" placeholder="Short description shown on the offer card…"
                    class="w-full rounded-md border border-[#a6a6a6] px-3 py-2 text-[14px] text-[#0f1111] placeholder:text-[#a6a6a6] focus:border-[#007185] focus:outline-none focus:ring-2 focus:ring-[rgba(0,113,133,0.15)]"></textarea>
            </div>

            {{-- Badge + Featured --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Badge Text</label>
                    <input type="text" x-model="form.badgeText" placeholder="e.g. HOT DEAL"
                        class="w-full h-10 rounded-md border border-[#a6a6a6] px-3 text-[14px] uppercase text-[#0f1111] placeholder:normal-case placeholder:text-[#a6a6a6] focus:border-[#007185] focus:outline-none focus:ring-2 focus:ring-[rgba(0,113,133,0.15)]" />
                </div>
                <div class="flex flex-col justify-end">
                    <div class="flex items-center justify-between rounded-md border border-[#d5d9d9] bg-[#f7fafa] h-10 px-3">
                        <span class="text-[13px] font-bold text-[#0f1111]">Featured</span>
                        <button type="button" @click="form.isFeatured = !form.isFeatured"
                            :class="form.isFeatured ? 'bg-[#007185]' : 'bg-[#d5d9d9]'"
                            class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors">
                            <span :class="form.isFeatured ? 'translate-x-4' : 'translate-x-0'"
                                  class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow transition"></span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Banner Image URL --}}
            <div>
                <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Banner Image URL <span class="text-[#565959] font-normal">(optional)</span></label>
                <input type="url" x-model="form.bannerImage" placeholder="https://…"
                    class="w-full h-10 rounded-md border border-[#a6a6a6] px-3 text-[14px] text-[#0f1111] placeholder:text-[#a6a6a6] focus:border-[#007185] focus:outline-none focus:ring-2 focus:ring-[rgba(0,113,133,0.15)]" />
                <p class="mt-1 text-[12px] text-[#565959]">Background image for the offer card.</p>
            </div>

            {{-- Coupon Code --}}
            <div>
                <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Coupon Code <span class="text-[#565959] font-normal">(optional)</span></label>
                <input type="text" x-model="form.code" placeholder="e.g. SUMMER20"
                    class="w-full h-10 rounded-md border border-[#a6a6a6] px-3 text-[14px] uppercase text-[#0f1111] placeholder:normal-case placeholder:text-[#a6a6a6] focus:border-[#007185] focus:outline-none focus:ring-2 focus:ring-[rgba(0,113,133,0.15)]" />
                <p class="mt-1 text-[12px] text-[#565959]">Leave blank for automatic/store-wide offers.</p>
            </div>

            {{-- Type + Value --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Type <span class="text-[#b12704]">*</span></label>
                    <div class="relative">
                        <select x-model="form.type"
                            class="w-full h-10 appearance-none rounded-md border border-[#a6a6a6] bg-white px-3 pr-8 text-[14px] text-[#0f1111] focus:border-[#007185] focus:outline-none focus:ring-2 focus:ring-[rgba(0,113,133,0.15)]">
                            <option value="percentage">Percentage %</option>
                            <option value="fixed">Fixed Amount</option>
                            <option value="free_shipping">Free Shipping</option>
                        </select>
                        <i data-lucide="chevron-down" class="pointer-events-none absolute right-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-[#565959]"></i>
                    </div>
                </div>
                <div x-show="form.type !== 'free_shipping'">
                    <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">
                        <span x-text="form.type === 'percentage' ? 'Discount %' : 'Amount (UGX)'"></span>
                        <span class="text-[#b12704]">*</span>
                    </label>
                    <input type="number" x-model="form.value" min="0" step="0.01" placeholder="0"
                        class="w-full h-10 rounded-md border border-[#a6a6a6] px-3 text-[14px] text-[#0f1111] placeholder:text-[#a6a6a6] focus:border-[#007185] focus:outline-none focus:ring-2 focus:ring-[rgba(0,113,133,0.15)]" />
                </div>
            </div>

            {{-- Min order + Max discount --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Min Order (UGX)</label>
                    <input type="number" x-model="form.minOrderAmount" min="0" step="1" placeholder="No minimum"
                        class="w-full h-10 rounded-md border border-[#a6a6a6] px-3 text-[14px] text-[#0f1111] placeholder:text-[#a6a6a6] focus:border-[#007185] focus:outline-none focus:ring-2 focus:ring-[rgba(0,113,133,0.15)]" />
                </div>
                <div x-show="form.type === 'percentage'">
                    <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Max Discount (UGX)</label>
                    <input type="number" x-model="form.maxDiscountAmount" min="0" step="1" placeholder="No cap"
                        class="w-full h-10 rounded-md border border-[#a6a6a6] px-3 text-[14px] text-[#0f1111] placeholder:text-[#a6a6a6] focus:border-[#007185] focus:outline-none focus:ring-2 focus:ring-[rgba(0,113,133,0.15)]" />
                </div>
            </div>

            {{-- Date range --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Start Date</label>
                    <input type="date" x-model="form.startsAt"
                        class="w-full h-10 rounded-md border border-[#a6a6a6] px-3 text-[14px] text-[#0f1111] focus:border-[#007185] focus:outline-none focus:ring-2 focus:ring-[rgba(0,113,133,0.15)]" />
                </div>
                <div>
                    <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Expiry Date</label>
                    <input type="date" x-model="form.expiresAt"
                        class="w-full h-10 rounded-md border border-[#a6a6a6] px-3 text-[14px] text-[#0f1111] focus:border-[#007185] focus:outline-none focus:ring-2 focus:ring-[rgba(0,113,133,0.15)]" />
                </div>
            </div>

            {{-- Usage limit --}}
            <div>
                <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Usage Limit</label>
                <input type="number" x-model="form.usageLimit" min="1" step="1" placeholder="Unlimited"
                    class="w-full h-10 rounded-md border border-[#a6a6a6] px-3 text-[14px] text-[#0f1111] placeholder:text-[#a6a6a6] focus:border-[#007185] focus:outline-none focus:ring-2 focus:ring-[rgba(0,113,133,0.15)]" />
            </div>

            {{-- Applies To --}}
            <div>
                <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Applies To</label>
                <div class="flex gap-2 rounded-md border border-[#d5d9d9] bg-[#f7fafa] p-1">
                    <button type="button" @click="form.targetType = 'all'; form.targetIds = []"
                        :class="form.targetType === 'all' ? 'bg-white border-[#a6a6a6] text-[#0f1111] shadow-sm' : 'border-transparent text-[#565959] hover:text-[#0f1111]'"
                        class="flex-1 rounded border px-3 py-1.5 text-[12px] font-medium transition">All Products</button>
                    <button type="button" @click="form.targetType = 'products'; form.targetIds = []; loadProducts()"
                        :class="form.targetType === 'products' ? 'bg-white border-[#a6a6a6] text-[#0f1111] shadow-sm' : 'border-transparent text-[#565959] hover:text-[#0f1111]'"
                        class="flex-1 rounded border px-3 py-1.5 text-[12px] font-medium transition">Specific Products</button>
                    <button type="button" @click="form.targetType = 'categories'; form.targetIds = []; loadCategories()"
                        :class="form.targetType === 'categories' ? 'bg-white border-[#a6a6a6] text-[#0f1111] shadow-sm' : 'border-transparent text-[#565959] hover:text-[#0f1111]'"
                        class="flex-1 rounded border px-3 py-1.5 text-[12px] font-medium transition">Categories</button>
                </div>

                {{-- Product picker --}}
                <div x-show="form.targetType === 'products'" class="mt-3 space-y-2">
                    <div class="relative">
                        <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#a6a6a6]"></i>
                        <input type="text" x-model="productQuery" @input.debounce.300ms="searchProducts()"
                            placeholder="Search products by name…"
                            class="h-9 w-full rounded-md border border-[#a6a6a6] bg-white pl-9 pr-3 text-[13px] text-[#0f1111] placeholder:text-[#a6a6a6] focus:border-[#007185] focus:outline-none focus:ring-2 focus:ring-[rgba(0,113,133,0.15)]" />
                    </div>
                    <template x-if="productResults.length > 0">
                        <ul class="max-h-36 overflow-y-auto rounded-md border border-[#d5d9d9] bg-white">
                            <template x-for="p in productResults" :key="p.id">
                                <li>
                                    <button type="button" @click="addTarget(p)"
                                        :disabled="form.targetIds.some(t => t.id === p.id)"
                                        class="flex w-full items-center justify-between px-3 py-2 text-[13px] text-[#0f1111] hover:bg-[#f7fafa] disabled:opacity-40">
                                        <span x-text="p.name"></span>
                                        <i data-lucide="plus" class="h-3.5 w-3.5 text-[#007185]"></i>
                                    </button>
                                </li>
                            </template>
                        </ul>
                    </template>
                    <template x-if="form.targetIds.length > 0">
                        <div class="flex flex-wrap gap-1.5">
                            <template x-for="t in form.targetIds" :key="t.id">
                                <span class="inline-flex items-center gap-1 rounded border border-[#d5d9d9] bg-[#f0f2f2] px-2 py-0.5 text-[12px] text-[#0f1111]">
                                    <span x-text="t.name"></span>
                                    <button type="button" @click="removeTarget(t.id)" class="ml-0.5 text-[#565959] hover:text-[#b12704]">
                                        <i data-lucide="x" class="h-3 w-3"></i>
                                    </button>
                                </span>
                            </template>
                        </div>
                    </template>
                    <p x-show="form.targetIds.length === 0" class="text-[12px] text-[#a6a6a6]">No products selected — search and add above.</p>
                </div>

                {{-- Category picker --}}
                <div x-show="form.targetType === 'categories'" class="mt-3">
                    <template x-if="allCategories.length === 0">
                        <p class="text-[12px] text-[#a6a6a6]">Loading categories…</p>
                    </template>
                    <div class="max-h-48 overflow-y-auto rounded-md border border-[#d5d9d9] bg-white">
                        <template x-for="cat in allCategories" :key="cat.id">
                            <label class="flex cursor-pointer items-center gap-3 border-b border-[#f0f2f2] px-3 py-2 last:border-0 hover:bg-[#f7fafa]">
                                <input type="checkbox"
                                    :checked="form.targetIds.some(t => t.id === cat.id)"
                                    @change="toggleCategory(cat)"
                                    class="h-4 w-4 rounded border-[#a6a6a6] accent-[#007185]" />
                                <span class="text-[13px] text-[#0f1111]" x-text="cat.name"></span>
                            </label>
                        </template>
                    </div>
                    <p x-show="form.targetIds.length > 0" class="mt-1.5 text-[12px] text-[#565959]">
                        <span x-text="form.targetIds.length"></span> categor<span x-text="form.targetIds.length === 1 ? 'y' : 'ies'"></span> selected
                    </p>
                </div>
            </div>

            {{-- Active toggle --}}
            <div class="flex items-center justify-between rounded-md border border-[#d5d9d9] bg-[#f7fafa] px-4 py-3">
                <div>
                    <p class="text-[13px] font-bold text-[#0f1111]">Active</p>
                    <p class="text-[12px] text-[#565959]">Offer is live and can be applied at checkout.</p>
                </div>
                <button type="button" @click="form.isActive = !form.isActive"
                    :class="form.isActive ? 'bg-[#007185]' : 'bg-[#d5d9d9]'"
                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none">
                    <span :class="form.isActive ? 'translate-x-5' : 'translate-x-0'"
                          class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                </button>
            </div>

            <template x-if="formError">
                <p class="rounded-md border border-[#f5c6cb] bg-[#fef2f2] px-3 py-2 text-[13px] text-[#b12704]" x-text="formError"></p>
            </template>
        </div>

        <div class="flex gap-3 border-t border-[#d5d9d9] px-6 py-4">
            <button @click="closePanel()" type="button"
                class="flex h-10 flex-1 items-center justify-center rounded-md border border-[#d5d9d9] bg-white text-[14px] font-medium text-[#0f1111] hover:bg-[#f7fafa]">
                Cancel
            </button>
            <button @click="save()" :disabled="saving" type="button"
                class="flex h-10 flex-1 items-center justify-center gap-2 rounded-md border border-[#fcd200] bg-[#ffd814] text-[14px] font-medium text-[#0f1111] shadow-[inset_0_-1px_0_rgba(0,0,0,0.15)] hover:bg-[#f7ca00] disabled:opacity-50">
                <template x-if="saving"><i data-lucide="loader-2" class="h-4 w-4 animate-spin"></i></template>
                <span x-text="saving ? 'Saving…' : (editTarget ? 'Save Changes' : 'Create Offer')"></span>
            </button>
        </div>
    </div>

    {{-- Delete modal --}}
    <div x-show="deleteTarget"
         x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div x-show="deleteTarget"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="w-full max-w-sm rounded-lg border border-[#d5d9d9] bg-white p-6 shadow-xl">
            <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-md bg-[#fef2f2]">
                <i data-lucide="trash-2" class="h-5 w-5 text-[#b12704]"></i>
            </div>
            <h3 class="text-[15px] font-bold text-[#0f1111]">Delete Offer?</h3>
            <p class="mt-1 text-[13px] text-[#565959]">
                "<span x-text="deleteTarget?.name"></span>" will be permanently removed.
            </p>
            <div class="mt-5 flex gap-3">
                <button @click="deleteTarget = null" type="button"
                    class="flex h-9 flex-1 items-center justify-center rounded-md border border-[#d5d9d9] bg-white text-[13px] font-medium text-[#0f1111] hover:bg-[#f7fafa]">
                    Cancel
                </button>
                <button @click="doDelete()" :disabled="deleting" type="button"
                    class="flex h-9 flex-1 items-center justify-center rounded-md border border-[#b12704] bg-[#b12704] text-[13px] font-medium text-white hover:bg-[#9a2203] disabled:opacity-50">
                    <template x-if="deleting"><i data-lucide="loader-2" class="mr-1.5 h-3.5 w-3.5 animate-spin"></i></template>
                    <span x-text="deleting ? 'Deleting…' : 'Yes, Delete'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Header --}}
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-[22px] font-bold text-[#0f1111]">Offers & Promotions</h1>
            <p class="mt-0.5 text-[13px] text-[#565959]">Manage discount codes, promotions, and special offers.</p>
        </div>
        <button @click="openCreate()" type="button"
            class="inline-flex h-9 items-center gap-2 rounded-md border border-[#fcd200] bg-[#ffd814] px-4 text-[14px] font-medium text-[#0f1111] shadow-[inset_0_-1px_0_rgba(0,0,0,0.15)] hover:bg-[#f7ca00] active:scale-[.99]">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Create Offer
        </button>
    </div>

    {{-- Stats --}}
    <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-lg border border-[#d5d9d9] bg-white p-4 shadow-[0_1px_3px_rgba(15,17,17,0.08)]">
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#565959]">Total Offers</p>
            <p class="mt-1.5 text-[24px] font-bold text-[#0f1111]" x-text="items.length"></p>
        </div>
        <div class="rounded-lg border border-[#d5d9d9] bg-white p-4 shadow-[0_1px_3px_rgba(15,17,17,0.08)]">
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#565959]">Active</p>
            <p class="mt-1.5 text-[24px] font-bold text-[#007185]" x-text="items.filter(o => o.isActive).length"></p>
        </div>
        <div class="rounded-lg border border-[#d5d9d9] bg-white p-4 shadow-[0_1px_3px_rgba(15,17,17,0.08)]">
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#565959]">With Code</p>
            <p class="mt-1.5 text-[24px] font-bold text-[#0f1111]" x-text="items.filter(o => o.code).length"></p>
        </div>
        <div class="rounded-lg border border-[#d5d9d9] bg-white p-4 shadow-[0_1px_3px_rgba(15,17,17,0.08)]">
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#565959]">Expired</p>
            <p class="mt-1.5 text-[24px] font-bold text-[#b12704]" x-text="expiredCount"></p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-3 flex flex-wrap items-center gap-2">
        <div class="relative flex-1 min-w-[200px]">
            <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#a6a6a6]"></i>
            <input type="text" x-model="search" placeholder="Search offers…"
                class="h-9 w-full rounded-md border border-[#a6a6a6] bg-white pl-9 pr-3 text-[14px] text-[#0f1111] placeholder:text-[#a6a6a6] focus:border-[#007185] focus:outline-none focus:ring-2 focus:ring-[rgba(0,113,133,0.15)]" />
        </div>
        <div class="relative">
            <select x-model="filterStatus"
                class="h-9 appearance-none rounded-md border border-[#a6a6a6] bg-white pl-3 pr-8 text-[14px] text-[#0f1111] focus:border-[#007185] focus:outline-none focus:ring-2 focus:ring-[rgba(0,113,133,0.15)]">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <i data-lucide="chevron-down" class="pointer-events-none absolute right-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-[#565959]"></i>
        </div>
        <div class="relative">
            <select x-model="filterType"
                class="h-9 appearance-none rounded-md border border-[#a6a6a6] bg-white pl-3 pr-8 text-[14px] text-[#0f1111] focus:border-[#007185] focus:outline-none focus:ring-2 focus:ring-[rgba(0,113,133,0.15)]">
                <option value="">All Types</option>
                <option value="percentage">Percentage</option>
                <option value="fixed">Fixed Amount</option>
                <option value="free_shipping">Free Shipping</option>
            </select>
            <i data-lucide="chevron-down" class="pointer-events-none absolute right-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-[#565959]"></i>
        </div>
        <span class="text-[13px] text-[#565959]" x-text="filtered.length + ' result' + (filtered.length !== 1 ? 's' : '')"></span>
    </div>

    {{-- Table --}}
    <div class="rounded-lg border border-[#d5d9d9] bg-white shadow-[0_1px_3px_rgba(15,17,17,0.08)]">

        {{-- Empty state --}}
        <template x-if="filtered.length === 0">
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg border border-[#d5d9d9] bg-[#f0f2f2]">
                    <i data-lucide="tag" class="h-6 w-6 text-[#565959]"></i>
                </div>
                <p class="text-[14px] font-bold text-[#0f1111]">No offers found</p>
                <p class="mt-1 text-[13px] text-[#565959]">Create your first offer to get started.</p>
                <button @click="openCreate()" type="button"
                    class="mt-4 inline-flex h-9 items-center gap-2 rounded-md border border-[#fcd200] bg-[#ffd814] px-4 text-[13px] font-medium text-[#0f1111] shadow-[inset_0_-1px_0_rgba(0,0,0,0.15)] hover:bg-[#f7ca00]">
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    Create Offer
                </button>
            </div>
        </template>

        <template x-if="filtered.length > 0">
            <div>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-[14px]">
                        <thead>
                            <tr class="border-b border-[#d5d9d9] bg-[#f7fafa]">
                                <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#565959]">Offer</th>
                                <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#565959]">Type</th>
                                <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#565959]">Value</th>
                                <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#565959]">Validity</th>
                                <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#565959]">Usage</th>
                                <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#565959]">Status</th>
                                <th class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-[#565959]">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="offer in paginated" :key="offer.id">
                                <tr class="border-b border-[#f0f2f2] last:border-0 hover:bg-[#f7fafa]">
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-[#0f1111]" x-text="offer.name"></p>
                                        <template x-if="offer.code">
                                            <span class="mt-0.5 inline-flex items-center gap-1 rounded border border-[#d5d9d9] bg-[#f0f2f2] px-1.5 py-0.5 font-mono text-[11px] font-bold text-[#565959]">
                                                <i data-lucide="tag" class="h-3 w-3"></i>
                                                <span x-text="offer.code"></span>
                                            </span>
                                        </template>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded border px-2 py-0.5 text-[12px] font-medium"
                                            :class="{
                                                'border-[#bee3f8] bg-[#ebf8ff] text-[#2b6cb0]': offer.type === 'percentage',
                                                'border-[#c6f6d5] bg-[#f0fff4] text-[#276749]': offer.type === 'fixed',
                                                'border-[#e9d8fd] bg-[#faf5ff] text-[#553c9a]': offer.type === 'free_shipping'
                                            }"
                                            x-text="offer.type === 'percentage' ? 'Percentage' : offer.type === 'fixed' ? 'Fixed Amount' : 'Free Shipping'">
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-[#0f1111]">
                                        <span x-text="offer.type === 'percentage' ? offer.value + '%' : offer.type === 'fixed' ? 'UGX ' + Number(offer.value).toLocaleString() : '—'"></span>
                                    </td>
                                    <td class="px-4 py-3 text-[13px] text-[#565959]">
                                        <template x-if="offer.startsAt || offer.expiresAt">
                                            <div>
                                                <p x-text="(offer.startsAt || '∞') + ' → ' + (offer.expiresAt || '∞')"></p>
                                                <template x-if="offer.expiresAt && new Date(offer.expiresAt) < new Date()">
                                                    <span class="text-[11px] font-bold text-[#b12704]">EXPIRED</span>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="!offer.startsAt && !offer.expiresAt">
                                            <span class="text-[#a6a6a6]">No limit</span>
                                        </template>
                                    </td>
                                    <td class="px-4 py-3 text-[13px] text-[#565959]">
                                        <span x-text="offer.usageCount"></span>
                                        <template x-if="offer.usageLimit">
                                            <span x-text="' / ' + offer.usageLimit"></span>
                                        </template>
                                        <template x-if="!offer.usageLimit">
                                            <span class="text-[#a6a6a6]"> / ∞</span>
                                        </template>
                                    </td>
                                    <td class="px-4 py-3">
                                        <button @click="toggleStatus(offer)" type="button"
                                            class="inline-flex items-center rounded border px-2 py-0.5 text-[12px] font-medium transition hover:opacity-75"
                                            :class="offer.isActive ? 'border-[#c3e6cb] bg-[#d4edda] text-[#155724]' : 'border-[#d5d9d9] bg-[#f0f2f2] text-[#565959]'"
                                            x-text="offer.isActive ? 'Active' : 'Inactive'">
                                        </button>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="inline-flex gap-1">
                                            <button @click="openEdit(offer)" type="button"
                                                class="flex h-8 w-8 items-center justify-center rounded-md border border-[#d5d9d9] text-[#565959] hover:border-[#007185] hover:text-[#007185]">
                                                <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                            </button>
                                            <button @click="confirmDelete(offer)" type="button"
                                                class="flex h-8 w-8 items-center justify-center rounded-md border border-[#d5d9d9] text-[#565959] hover:border-[#b12704] hover:text-[#b12704]">
                                                <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <template x-if="totalPages > 1">
                    <div class="flex items-center justify-between border-t border-[#d5d9d9] px-4 py-3">
                        <p class="text-[13px] text-[#565959]">
                            Showing <span x-text="(page - 1) * perPage + 1"></span>–<span x-text="Math.min(page * perPage, filtered.length)"></span> of <span x-text="filtered.length"></span>
                        </p>
                        <div class="flex items-center gap-1">
                            <button @click="page--" :disabled="page === 1" type="button"
                                class="flex h-8 w-8 items-center justify-center rounded-md border border-[#d5d9d9] text-[#565959] hover:border-[#a6a6a6] disabled:opacity-40">
                                <i data-lucide="chevron-left" class="h-4 w-4"></i>
                            </button>
                            <template x-for="p in pageNumbers" :key="p">
                                <button @click="p !== '…' && (page = p)" type="button"
                                    :class="p === page ? 'border-[#fcd200] bg-[#ffd814] font-bold text-[#0f1111]' : p === '…' ? 'cursor-default border-transparent text-[#565959]' : 'border-[#d5d9d9] text-[#565959] hover:border-[#a6a6a6]'"
                                    class="flex h-8 w-8 items-center justify-center rounded-md border text-[13px]"
                                    x-text="p">
                                </button>
                            </template>
                            <button @click="page++" :disabled="page === totalPages" type="button"
                                class="flex h-8 w-8 items-center justify-center rounded-md border border-[#d5d9d9] text-[#565959] hover:border-[#a6a6a6] disabled:opacity-40">
                                <i data-lucide="chevron-right" class="h-4 w-4"></i>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </div>
</div>
@endsection

@push('scripts')
<script>
function offersApp(initial, token) {
    return {
        items:        initial,
        search:       '',
        filterStatus: '',
        filterType:   '',
        page:         1,
        perPage:      15,

        panelOpen:   false,
        editTarget:  null,
        saving:      false,
        formError:   null,

        deleteTarget: null,
        deleting:     false,

        form: {
            name: '', headline: '', description: '', badgeText: '', bannerImage: '',
            code: '', type: 'percentage', value: '',
            minOrderAmount: '', maxDiscountAmount: '',
            startsAt: '', expiresAt: '', usageLimit: '',
            isActive: true, isFeatured: false,
            targetType: 'all', targetIds: [],
        },

        productQuery: '',
        productResults: [],
        allCategories: [],

        init() {
            this.$nextTick(() => lucide.createIcons());
        },

        async loadProducts() {
            if (this.productResults.length) return;
            await this.searchProducts();
        },

        async searchProducts() {
            try {
                const res = await fetch('/api/admin/offers/product-search?q=' + encodeURIComponent(this.productQuery), {
                    headers: { 'Authorization': 'Bearer ' + token },
                });
                const data = await res.json();
                this.productResults = data.products || [];
                this.$nextTick(() => lucide.createIcons());
            } catch {}
        },

        async loadCategories() {
            if (this.allCategories.length) return;
            try {
                const res = await fetch('/api/admin/offers/categories', {
                    headers: { 'Authorization': 'Bearer ' + token },
                });
                const data = await res.json();
                this.allCategories = data.categories || [];
            } catch {}
        },

        addTarget(item) {
            if (!this.form.targetIds.some(t => t.id === item.id)) {
                this.form.targetIds.push({ id: item.id, name: item.name });
            }
            this.$nextTick(() => lucide.createIcons());
        },

        removeTarget(id) {
            this.form.targetIds = this.form.targetIds.filter(t => t.id !== id);
            this.$nextTick(() => lucide.createIcons());
        },

        toggleCategory(cat) {
            const idx = this.form.targetIds.findIndex(t => t.id === cat.id);
            if (idx === -1) this.form.targetIds.push({ id: cat.id, name: cat.name });
            else this.form.targetIds.splice(idx, 1);
        },

        get expiredCount() {
            const now = new Date();
            return this.items.filter(o => o.expiresAt && new Date(o.expiresAt) < now).length;
        },

        get filtered() {
            let list = this.items;
            if (this.search.trim()) {
                const q = this.search.toLowerCase();
                list = list.filter(o =>
                    o.name.toLowerCase().includes(q) ||
                    (o.code && o.code.toLowerCase().includes(q))
                );
            }
            if (this.filterStatus === 'active')   list = list.filter(o => o.isActive);
            if (this.filterStatus === 'inactive') list = list.filter(o => !o.isActive);
            if (this.filterType) list = list.filter(o => o.type === this.filterType);
            return list;
        },

        get totalPages() { return Math.max(1, Math.ceil(this.filtered.length / this.perPage)); },

        get paginated() {
            const start = (this.page - 1) * this.perPage;
            return this.filtered.slice(start, start + this.perPage);
        },

        get pageNumbers() {
            const total = this.totalPages, cur = this.page, pages = [];
            for (let i = 1; i <= total; i++) {
                if (i === 1 || i === total || Math.abs(i - cur) <= 1) pages.push(i);
                else if (pages[pages.length - 1] !== '…') pages.push('…');
            }
            return pages;
        },

        openCreate() {
            this.editTarget = null;
            this.form = { name: '', headline: '', description: '', badgeText: '', bannerImage: '', code: '', type: 'percentage', value: '', minOrderAmount: '', maxDiscountAmount: '', startsAt: '', expiresAt: '', usageLimit: '', isActive: true, isFeatured: false, targetType: 'all', targetIds: [] };
            this.formError = null;
            this.productQuery = '';
            this.productResults = [];
            this.panelOpen = true;
            this.$nextTick(() => lucide.createIcons());
        },

        openEdit(offer) {
            this.editTarget = offer;
            this.form = {
                name: offer.name, headline: offer.headline || '', description: offer.description || '',
                badgeText: offer.badgeText || '', bannerImage: offer.bannerImage || '',
                code: offer.code || '', type: offer.type,
                value: offer.value, minOrderAmount: offer.minOrderAmount || '',
                maxDiscountAmount: offer.maxDiscountAmount || '',
                startsAt: offer.startsAt || '', expiresAt: offer.expiresAt || '',
                usageLimit: offer.usageLimit || '', isActive: offer.isActive, isFeatured: offer.isFeatured || false,
                targetType: offer.targetType || 'all',
                targetIds: offer.targetType === 'products'
                    ? (offer.targetProducts || []).map(p => ({ id: p.id, name: p.name }))
                    : offer.targetType === 'categories'
                        ? (offer.targetCategories || []).map(c => ({ id: c.id, name: c.name }))
                        : [],
            };
            this.formError = null;
            this.productQuery = '';
            this.productResults = [];
            if (offer.targetType === 'categories') this.loadCategories();
            this.panelOpen = true;
            this.$nextTick(() => lucide.createIcons());
        },

        closePanel() {
            this.panelOpen = false;
            this.editTarget = null;
        },

        async save() {
            if (!this.form.name.trim()) { this.formError = 'Offer name is required.'; return; }
            if (this.form.type !== 'free_shipping' && (!this.form.value || this.form.value <= 0)) {
                this.formError = 'Please enter a valid discount value.'; return;
            }
            this.saving = true; this.formError = null;
            const payload = {
                name: this.form.name.trim(),
                headline: this.form.headline.trim() || null,
                description: this.form.description.trim() || null,
                badge_text: this.form.badgeText.trim().toUpperCase() || null,
                banner_image: this.form.bannerImage.trim() || null,
                code: this.form.code.trim().toUpperCase() || null,
                type: this.form.type,
                value: parseFloat(this.form.value) || 0,
                min_order_amount: this.form.minOrderAmount ? parseFloat(this.form.minOrderAmount) : null,
                max_discount_amount: this.form.maxDiscountAmount ? parseFloat(this.form.maxDiscountAmount) : null,
                starts_at: this.form.startsAt || null,
                expires_at: this.form.expiresAt || null,
                usage_limit: this.form.usageLimit ? parseInt(this.form.usageLimit) : null,
                is_active: this.form.isActive,
                is_featured: this.form.isFeatured,
                target_type: this.form.targetType,
                target_ids: this.form.targetIds.map(t => t.id),
            };
            try {
                let res, data;
                if (this.editTarget) {
                    res = await fetch(`/api/admin/offers/${this.editTarget.id}`, {
                        method: 'PUT', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
                        body: JSON.stringify(payload),
                    });
                    data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Update failed.');
                    const idx = this.items.findIndex(o => o.id === this.editTarget.id);
                    if (idx !== -1) this.items[idx] = { ...this.items[idx], ...data.offer ?? data };
                } else {
                    res = await fetch('/api/admin/offers', {
                        method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
                        body: JSON.stringify(payload),
                    });
                    data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Create failed.');
                    this.items.unshift(data.offer ?? data);
                }
                this.closePanel();
                this.$nextTick(() => lucide.createIcons());
            } catch (e) {
                this.formError = e.message;
            } finally {
                this.saving = false;
            }
        },

        async toggleStatus(offer) {
            const prev = offer.isActive;
            offer.isActive = !prev;
            try {
                const res = await fetch(`/api/admin/offers/${offer.id}/toggle`, {
                    method: 'PATCH', headers: { 'Authorization': 'Bearer ' + token },
                });
                if (!res.ok) { offer.isActive = prev; }
            } catch { offer.isActive = prev; }
        },

        confirmDelete(offer) {
            this.deleteTarget = offer;
            this.$nextTick(() => lucide.createIcons());
        },

        async doDelete() {
            this.deleting = true;
            try {
                const res = await fetch(`/api/admin/offers/${this.deleteTarget.id}`, {
                    method: 'DELETE', headers: { 'Authorization': 'Bearer ' + token },
                });
                if (!res.ok) throw new Error('Delete failed.');
                this.items = this.items.filter(o => o.id !== this.deleteTarget.id);
                this.deleteTarget = null;
                this.$nextTick(() => lucide.createIcons());
            } catch (e) {
                alert(e.message);
            } finally {
                this.deleting = false;
            }
        },
    };
}
</script>
@endpush
