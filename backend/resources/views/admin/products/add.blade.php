@extends('admin.layout')

@section('title', 'Add New Product')

@section('content')
<div
    class="-mx-4 -mt-4 sm:-mx-6 sm:-mt-6 min-h-screen bg-[#f7f7f8] px-4 pt-4 pb-16 sm:px-6 sm:pt-6"
    x-data="addProductApp('{{ session('admin_token') }}')"
    x-init="init()"
>
    {{-- Header --}}
    <div class="mx-auto max-w-[1220px]">
        <div class="mb-6 flex items-start gap-3">
            <span class="mt-[10px] h-2.5 w-8 rounded-full bg-[#0b63ce] flex-shrink-0"></span>
            <div>
                <h1 class="text-[28px] font-semibold tracking-tight text-gray-900">Add New Product</h1>
                <p class="mt-1 text-sm text-gray-500">Fill in the information below to register a new product.</p>
            </div>
        </div>

        {{-- Tab nav --}}
        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="flex min-w-max items-center gap-1.5 px-3 py-2.5">
                <template x-for="(tab, index) in tabs" :key="tab">
                    <button
                        type="button"
                        @click="activeTab = index"
                        :class="index === activeTab
                            ? 'bg-indigo-50 text-[#0b63ce]'
                            : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'"
                        class="rounded-md px-5 py-2.5 text-sm font-semibold transition"
                        x-text="tab"
                    ></button>
                </template>
            </div>
        </div>

        {{-- Tab content card --}}
        <section class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 bg-gray-50/30 px-6 py-5">
                <h2 class="text-lg font-bold text-gray-900" x-text="tabs[activeTab]"></h2>
            </div>

            <div class="px-6 py-8">

                {{-- TAB 0: Product Information --}}
                <div x-show="activeTab === 0" class="space-y-6">
                    <div>
                        <label class="mb-2 block text-sm font-bold text-gray-700">Product Name <span class="text-red-500">*</span></label>
                        <input x-model="form.productName" @input="autoSlug()" type="text" placeholder="e.g. Samsung Galaxy S24 Ultra"
                            class="h-11 w-full rounded-md border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
                    </div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-bold text-gray-700">Category <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select x-model="form.category"
                                    :class="form.category === 'Select Category' ? 'text-gray-400' : 'text-gray-700'"
                                    class="h-11 w-full appearance-none rounded-md border border-gray-300 bg-white px-4 pr-10 text-sm outline-none transition hover:border-gray-400 focus:border-[#0b63ce]">
                                    <option value="Select Category">Select Category</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat['name'] }}">{{ $cat['name'] }}</option>
                                    @endforeach
                                </select>
                                <i data-lucide="chevron-down" class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-gray-700">Brand</label>
                            <input x-model="form.brand" type="text" placeholder="e.g. Samsung, Sony, LG"
                                class="h-11 w-full rounded-md border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
                        </div>
                    </div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-bold text-gray-700">Unit <span class="text-red-500">*</span></label>
                            <input x-model="form.unit" type="text" placeholder="e.g kg, pc, box"
                                class="h-11 w-full rounded-md border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-gray-700">Min. Order Quantity <span class="text-red-500">*</span></label>
                            <input x-model="form.minimumOrderQuantity" type="number" min="1"
                                class="h-11 w-full rounded-md border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
                        </div>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold text-gray-700">Barcode</label>
                        <input x-model="form.barcode" type="text" placeholder="Enter product barcode"
                            class="h-11 w-full rounded-md border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold text-gray-700">Tags</label>
                        <input x-model="form.tags" type="text" placeholder="smart tv, samsung, 4k"
                            class="h-11 w-full rounded-md border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold text-gray-700">Slug</label>
                        <input x-model="form.slug" type="text" placeholder="product-slug-here"
                            class="h-11 w-full rounded-md border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-5 py-4">
                        <div>
                            <div class="text-sm font-bold text-gray-800">Digital Product</div>
                            <div class="mt-0.5 text-xs text-gray-500">If enabled, this product won't require shipping.</div>
                        </div>
                        <button type="button" @click="form.isDigitalProduct = !form.isDigitalProduct"
                            :class="form.isDigitalProduct ? 'bg-[#0b63ce]' : 'bg-gray-300'"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200">
                            <span :class="form.isDigitalProduct ? 'translate-x-5' : 'translate-x-0'"
                                class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200"></span>
                        </button>
                    </div>
                </div>

                {{-- TAB 1: Images & Videos --}}
                <div x-show="activeTab === 1" class="space-y-6">

                    {{-- Hidden file input --}}
                    <input id="media-file-input" type="file" accept="image/*,video/*" multiple class="hidden"
                        @change="handleFileSelect($event.target.files); $event.target.value = '';" />

                    {{-- Drop zone / upload area --}}
                    <div
                        class="rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 px-6 py-10 text-center transition hover:border-[#0b63ce] hover:bg-blue-50/30"
                        :class="isDragging ? 'border-[#0b63ce] bg-blue-50/40' : ''"
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="isDragging = false; handleFileSelect($event.dataTransfer.files)"
                    >
                        <div class="mx-auto max-w-sm">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-white shadow-sm border border-gray-200">
                                <i data-lucide="upload-cloud" class="h-6 w-6 text-[#0b63ce]"></i>
                            </div>
                            <div class="mt-4 text-base font-semibold text-gray-900">Upload product images &amp; videos</div>
                            <p class="mt-1 text-sm text-gray-500">Drag &amp; drop files here, or click to browse</p>
                            <div class="mt-2 text-xs text-gray-400">JPG, PNG, WebP, GIF, SVG, MP4 · Max 20 MB per file</div>
                            <button type="button" @click="document.getElementById('media-file-input').click()"
                                class="mt-5 inline-flex items-center gap-2 rounded-lg bg-[#1f2937] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-black">
                                <i data-lucide="folder-open" class="h-4 w-4"></i> Choose files
                            </button>
                        </div>
                    </div>

                    {{-- Upload progress bar --}}
                    <div x-show="uploadingCount > 0" class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3">
                        <div class="flex items-center gap-2 text-sm font-medium text-blue-700">
                            <i data-lucide="loader-2" class="h-4 w-4 animate-spin"></i>
                            Uploading <span x-text="uploadingCount"></span> file(s)…
                        </div>
                    </div>

                    {{-- URL fallback --}}
                    <div>
                        <div class="mb-2 text-sm font-bold text-gray-700">Or add by URL</div>
                        <div class="flex gap-2">
                            <input x-model="newMediaUrl" type="url" placeholder="https://example.com/image.jpg"
                                @keydown.enter.prevent="addMedia()"
                                class="h-11 flex-1 rounded-md border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
                            <button type="button" @click="addMedia()"
                                class="inline-flex h-11 items-center gap-2 rounded-md border border-gray-300 bg-white px-4 text-sm font-bold text-gray-700 transition hover:bg-gray-50">
                                <i data-lucide="plus" class="h-4 w-4"></i> Add URL
                            </button>
                        </div>
                    </div>
                    <div x-show="media.length > 0" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <template x-for="(item, idx) in media" :key="item.id">
                            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                                <div class="relative flex h-48 items-center justify-center bg-gray-100 p-3">
                                    <template x-if="item.uploading">
                                        <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-white/80 backdrop-blur-sm">
                                            <i data-lucide="loader-2" class="h-7 w-7 animate-spin text-[#0b63ce]"></i>
                                            <span class="text-xs font-semibold text-gray-500">Uploading…</span>
                                        </div>
                                    </template>
                                    <template x-if="!item.uploading && item.kind === 'image'">
                                        <img :src="item.url" :alt="item.name" class="max-h-full w-full object-contain" />
                                    </template>
                                    <template x-if="!item.uploading && item.kind === 'video'">
                                        <video :src="item.url" class="max-h-full w-full rounded-lg object-cover" controls></video>
                                    </template>
                                </div>
                                <div class="flex items-center justify-between gap-3 border-t border-gray-100 px-4 py-3">
                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-semibold text-gray-900" x-text="item.name"></div>
                                        <div class="text-[11px] uppercase tracking-wider text-gray-400"
                                             x-text="item.uploading ? 'Uploading…' : item.kind"></div>
                                    </div>
                                    <button type="button" @click="removeMedia(item.id)" :disabled="item.uploading"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-red-50 text-red-500 transition hover:bg-red-100 disabled:opacity-40">
                                        <i data-lucide="x" class="h-4 w-4"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- TAB 2: Price & Stock --}}
                <div x-show="activeTab === 2" class="space-y-8">
                    <div class="grid gap-5 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-bold text-gray-700">Currency <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select x-model="form.currencyCode"
                                    class="h-11 w-full appearance-none rounded-md border border-gray-300 bg-white px-4 pr-10 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]">
                                    <option>UGX</option><option>USD</option><option>KES</option>
                                </select>
                                <i data-lucide="chevron-down" class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-gray-700">Low Stock Alert</label>
                            <input x-model="form.lowStockThreshold" type="number" min="0"
                                class="h-11 w-full rounded-md border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-gray-700">Tax Class</label>
                            <div class="relative">
                                <select x-model="form.taxClass"
                                    class="h-11 w-full appearance-none rounded-md border border-gray-300 bg-white px-4 pr-10 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]">
                                    <option>Standard</option><option>Zero Rated</option><option>Exempt</option>
                                </select>
                                <i data-lucide="chevron-down" class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Variants --}}
                    <div class="space-y-4">
                        <template x-for="(variant, index) in variants" :key="variant.id">
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                                <div class="mb-4 flex items-center justify-between gap-3">
                                    <div class="text-sm font-semibold text-gray-900" x-text="'Variant ' + (index + 1)"></div>
                                    <button x-show="variants.length > 1" type="button" @click="removeVariant(variant.id)"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-red-50 text-red-500 transition hover:bg-red-100">
                                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                                    </button>
                                </div>
                                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                                    <div>
                                        <label class="mb-1.5 block text-xs font-bold text-gray-600">Variant Name</label>
                                        <input x-model="variant.label" type="text" placeholder="Default"
                                            class="h-11 w-full rounded-md border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-xs font-bold text-gray-600">SKU</label>
                                        <input x-model="variant.sku" type="text" placeholder="SKU-001"
                                            class="h-11 w-full rounded-md border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-xs font-bold text-gray-600">Price <span class="text-red-500">*</span></label>
                                        <input x-model="variant.price" type="number" min="0" placeholder="120000"
                                            class="h-11 w-full rounded-md border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-xs font-bold text-gray-600">Compare At</label>
                                        <input x-model="variant.compareAtPrice" type="number" min="0" placeholder="150000"
                                            class="h-11 w-full rounded-md border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-xs font-bold text-gray-600">Stock Qty</label>
                                        <input x-model="variant.stockQty" type="number" min="0" placeholder="20"
                                            class="h-11 w-full rounded-md border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="addVariant()"
                        class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 transition hover:bg-gray-50">
                        <i data-lucide="plus" class="h-4 w-4"></i> Add variant
                    </button>
                </div>

                {{-- TAB 3: Description & Specification --}}
                <div x-show="activeTab === 3" class="space-y-8">
                    <div>
                        <label class="mb-2 block text-sm font-bold text-gray-700">Short Description</label>
                        <textarea x-model="form.shortDescription" rows="3"
                            placeholder="A short summary shown on cards and listing pages."
                            class="w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]"></textarea>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold text-gray-700">Full Description</label>
                        <textarea x-model="form.description" rows="8"
                            placeholder="Describe the product, features, compatibility, and what is included in the box."
                            class="w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]"></textarea>
                    </div>

                    {{-- Bullets --}}
                    <div>
                        <div class="mb-3 text-sm font-bold text-gray-700">About This Item</div>
                        <div class="space-y-3">
                            <template x-for="(bullet, index) in bullets" :key="index">
                                <div class="flex gap-3">
                                    <input :value="bullet" @input="bullets[index] = $event.target.value"
                                        :placeholder="'Bullet point ' + (index + 1)"
                                        class="h-11 w-full rounded-md border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
                                    <button type="button" @click="removeBullet(index)"
                                        class="inline-flex h-11 w-11 items-center justify-center rounded-md bg-red-50 text-red-500 transition hover:bg-red-100">
                                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                                    </button>
                                </div>
                            </template>
                        </div>
                        <button type="button" @click="bullets.push('')"
                            class="mt-3 inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 transition hover:bg-gray-50">
                            <i data-lucide="plus" class="h-4 w-4"></i> Add bullet
                        </button>
                    </div>

                    {{-- Specifications --}}
                    <div>
                        <div class="mb-3 text-sm font-bold text-gray-700">Specifications</div>
                        <div class="space-y-3">
                            <template x-for="(spec, index) in specs" :key="spec.id">
                                <div class="grid gap-3 md:grid-cols-[1fr_1fr_auto]">
                                    <input :value="spec.label" @input="specs[index].label = $event.target.value"
                                        placeholder="Spec label"
                                        class="h-11 rounded-md border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
                                    <input :value="spec.value" @input="specs[index].value = $event.target.value"
                                        placeholder="Spec value"
                                        class="h-11 rounded-md border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
                                    <button type="button" @click="removeSpec(spec.id)"
                                        class="inline-flex h-11 w-11 items-center justify-center rounded-md bg-red-50 text-red-500 transition hover:bg-red-100">
                                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                                    </button>
                                </div>
                            </template>
                        </div>
                        <button type="button" @click="addSpec()"
                            class="mt-3 inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 transition hover:bg-gray-50">
                            <i data-lucide="plus" class="h-4 w-4"></i> Add specification
                        </button>
                    </div>
                </div>

                {{-- TAB 4: Shipping Info --}}
                <div x-show="activeTab === 4" class="space-y-8">
                    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                        <div>
                            <label class="mb-2 block text-sm font-bold text-gray-700">Weight (kg)</label>
                            <input x-model="form.shippingWeight" type="number" min="0" step="0.01" placeholder="0.5"
                                class="h-11 w-full rounded-md border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-gray-700">Length (cm)</label>
                            <input x-model="form.shippingLength" type="number" min="0" placeholder="20"
                                class="h-11 w-full rounded-md border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-gray-700">Width (cm)</label>
                            <input x-model="form.shippingWidth" type="number" min="0" placeholder="10"
                                class="h-11 w-full rounded-md border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-gray-700">Height (cm)</label>
                            <input x-model="form.shippingHeight" type="number" min="0" placeholder="8"
                                class="h-11 w-full rounded-md border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
                        </div>
                    </div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-bold text-gray-700">Shipping Class</label>
                            <div class="relative">
                                <select x-model="form.shippingClass"
                                    class="h-11 w-full appearance-none rounded-md border border-gray-300 bg-white px-4 pr-10 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]">
                                    <option>Standard Parcel</option><option>Bulky</option><option>Express</option><option>Pickup Only</option>
                                </select>
                                <i data-lucide="chevron-down" class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-gray-700">Dispatch Time</label>
                            <div class="relative">
                                <select x-model="form.dispatchTime"
                                    class="h-11 w-full appearance-none rounded-md border border-gray-300 bg-white px-4 pr-10 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]">
                                    <option>Same day</option><option>1-2 business days</option><option>3-5 business days</option><option>Pre-order</option>
                                </select>
                                <i data-lucide="chevron-down" class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold text-gray-700">Shipping Notes</label>
                        <textarea x-model="form.shippingNotes" rows="4"
                            placeholder="Add packaging notes, delivery limitations, or special handling instructions."
                            class="w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]"></textarea>
                    </div>
                </div>

                {{-- TAB 5: Others --}}
                <div x-show="activeTab === 5" class="space-y-8">
                    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                        <div>
                            <label class="mb-2 block text-sm font-bold text-gray-700">Product Type</label>
                            <div class="relative">
                                <select x-model="form.productType"
                                    class="h-11 w-full appearance-none rounded-md border border-gray-300 bg-white px-4 pr-10 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]">
                                    <option>Physical</option><option>Digital</option><option>Service</option><option>Bundle</option>
                                </select>
                                <i data-lucide="chevron-down" class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-gray-700">Condition</label>
                            <div class="relative">
                                <select x-model="form.condition"
                                    class="h-11 w-full appearance-none rounded-md border border-gray-300 bg-white px-4 pr-10 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]">
                                    <option>New</option><option>Refurbished</option><option>Used</option>
                                </select>
                                <i data-lucide="chevron-down" class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-gray-700">Visibility</label>
                            <div class="relative">
                                <select x-model="form.visibility"
                                    class="h-11 w-full appearance-none rounded-md border border-gray-300 bg-white px-4 pr-10 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]">
                                    <option>Published</option><option>Draft</option><option>Hidden</option>
                                </select>
                                <i data-lucide="chevron-down" class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-gray-700">Warranty</label>
                            <div class="relative">
                                <select x-model="form.warranty"
                                    class="h-11 w-full appearance-none rounded-md border border-gray-300 bg-white px-4 pr-10 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]">
                                    <option>No warranty</option><option>7 days</option><option>30 days</option><option>6 months</option><option>1 year</option>
                                </select>
                                <i data-lucide="chevron-down" class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-5 py-4">
                        <div>
                            <div class="text-sm font-bold text-gray-800">Featured Product</div>
                            <div class="mt-0.5 text-xs text-gray-500">Highlight this item in featured storefront collections.</div>
                        </div>
                        <button type="button" @click="form.featured = !form.featured"
                            :class="form.featured ? 'bg-[#0b63ce]' : 'bg-gray-300'"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200">
                            <span :class="form.featured ? 'translate-x-5' : 'translate-x-0'"
                                class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200"></span>
                        </button>
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-5 py-4">
                        <div>
                            <div class="text-sm font-bold text-gray-800">Returnable</div>
                            <div class="mt-0.5 text-xs text-gray-500">Allow the product to be included in return and refund workflows.</div>
                        </div>
                        <button type="button" @click="form.returnable = !form.returnable"
                            :class="form.returnable ? 'bg-[#0b63ce]' : 'bg-gray-300'"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200">
                            <span :class="form.returnable ? 'translate-x-5' : 'translate-x-0'"
                                class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200"></span>
                        </button>
                    </div>
                </div>

                {{-- TAB 6: SEO --}}
                <div x-show="activeTab === 6" class="space-y-8">
                    <div>
                        <label class="mb-2 block text-sm font-bold text-gray-700">Meta Title</label>
                        <input x-model="form.seoTitle" type="text" placeholder="SEO title for search engines"
                            class="h-11 w-full rounded-md border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold text-gray-700">Meta Description</label>
                        <textarea x-model="form.seoDescription" rows="4"
                            placeholder="A concise search snippet for search engines."
                            class="w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]"></textarea>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold text-gray-700">Meta Keywords</label>
                        <input x-model="form.seoKeywords" type="text" placeholder="keyword 1, keyword 2, keyword 3"
                            class="h-11 w-full rounded-md border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
                    </div>
                </div>

            </div>
        </section>

        {{-- Status message --}}
        <div x-show="message"
            :class="messageTone === 'success'
                ? 'border-[#bbf7d0] bg-[#f0fdf4] text-[#166534]'
                : 'border-[#fecaca] bg-[#fef2f2] text-[#b91c1c]'"
            class="mt-4 rounded-md border px-4 py-3 text-sm font-medium"
            x-text="message">
        </div>
    </div>

    {{-- Sticky footer --}}
    <div class="sticky bottom-0 z-10 mt-8 border-t border-gray-200 bg-white/95 px-4 py-5 backdrop-blur-md md:px-8">
        <div class="mx-auto flex max-w-[1220px] items-center justify-between gap-3">
            <a href="{{ route('dashboard.products') }}" class="text-sm font-bold text-gray-500 hover:text-gray-900">Back to products</a>
            <div class="flex items-center gap-3">
                <button type="button" @click="submit('draft')" :disabled="isSubmitting"
                    class="inline-flex h-11 items-center justify-center rounded-md bg-gray-100 px-6 text-sm font-bold text-gray-700 transition hover:bg-gray-200 disabled:opacity-50">
                    <template x-if="isSubmitting"><i data-lucide="loader-2" class="mr-2 h-4 w-4 animate-spin"></i></template>
                    SAVE &amp; UNPUBLISH
                </button>
                <button type="button" @click="submit('publish')" :disabled="isSubmitting"
                    class="inline-flex h-11 items-center justify-center rounded-md bg-[#1f2937] px-8 text-sm font-bold tracking-wide text-white transition hover:bg-black shadow-sm disabled:opacity-50">
                    <template x-if="isSubmitting"><i data-lucide="loader-2" class="mr-2 h-4 w-4 animate-spin"></i></template>
                    SAVE &amp; PUBLISH
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function addProductApp(token) {
    return {
        token,
        activeTab: 0,
        tabs: [
            'Product Information',
            'Images & Videos',
            'Product Price & Stock',
            'Description & Specification',
            'Shipping Info',
            'Others',
            'SEO',
        ],
        form: {
            productName: '',
            category: 'Select Category',
            brand: '',
            unit: '',
            minimumOrderQuantity: '1',
            barcode: '',
            tags: '',
            slug: '',
            isDigitalProduct: false,
            currencyCode: 'UGX',
            taxClass: 'Standard',
            lowStockThreshold: '5',
            shortDescription: '',
            description: '',
            shippingWeight: '',
            shippingLength: '',
            shippingWidth: '',
            shippingHeight: '',
            shippingClass: 'Standard Parcel',
            dispatchTime: '1-2 business days',
            shippingNotes: '',
            productType: 'Physical',
            condition: 'New',
            visibility: 'Published',
            warranty: '7 days',
            featured: false,
            returnable: true,
            seoTitle: '',
            seoDescription: '',
            seoKeywords: '',
        },
        media: [],
        newMediaUrl: '',
        isDragging: false,
        uploadingCount: 0,
        variants: [{ id: crypto.randomUUID(), label: 'Default', sku: '', price: '', compareAtPrice: '', stockQty: '' }],
        specs: [],
        bullets: ['', '', ''],
        isSubmitting: false,
        message: '',
        messageTone: 'success',

        init() {
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        },

        async handleFileSelect(files) {
            if (!files || !files.length) return;
            const fileArr = Array.from(files);
            for (const file of fileArr) {
                const isVideo = file.type.startsWith('video/');
                const kind    = isVideo ? 'video' : 'image';
                const id      = crypto.randomUUID();
                const preview = URL.createObjectURL(file);
                this.media.push({ id, name: file.name, url: preview, kind, uploading: true });
                this.uploadingCount++;
                this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
                try {
                    const remoteUrl = await uploadFile(file, this.token);
                    URL.revokeObjectURL(preview);
                    this.media = this.media.map(m => m.id === id ? { ...m, url: remoteUrl, uploading: false } : m);
                } catch (err) {
                    this.media = this.media.filter(m => m.id !== id);
                    alert('Upload failed: ' + (err.message || 'Unknown error'));
                } finally {
                    this.uploadingCount = Math.max(0, this.uploadingCount - 1);
                }
            }
        },

        autoSlug() {
            if (!this.form.slug) {
                this.form.slug = this.slugify(this.form.productName);
            }
        },

        slugify(text) {
            return text.toString().toLowerCase()
                .replace(/\s+/g, '-')
                .replace(/[^\w\-]+/g, '')
                .replace(/\-\-+/g, '-')
                .replace(/^-+/, '')
                .replace(/-+$/, '');
        },

        addMedia() {
            const url = this.newMediaUrl.trim();
            if (!url) return;
            const ext = url.split('?')[0].split('.').pop().toLowerCase();
            const videoExts = ['mp4', 'webm', 'ogg', 'mov'];
            const kind = videoExts.includes(ext) ? 'video' : 'image';
            const name = url.split('/').pop().split('?')[0] || url;
            this.media.push({ id: crypto.randomUUID(), name, url, kind });
            this.newMediaUrl = '';
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        },

        removeMedia(id) {
            this.media = this.media.filter(m => m.id !== id);
        },

        addVariant() {
            this.variants.push({
                id: crypto.randomUUID(),
                label: 'Variant ' + (this.variants.length + 1),
                sku: '',
                price: '',
                compareAtPrice: '',
                stockQty: '',
            });
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        },

        removeVariant(id) {
            this.variants = this.variants.filter(v => v.id !== id);
        },

        addSpec() {
            this.specs.push({ id: crypto.randomUUID(), label: '', value: '' });
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        },

        removeSpec(id) {
            this.specs = this.specs.filter(s => s.id !== id);
        },

        removeBullet(index) {
            this.bullets.splice(index, 1);
        },

        buildSpecs() {
            const system = [];
            if (this.form.unit)                  system.push({ label: 'Unit',                   value: this.form.unit });
            if (this.form.minimumOrderQuantity)   system.push({ label: 'Minimum Order Quantity', value: this.form.minimumOrderQuantity });
            if (this.form.barcode)                system.push({ label: 'Barcode',                value: this.form.barcode });
            if (this.form.tags)                   system.push({ label: 'Tags',                   value: this.form.tags });
            if (this.form.taxClass)               system.push({ label: 'Tax Class',              value: this.form.taxClass });
            if (this.form.lowStockThreshold)      system.push({ label: 'Low Stock Alert',        value: this.form.lowStockThreshold });
            if (this.form.productType)            system.push({ label: 'Product Type',           value: this.form.productType });
            if (this.form.condition)              system.push({ label: 'Condition',              value: this.form.condition });
            if (this.form.warranty)               system.push({ label: 'Warranty',               value: this.form.warranty });
            if (this.form.shippingNotes)          system.push({ label: 'Shipping Notes',         value: this.form.shippingNotes });
            if (this.form.seoTitle)               system.push({ label: 'SEO Title',              value: this.form.seoTitle });
            if (this.form.seoDescription)         system.push({ label: 'SEO Description',        value: this.form.seoDescription });
            if (this.form.seoKeywords)            system.push({ label: 'SEO Keywords',           value: this.form.seoKeywords });
            const custom = this.specs.filter(s => s.label.trim() && s.value.trim());
            return [...system, ...custom];
        },

        async submit(action) {
            if (!this.form.productName.trim()) {
                alert('Product name is required.');
                return;
            }
            if (this.form.category === 'Select Category') {
                alert('Please select a category.');
                return;
            }
            if (this.variants.some(v => !v.price.trim())) {
                this.activeTab = 2;
                alert('Every variant needs a price.');
                return;
            }
            if (this.uploadingCount > 0) {
                this.activeTab = 1;
                alert('Please wait for all uploads to finish.');
                return;
            }

            this.isSubmitting = true;
            this.message = '';

            try {
                const response = await fetch('/api/admin/products', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + this.token,
                    },
                    body: JSON.stringify({
                        action,
                        name: this.form.productName,
                        slug: this.form.slug || this.slugify(this.form.productName),
                        categoryName: this.form.category,
                        brand: this.form.brand,
                        currencyCode: this.form.currencyCode,
                        shortDescription: this.form.shortDescription,
                        description: this.form.description,
                        media: this.media.map(m => ({ url: m.url, kind: m.kind, altText: this.form.productName })),
                        variants: this.variants,
                        specs: this.buildSpecs(),
                        bullets: this.bullets.filter(b => b.trim()),
                        featured: this.form.featured,
                        shippingLabel: this.form.shippingClass + (this.form.shippingWeight ? ' | ' + this.form.shippingWeight + 'kg' : ''),
                        deliveryLabel: this.form.dispatchTime,
                        returnsLabel: this.form.returnable ? ('Returns allowed | ' + this.form.warranty) : 'Not returnable',
                        paymentLabel: 'Tax: ' + this.form.taxClass,
                        bestsellerLabel: this.form.featured ? 'Featured Product' : '',
                        bestsellerCategory: this.form.category,
                        boughtPastMonthLabel: this.form.tags ? 'Tags: ' + this.form.tags : '',
                        publishState: action,
                    }),
                });

                const text = await response.text();
                let payload = {};
                try { payload = JSON.parse(text); } catch { payload = { error: text }; }

                if (!response.ok) throw new Error(payload.error || 'Failed to save product.');

                this.messageTone = 'success';
                this.message = action === 'publish'
                    ? 'Product published successfully.'
                    : 'Product saved as draft.';

                // Reset form
                this.form = {
                    productName: '', category: 'Select Category', brand: '', unit: '',
                    minimumOrderQuantity: '1', barcode: '', tags: '', slug: '',
                    isDigitalProduct: false, currencyCode: 'UGX', taxClass: 'Standard',
                    lowStockThreshold: '5', shortDescription: '', description: '',
                    shippingWeight: '', shippingLength: '', shippingWidth: '', shippingHeight: '',
                    shippingClass: 'Standard Parcel', dispatchTime: '1-2 business days',
                    shippingNotes: '', productType: 'Physical', condition: 'New',
                    visibility: 'Published', warranty: '7 days', featured: false,
                    returnable: true, seoTitle: '', seoDescription: '', seoKeywords: '',
                };
                this.media = [];
                this.variants = [{ id: crypto.randomUUID(), label: 'Default', sku: '', price: '', compareAtPrice: '', stockQty: '' }];
                this.specs = [];
                this.bullets = ['', '', ''];
                this.activeTab = 0;

            } catch (err) {
                this.messageTone = 'error';
                this.message = err.message || 'Failed to save product.';
            } finally {
                this.isSubmitting = false;
            }
        },
    };
}
</script>
@endpush
