@extends('admin.layout')

@section('title', 'Add New Product')

@section('content')
<div
    class="-mx-4 -mt-4 sm:-mx-6 sm:-mt-6 min-h-screen bg-[#eaeded] px-4 pt-4 pb-16 sm:px-6 sm:pt-6"
    x-data="addProductApp('{{ session('admin_token') }}', @js($categories), @js($editId))"
    x-init="init()"
>
    {{-- Header --}}
    <div class="mb-5">
        <h1 class="text-[22px] font-bold text-[#0f1111]" x-text="editId ? 'Edit Product' : 'Add New Product'">Add New Product</h1>
        <p class="mt-0.5 text-[13px] text-[#565959]" x-text="editId ? 'Update the saved product information below.' : 'Fill in the information below to register a new product.'"></p>
    </div>

    <div x-show="isLoadingProduct" x-cloak class="mb-4 flex items-center gap-2 rounded-md border border-[#d5d9d9] bg-white px-4 py-3 text-[13px] text-[#565959]">
        <i data-lucide="loader-2" class="h-4 w-4 animate-spin"></i>
        Loading product information...
    </div>

    {{-- Tab nav --}}
    <div class="overflow-x-auto rounded-lg border border-[#d5d9d9] bg-white shadow-[0_1px_3px_rgba(15,17,17,0.08)]">
        <div class="flex min-w-max items-center gap-1 px-3 py-2">
            <template x-for="(tab, index) in tabs" :key="tab">
                <button
                    type="button"
                    @click="activeTab = index"
                    :class="index === activeTab
                        ? 'border-[#fcd200] bg-[#ffd814] text-[#0f1111]'
                        : 'border-transparent text-[#565959] hover:bg-[#f0f2f2] hover:text-[#0f1111]'"
                    class="rounded-md border px-4 py-2 text-[13px] font-medium transition"
                    x-text="tab"
                ></button>
            </template>
        </div>
    </div>

    {{-- Tab content card --}}
    <section class="mt-4 overflow-hidden rounded-lg border border-[#d5d9d9] bg-white shadow-[0_1px_3px_rgba(15,17,17,0.08)]">
        <div class="border-b border-[#d5d9d9] bg-[#f7fafa] px-5 py-4">
            <h2 class="text-[15px] font-bold text-[#0f1111]" x-text="tabs[activeTab]"></h2>
        </div>

        <div class="px-5 py-6">

            {{-- TAB 0: Product Information --}}
            <div x-show="activeTab === 0" class="space-y-5">
                <div>
                    <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Product Name <span class="text-[#b12704]">*</span></label>
                    <input x-model="form.productName" @input="autoSlug()" type="text" placeholder="e.g. Samsung Galaxy S24 Ultra"
                        class="h-10 w-full rounded-md border border-[#a6a6a6] bg-white px-3 text-[14px] text-[#0f1111] outline-none shadow-[inset_0_1px_2px_rgba(15,17,17,0.06)] placeholder:text-[#8a8f98] focus:border-[#007185] focus:shadow-[inset_0_1px_2px_rgba(15,17,17,0.06),0_0_0_3px_rgba(0,113,133,0.1)] transition-shadow" />
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Category <span class="text-[#b12704]">*</span></label>
                        <div class="grid gap-3">
                            <div class="relative">
                                <select x-model="form.mainCategoryId" @change="selectMainCategory()"
                                    :class="form.mainCategoryId === '' ? 'text-[#8a8f98]' : 'text-[#0f1111]'"
                                    class="h-10 w-full appearance-none rounded-md border border-[#a6a6a6] bg-white px-3 pr-10 text-[14px] outline-none focus:border-[#007185] transition">
                                    <option value="">Select main category</option>
                                    <template x-for="category in mainCategories" :key="category.id">
                                        <option :value="category.id" x-text="category.name"></option>
                                    </template>
                                </select>
                                <i data-lucide="chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#565959]"></i>
                            </div>

                            <div class="relative" x-show="subCategories.length > 0" x-cloak>
                                <select x-model="form.subCategoryId" @change="selectSubCategory()"
                                    :class="form.subCategoryId === '' ? 'text-[#8a8f98]' : 'text-[#0f1111]'"
                                    class="h-10 w-full appearance-none rounded-md border border-[#a6a6a6] bg-white px-3 pr-10 text-[14px] outline-none focus:border-[#007185] transition">
                                    <option value="">Select subcategory</option>
                                    <template x-for="category in subCategories" :key="category.id">
                                        <option :value="category.id" x-text="category.name"></option>
                                    </template>
                                </select>
                                <i data-lucide="chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#565959]"></i>
                            </div>

                            <div class="relative" x-show="subSubCategories.length > 0" x-cloak>
                                <select x-model="form.subSubCategoryId" @change="selectSubSubCategory()"
                                    :class="form.subSubCategoryId === '' ? 'text-[#8a8f98]' : 'text-[#0f1111]'"
                                    class="h-10 w-full appearance-none rounded-md border border-[#a6a6a6] bg-white px-3 pr-10 text-[14px] outline-none focus:border-[#007185] transition">
                                    <option value="">Select sub-subcategory</option>
                                    <template x-for="category in subSubCategories" :key="category.id">
                                        <option :value="category.id" x-text="category.name"></option>
                                    </template>
                                </select>
                                <i data-lucide="chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#565959]"></i>
                            </div>

                            <p class="text-[12px] text-[#565959]" x-show="selectedCategoryName" x-cloak>
                                Selected: <span class="font-semibold text-[#0f1111]" x-text="selectedCategoryPath"></span>
                            </p>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Brand</label>
                        <input x-model="form.brand" type="text" placeholder="e.g. Samsung, Sony, LG"
                            class="h-10 w-full rounded-md border border-[#a6a6a6] bg-white px-3 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition placeholder:text-[#8a8f98]" />
                    </div>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Unit <span class="text-[#b12704]">*</span></label>
                        <input x-model="form.unit" type="text" placeholder="e.g kg, pc, box"
                            class="h-10 w-full rounded-md border border-[#a6a6a6] bg-white px-3 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition placeholder:text-[#8a8f98]" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Min. Order Quantity <span class="text-[#b12704]">*</span></label>
                        <input x-model="form.minimumOrderQuantity" type="number" min="1"
                            class="h-10 w-full rounded-md border border-[#a6a6a6] bg-white px-3 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition" />
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Barcode</label>
                    <input x-model="form.barcode" type="text" placeholder="Enter product barcode"
                        class="h-10 w-full rounded-md border border-[#a6a6a6] bg-white px-3 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition placeholder:text-[#8a8f98]" />
                </div>
                <div>
                    <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Tags</label>
                    <input x-model="form.tags" type="text" placeholder="smart tv, samsung, 4k"
                        class="h-10 w-full rounded-md border border-[#a6a6a6] bg-white px-3 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition placeholder:text-[#8a8f98]" />
                </div>
                <div>
                    <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Slug</label>
                    <input x-model="form.slug" type="text" placeholder="product-slug-here"
                        class="h-10 w-full rounded-md border border-[#a6a6a6] bg-white px-3 font-mono text-[13px] text-[#0f1111] outline-none focus:border-[#007185] transition placeholder:text-[#8a8f98]" />
                </div>
                <div class="flex items-center justify-between rounded-md border border-[#d5d9d9] bg-[#f7fafa] px-4 py-3">
                    <div>
                        <div class="text-[13px] font-bold text-[#0f1111]">Digital Product</div>
                        <div class="mt-0.5 text-[12px] text-[#565959]">If enabled, this product won't require shipping.</div>
                    </div>
                    <button type="button" @click="form.isDigitalProduct = !form.isDigitalProduct"
                        :class="form.isDigitalProduct ? 'bg-[#007185]' : 'bg-[#d5d9d9]'"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200">
                        <span :class="form.isDigitalProduct ? 'translate-x-5' : 'translate-x-0'"
                            class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200"></span>
                    </button>
                </div>
            </div>

            {{-- TAB 1: Images & Videos --}}
            <div x-show="activeTab === 1" class="space-y-5">
                <input id="media-file-input" type="file" accept="image/*,video/*" multiple class="hidden"
                    @change="handleFileSelect($event.target.files); $event.target.value = '';" />

                <div
                    class="rounded-md border-2 border-dashed border-[#d5d9d9] bg-[#f7fafa] px-6 py-10 text-center transition hover:border-[#007185] hover:bg-[#eaf5f5]/30"
                    :class="isDragging ? 'border-[#007185] bg-[#eaf5f5]/40' : ''"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="isDragging = false; handleFileSelect($event.dataTransfer.files)"
                >
                    <div class="mx-auto max-w-sm">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg border border-[#d5d9d9] bg-white">
                            <i data-lucide="upload-cloud" class="h-6 w-6 text-[#007185]"></i>
                        </div>
                        <div class="mt-3 text-[14px] font-semibold text-[#0f1111]">Upload product images &amp; videos</div>
                        <p class="mt-1 text-[13px] text-[#565959]">Drag &amp; drop files here, or click to browse</p>
                        <div class="mt-1 text-[12px] text-[#8a8f98]">JPG, PNG, WebP, GIF, SVG, MP4 · Max 20 MB per file</div>
                        <button type="button" @click="document.getElementById('media-file-input').click()"
                            class="mt-5 inline-flex items-center gap-2 rounded-md border border-[#d5d9d9] bg-white px-4 py-2 text-[13px] font-bold text-[#565959] transition hover:bg-[#f0f2f2]">
                            <i data-lucide="folder-open" class="h-4 w-4"></i> Choose files
                        </button>
                    </div>
                </div>

                <div x-show="uploadingCount > 0" class="rounded-md border border-[#007185]/30 bg-[#eaf5f5] px-4 py-3">
                    <div class="flex items-center gap-2 text-[13px] font-medium text-[#007185]">
                        <i data-lucide="loader-2" class="h-4 w-4 animate-spin"></i>
                        Uploading <span x-text="uploadingCount"></span> file(s)…
                    </div>
                </div>

                <div>
                    <div class="mb-1.5 text-[13px] font-bold text-[#0f1111]">Or add by URL</div>
                    <div class="flex gap-2">
                        <input x-model="newMediaUrl" type="url" placeholder="https://example.com/image.jpg"
                            @keydown.enter.prevent="addMedia()"
                            class="h-10 flex-1 rounded-md border border-[#a6a6a6] bg-white px-3 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition placeholder:text-[#8a8f98]" />
                        <button type="button" @click="addMedia()"
                            class="inline-flex h-10 items-center gap-2 rounded-md border border-[#d5d9d9] bg-white px-3 text-[13px] font-bold text-[#565959] transition hover:bg-[#f0f2f2]">
                            <i data-lucide="plus" class="h-4 w-4"></i> Add
                        </button>
                    </div>
                </div>

                <div x-show="media.length > 0" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <template x-for="(item, idx) in media" :key="item.id">
                        <div class="overflow-hidden rounded-md border border-[#d5d9d9] bg-white">
                            <div class="relative flex h-44 items-center justify-center bg-[#f0f2f2] p-3">
                                <template x-if="item.uploading">
                                    <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-white/80 backdrop-blur-sm">
                                        <i data-lucide="loader-2" class="h-7 w-7 animate-spin text-[#007185]"></i>
                                        <span class="text-[12px] font-semibold text-[#565959]">Uploading…</span>
                                    </div>
                                </template>
                                <template x-if="!item.uploading && item.kind === 'image'">
                                    <img :src="item.url" :alt="item.name" class="max-h-full w-full object-contain" />
                                </template>
                                <template x-if="!item.uploading && item.kind === 'video'">
                                    <video :src="item.url" class="max-h-full w-full rounded object-cover" controls></video>
                                </template>
                            </div>
                            <div class="flex items-center justify-between gap-3 border-t border-[#d5d9d9] px-3 py-2.5">
                                <div class="min-w-0">
                                    <div class="truncate text-[13px] font-semibold text-[#0f1111]" x-text="item.name"></div>
                                    <div class="text-[11px] uppercase tracking-wider text-[#8a8f98]"
                                         x-text="item.uploading ? 'Uploading…' : item.kind"></div>
                                </div>
                                <button type="button" @click="removeMedia(item.id)" :disabled="item.uploading"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-[#d5d9d9] bg-white text-[#565959] transition hover:border-[#b12704] hover:text-[#b12704] disabled:opacity-40">
                                    <i data-lucide="x" class="h-4 w-4"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- TAB 2: Price & Stock --}}
            <div x-show="activeTab === 2" class="space-y-6">
                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Currency <span class="text-[#b12704]">*</span></label>
                        <div class="relative">
                            <select x-model="form.currencyCode"
                                class="h-10 w-full appearance-none rounded-md border border-[#a6a6a6] bg-white px-3 pr-10 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition">
                                <option>UGX</option><option>USD</option><option>KES</option>
                            </select>
                            <i data-lucide="chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#565959]"></i>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Low Stock Alert</label>
                        <input x-model="form.lowStockThreshold" type="number" min="0"
                            class="h-10 w-full rounded-md border border-[#a6a6a6] bg-white px-3 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Tax Class</label>
                        <div class="relative">
                            <select x-model="form.taxClass"
                                class="h-10 w-full appearance-none rounded-md border border-[#a6a6a6] bg-white px-3 pr-10 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition">
                                <option>Standard</option><option>Zero Rated</option><option>Exempt</option>
                            </select>
                            <i data-lucide="chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#565959]"></i>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <template x-for="(variant, index) in variants" :key="variant.id">
                        <div class="rounded-md border border-[#d5d9d9] bg-[#f7fafa] p-4">
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <div class="text-[13px] font-bold text-[#0f1111]" x-text="'Variant ' + (index + 1)"></div>
                                <button x-show="variants.length > 1" type="button" @click="removeVariant(variant.id)"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-[#d5d9d9] bg-white text-[#565959] transition hover:border-[#b12704] hover:text-[#b12704]">
                                    <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                </button>
                            </div>
                            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                                <div>
                                    <label class="mb-1 block text-[12px] font-bold text-[#565959]">Variant Name</label>
                                    <input x-model="variant.label" type="text" placeholder="Default"
                                        class="h-10 w-full rounded-md border border-[#a6a6a6] bg-white px-3 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-[12px] font-bold text-[#565959]">SKU</label>
                                    <input x-model="variant.sku" type="text" placeholder="SKU-001"
                                        class="h-10 w-full rounded-md border border-[#a6a6a6] bg-white px-3 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-[12px] font-bold text-[#565959]">Price <span class="text-[#b12704]">*</span></label>
                                    <input x-model="variant.price" type="number" min="0" placeholder="120000"
                                        class="h-10 w-full rounded-md border border-[#a6a6a6] bg-white px-3 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-[12px] font-bold text-[#565959]">Compare At</label>
                                    <input x-model="variant.compareAtPrice" type="number" min="0" placeholder="150000"
                                        class="h-10 w-full rounded-md border border-[#a6a6a6] bg-white px-3 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-[12px] font-bold text-[#565959]">Stock Qty</label>
                                    <input x-model="variant.stockQty" type="number" min="0" placeholder="20"
                                        class="h-10 w-full rounded-md border border-[#a6a6a6] bg-white px-3 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition" />
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <button type="button" @click="addVariant()"
                    class="inline-flex items-center gap-2 rounded-md border border-[#d5d9d9] bg-white px-4 py-2 text-[13px] font-bold text-[#565959] transition hover:bg-[#f0f2f2]">
                    <i data-lucide="plus" class="h-4 w-4"></i> Add variant
                </button>
            </div>

            {{-- TAB 3: Description & Specification --}}
            <div x-show="activeTab === 3" class="space-y-6">
                <div>
                    <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Short Description</label>
                    <textarea x-model="form.shortDescription" rows="3"
                        placeholder="A short summary shown on cards and listing pages."
                        class="w-full rounded-md border border-[#a6a6a6] bg-white px-3 py-2.5 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition resize-none placeholder:text-[#8a8f98]"></textarea>
                </div>
                <div>
                    <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Full Description</label>
                    <textarea x-model="form.description" rows="7"
                        placeholder="Describe the product, features, compatibility, and what is included in the box."
                        class="w-full rounded-md border border-[#a6a6a6] bg-white px-3 py-2.5 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition placeholder:text-[#8a8f98]"></textarea>
                </div>

                <div>
                    <div class="mb-2 text-[13px] font-bold text-[#0f1111]">About This Item</div>
                    <div class="space-y-2">
                        <template x-for="(bullet, index) in bullets" :key="index">
                            <div class="flex gap-2">
                                <input :value="bullet" @input="bullets[index] = $event.target.value"
                                    :placeholder="'Bullet point ' + (index + 1)"
                                    class="h-10 w-full rounded-md border border-[#a6a6a6] bg-white px-3 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition placeholder:text-[#8a8f98]" />
                                <button type="button" @click="removeBullet(index)"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-[#d5d9d9] bg-white text-[#565959] transition hover:border-[#b12704] hover:text-[#b12704]">
                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="bullets.push('')"
                        class="mt-2 inline-flex items-center gap-2 rounded-md border border-[#d5d9d9] bg-white px-4 py-2 text-[13px] font-bold text-[#565959] transition hover:bg-[#f0f2f2]">
                        <i data-lucide="plus" class="h-4 w-4"></i> Add bullet
                    </button>
                </div>

                <div>
                    <div class="mb-2 text-[13px] font-bold text-[#0f1111]">Specifications</div>
                    <div class="space-y-2">
                        <template x-for="(spec, index) in specs" :key="spec.id">
                            <div class="grid gap-2 md:grid-cols-[1fr_1fr_auto]">
                                <input :value="spec.label" @input="specs[index].label = $event.target.value"
                                    placeholder="Spec label"
                                    class="h-10 rounded-md border border-[#a6a6a6] bg-white px-3 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition placeholder:text-[#8a8f98]" />
                                <input :value="spec.value" @input="specs[index].value = $event.target.value"
                                    placeholder="Spec value"
                                    class="h-10 rounded-md border border-[#a6a6a6] bg-white px-3 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition placeholder:text-[#8a8f98]" />
                                <button type="button" @click="removeSpec(spec.id)"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-[#d5d9d9] bg-white text-[#565959] transition hover:border-[#b12704] hover:text-[#b12704]">
                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="addSpec()"
                        class="mt-2 inline-flex items-center gap-2 rounded-md border border-[#d5d9d9] bg-white px-4 py-2 text-[13px] font-bold text-[#565959] transition hover:bg-[#f0f2f2]">
                        <i data-lucide="plus" class="h-4 w-4"></i> Add specification
                    </button>
                </div>
            </div>

            {{-- TAB 4: Shipping Info --}}
            <div x-show="activeTab === 4" class="space-y-6">
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Weight (kg)</label>
                        <input x-model="form.shippingWeight" type="number" min="0" step="0.01" placeholder="0.5"
                            class="h-10 w-full rounded-md border border-[#a6a6a6] bg-white px-3 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Length (cm)</label>
                        <input x-model="form.shippingLength" type="number" min="0" placeholder="20"
                            class="h-10 w-full rounded-md border border-[#a6a6a6] bg-white px-3 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Width (cm)</label>
                        <input x-model="form.shippingWidth" type="number" min="0" placeholder="10"
                            class="h-10 w-full rounded-md border border-[#a6a6a6] bg-white px-3 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Height (cm)</label>
                        <input x-model="form.shippingHeight" type="number" min="0" placeholder="8"
                            class="h-10 w-full rounded-md border border-[#a6a6a6] bg-white px-3 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition" />
                    </div>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Shipping Class</label>
                        <div class="relative">
                            <select x-model="form.shippingClass"
                                class="h-10 w-full appearance-none rounded-md border border-[#a6a6a6] bg-white px-3 pr-10 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition">
                                <option>Standard Parcel</option><option>Bulky</option><option>Express</option><option>Pickup Only</option>
                            </select>
                            <i data-lucide="chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#565959]"></i>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Dispatch Time</label>
                        <div class="relative">
                            <select x-model="form.dispatchTime"
                                class="h-10 w-full appearance-none rounded-md border border-[#a6a6a6] bg-white px-3 pr-10 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition">
                                <option>Same day</option><option>1-2 business days</option><option>3-5 business days</option><option>Pre-order</option>
                            </select>
                            <i data-lucide="chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#565959]"></i>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Shipping Notes</label>
                    <textarea x-model="form.shippingNotes" rows="4"
                        placeholder="Add packaging notes, delivery limitations, or special handling instructions."
                        class="w-full rounded-md border border-[#a6a6a6] bg-white px-3 py-2.5 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition placeholder:text-[#8a8f98]"></textarea>
                </div>
            </div>

            {{-- TAB 5: Others --}}
            <div x-show="activeTab === 5" class="space-y-5">
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Product Type</label>
                        <div class="relative">
                            <select x-model="form.productType"
                                class="h-10 w-full appearance-none rounded-md border border-[#a6a6a6] bg-white px-3 pr-10 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition">
                                <option>Physical</option><option>Digital</option><option>Service</option><option>Bundle</option>
                            </select>
                            <i data-lucide="chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#565959]"></i>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Condition</label>
                        <div class="relative">
                            <select x-model="form.condition"
                                class="h-10 w-full appearance-none rounded-md border border-[#a6a6a6] bg-white px-3 pr-10 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition">
                                <option>New</option><option>Refurbished</option><option>Used</option>
                            </select>
                            <i data-lucide="chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#565959]"></i>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Visibility</label>
                        <div class="relative">
                            <select x-model="form.visibility"
                                class="h-10 w-full appearance-none rounded-md border border-[#a6a6a6] bg-white px-3 pr-10 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition">
                                <option>Published</option><option>Draft</option><option>Hidden</option>
                            </select>
                            <i data-lucide="chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#565959]"></i>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Warranty</label>
                        <div class="relative">
                            <select x-model="form.warranty"
                                class="h-10 w-full appearance-none rounded-md border border-[#a6a6a6] bg-white px-3 pr-10 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition">
                                <option>No warranty</option><option>7 days</option><option>30 days</option><option>6 months</option><option>1 year</option>
                            </select>
                            <i data-lucide="chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#565959]"></i>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between rounded-md border border-[#d5d9d9] bg-[#f7fafa] px-4 py-3">
                    <div>
                        <div class="text-[13px] font-bold text-[#0f1111]">Featured Product</div>
                        <div class="mt-0.5 text-[12px] text-[#565959]">Highlight this item in featured storefront collections.</div>
                    </div>
                    <button type="button" @click="form.featured = !form.featured"
                        :class="form.featured ? 'bg-[#007185]' : 'bg-[#d5d9d9]'"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200">
                        <span :class="form.featured ? 'translate-x-5' : 'translate-x-0'"
                            class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200"></span>
                    </button>
                </div>
                <div class="flex items-center justify-between rounded-md border border-[#d5d9d9] bg-[#f7fafa] px-4 py-3">
                    <div>
                        <div class="text-[13px] font-bold text-[#0f1111]">Returnable</div>
                        <div class="mt-0.5 text-[12px] text-[#565959]">Allow the product to be included in return and refund workflows.</div>
                    </div>
                    <button type="button" @click="form.returnable = !form.returnable"
                        :class="form.returnable ? 'bg-[#007185]' : 'bg-[#d5d9d9]'"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200">
                        <span :class="form.returnable ? 'translate-x-5' : 'translate-x-0'"
                            class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200"></span>
                    </button>
                </div>
            </div>

            {{-- TAB 6: SEO --}}
            <div x-show="activeTab === 6" class="space-y-5">
                <div>
                    <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Meta Title</label>
                    <input x-model="form.seoTitle" type="text" placeholder="SEO title for search engines"
                        class="h-10 w-full rounded-md border border-[#a6a6a6] bg-white px-3 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition placeholder:text-[#8a8f98]" />
                </div>
                <div>
                    <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Meta Description</label>
                    <textarea x-model="form.seoDescription" rows="4"
                        placeholder="A concise search snippet for search engines."
                        class="w-full rounded-md border border-[#a6a6a6] bg-white px-3 py-2.5 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition placeholder:text-[#8a8f98]"></textarea>
                </div>
                <div>
                    <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Meta Keywords</label>
                    <input x-model="form.seoKeywords" type="text" placeholder="keyword 1, keyword 2, keyword 3"
                        class="h-10 w-full rounded-md border border-[#a6a6a6] bg-white px-3 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition placeholder:text-[#8a8f98]" />
                </div>
            </div>

        </div>
    </section>

    {{-- Status message --}}
    <div x-show="message"
        :class="messageTone === 'success'
            ? 'border-[#c3e6cb] bg-[#d4edda] text-[#155724]'
            : 'border-[#f5c6cb] bg-[#fef2f2] text-[#b12704]'"
        class="mt-4 rounded-md border px-4 py-3 text-[13px] font-medium"
        x-text="message">
    </div>

    {{-- Sticky footer --}}
    <div class="sticky bottom-0 z-10 mt-6 border-t border-[#d5d9d9] bg-white/95 px-4 py-4 backdrop-blur-md md:px-6">
        <div class="flex items-center justify-between gap-3">
            <a href="{{ route('dashboard.products') }}" class="text-[13px] font-bold text-[#565959] hover:text-[#0f1111] transition">← Back to products</a>
            <div class="flex items-center gap-2">
                <button type="button" @click="submit('draft')" :disabled="isSubmitting || isLoadingProduct"
                    class="inline-flex h-10 items-center justify-center rounded-md border border-[#d5d9d9] bg-white px-5 text-[13px] font-bold text-[#565959] transition hover:bg-[#f0f2f2] disabled:opacity-50">
                    <template x-if="isSubmitting"><i data-lucide="loader-2" class="mr-2 h-4 w-4 animate-spin"></i></template>
                    <span x-text="editId ? 'Update as Draft' : 'Save as Draft'"></span>
                </button>
                <button type="button" @click="submit('publish')" :disabled="isSubmitting || isLoadingProduct"
                    class="inline-flex h-10 items-center justify-center rounded-md border border-[#fcd200] bg-[#ffd814] px-6 text-[13px] font-medium text-[#0f1111] shadow-[inset_0_-1px_0_rgba(0,0,0,0.15)] transition hover:bg-[#f7ca00] disabled:opacity-50">
                    <template x-if="isSubmitting"><i data-lucide="loader-2" class="mr-2 h-4 w-4 animate-spin"></i></template>
                    <span x-text="editId ? 'Update & Publish' : 'Save & Publish'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const SYSTEM_SPEC_LABELS = new Set([
    'Unit', 'Minimum Order Quantity', 'Barcode', 'Tags', 'Tax Class',
    'Low Stock Alert', 'Product Type', 'Condition', 'Warranty',
    'Shipping Notes', 'SEO Title', 'SEO Description', 'SEO Keywords'
]);

function defaultProductForm() {
    return {
        productName: '', category: 'Select Category', categoryId: '',
        mainCategoryId: '', subCategoryId: '', subSubCategoryId: '',
        brand: '', unit: '',
        minimumOrderQuantity: '1', barcode: '', tags: '', slug: '',
        isDigitalProduct: false, currencyCode: 'UGX', taxClass: 'Standard',
        lowStockThreshold: '5', shortDescription: '', description: '',
        shippingWeight: '', shippingLength: '', shippingWidth: '', shippingHeight: '',
        shippingClass: 'Standard Parcel', dispatchTime: '1-2 business days',
        shippingNotes: '', productType: 'Physical', condition: 'New',
        visibility: 'Published', warranty: '7 days', featured: false,
        returnable: true, seoTitle: '', seoDescription: '', seoKeywords: '',
    };
}

function addProductApp(token, categories = [], editId = null) {
    return {
        token,
        editId,
        categories,
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
        form: defaultProductForm(),
        media: [],
        newMediaUrl: '',
        isDragging: false,
        uploadingCount: 0,
        variants: [{ id: crypto.randomUUID(), label: 'Default', sku: '', price: '', compareAtPrice: '', stockQty: '' }],
        specs: [],
        bullets: ['', '', ''],
        isSubmitting: false,
        isLoadingProduct: false,
        message: '',
        messageTone: 'success',

        init() {
            if (this.editId) this.loadProduct();
            this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
        },

        get mainCategories() {
            return this.childrenOf('');
        },

        get subCategories() {
            return this.childrenOf(this.form.mainCategoryId);
        },

        get subSubCategories() {
            return this.childrenOf(this.form.subCategoryId);
        },

        get selectedCategory() {
            return this.categories.find(category => category.id === this.form.categoryId) || null;
        },

        get selectedCategoryName() {
            return this.selectedCategory?.name || '';
        },

        get selectedCategoryPath() {
            const names = [];
            const main = this.categories.find(category => category.id === this.form.mainCategoryId);
            const sub = this.categories.find(category => category.id === this.form.subCategoryId);
            const subSub = this.categories.find(category => category.id === this.form.subSubCategoryId);
            if (main) names.push(main.name);
            if (sub) names.push(sub.name);
            if (subSub) names.push(subSub.name);
            return names.join(' / ');
        },

        childrenOf(parentId) {
            const normalizedParentId = parentId || null;
            return this.categories
                .filter(category => (category.parentId || null) === normalizedParentId)
                .sort((a, b) => (a.order || 0) - (b.order || 0) || a.name.localeCompare(b.name));
        },

        selectMainCategory() {
            this.form.subCategoryId = '';
            this.form.subSubCategoryId = '';
            this.setSelectedCategory(this.form.mainCategoryId);
        },

        selectSubCategory() {
            this.form.subSubCategoryId = '';
            this.setSelectedCategory(this.form.subCategoryId || this.form.mainCategoryId);
        },

        selectSubSubCategory() {
            this.setSelectedCategory(this.form.subSubCategoryId || this.form.subCategoryId || this.form.mainCategoryId);
        },

        setSelectedCategory(categoryId) {
            const category = this.categories.find(item => item.id === categoryId);
            this.form.categoryId = category?.id || '';
            this.form.category = category?.name || 'Select Category';
        },

        setCategorySelection(categoryId) {
            const chain = [];
            let current = this.categories.find(category => category.id === categoryId);
            while (current) {
                chain.unshift(current);
                current = this.categories.find(category => category.id === current.parentId);
            }

            this.form.mainCategoryId = chain[0]?.id || '';
            this.form.subCategoryId = chain[1]?.id || '';
            this.form.subSubCategoryId = chain[2]?.id || '';
            this.setSelectedCategory(categoryId || '');
        },

        async loadProduct() {
            this.isLoadingProduct = true;
            this.message = '';

            try {
                const response = await fetch(window.API_BASE + '/api/admin/products/' + this.editId, {
                    headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + this.token },
                });
                const text = await response.text();
                let payload = {};
                try { payload = JSON.parse(text); } catch { payload = { error: text }; }
                if (!response.ok || !payload.product) throw new Error(payload.error || 'Failed to load product.');

                this.applyProduct(payload.product);
            } catch (err) {
                this.messageTone = 'error';
                this.message = err.message || 'Failed to load product.';
            } finally {
                this.isLoadingProduct = false;
                this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
            }
        },

        applyProduct(product) {
            const specsByLabel = new Map((product.specs || []).map(spec => [spec.label, spec.value]));
            const shippingParts = (product.shippingLabel || '').split('|').map(part => part.trim());
            const weightMatch = (shippingParts[1] || '').match(/([\d.]+)/);

            this.form = {
                ...defaultProductForm(),
                productName: product.name || '',
                slug: product.slug || '',
                category: product.category || 'Select Category',
                categoryId: product.categoryId || '',
                brand: product.brand && product.brand !== 'Select Brand' ? product.brand : '',
                currencyCode: product.currencyCode || 'UGX',
                shortDescription: product.shortDescription || '',
                description: product.description || '',
                featured: Boolean(product.featured),
                visibility: product.isPublished ? 'Published' : 'Draft',
                unit: specsByLabel.get('Unit') || '',
                minimumOrderQuantity: specsByLabel.get('Minimum Order Quantity') || '1',
                barcode: specsByLabel.get('Barcode') || '',
                tags: specsByLabel.get('Tags') || '',
                taxClass: specsByLabel.get('Tax Class') || 'Standard',
                lowStockThreshold: specsByLabel.get('Low Stock Alert') || '5',
                productType: specsByLabel.get('Product Type') || 'Physical',
                condition: specsByLabel.get('Condition') || 'New',
                warranty: specsByLabel.get('Warranty') || '7 days',
                shippingNotes: specsByLabel.get('Shipping Notes') || '',
                seoTitle: specsByLabel.get('SEO Title') || '',
                seoDescription: specsByLabel.get('SEO Description') || '',
                seoKeywords: specsByLabel.get('SEO Keywords') || '',
                returnable: !String(product.returnsLabel || '').toLowerCase().includes('not returnable'),
                shippingClass: shippingParts[0] || 'Standard Parcel',
                shippingWeight: weightMatch?.[1] || '',
                dispatchTime: product.deliveryLabel || '1-2 business days',
            };

            this.setCategorySelection(product.categoryId || '');
            this.media = (product.media || []).map(item => ({ ...item, uploading: false }));
            this.variants = product.variants?.length
                ? product.variants
                : [{ id: crypto.randomUUID(), label: 'Default', sku: '', price: '', compareAtPrice: '', stockQty: '' }];
            this.specs = (product.specs || []).filter(spec => !SYSTEM_SPEC_LABELS.has(spec.label));
            this.bullets = product.bullets?.length ? product.bullets : ['', '', ''];
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
            if (!this.form.slug) this.form.slug = this.slugify(this.form.productName);
        },

        slugify(text) {
            return text.toString().toLowerCase()
                .replace(/\s+/g, '-').replace(/[^\w\-]+/g, '')
                .replace(/\-\-+/g, '-').replace(/^-+/, '').replace(/-+$/, '');
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
            this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
        },

        removeMedia(id) { this.media = this.media.filter(m => m.id !== id); },

        addVariant() {
            this.variants.push({ id: crypto.randomUUID(), label: 'Variant ' + (this.variants.length + 1), sku: '', price: '', compareAtPrice: '', stockQty: '' });
            this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
        },

        removeVariant(id) { this.variants = this.variants.filter(v => v.id !== id); },

        addSpec() {
            this.specs.push({ id: crypto.randomUUID(), label: '', value: '' });
            this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
        },

        removeSpec(id) { this.specs = this.specs.filter(s => s.id !== id); },
        removeBullet(index) { this.bullets.splice(index, 1); },

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
            if (!this.form.productName.trim()) { alert('Product name is required.'); return; }
            if (!this.form.categoryId) { alert('Please select a category.'); return; }
            if (this.variants.some(v => !v.price.trim())) { this.activeTab = 2; alert('Every variant needs a price.'); return; }
            if (this.uploadingCount > 0) { this.activeTab = 1; alert('Please wait for all uploads to finish.'); return; }

            this.isSubmitting = true;
            this.message = '';

            try {
                const endpoint = this.editId
                    ? window.API_BASE + '/api/admin/products/' + this.editId
                    : window.API_BASE + '/api/admin/products';
                const response = await fetch(endpoint, {
                    method: this.editId ? 'PATCH' : 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + this.token },
                    body: JSON.stringify({
                        action,
                        name: this.form.productName,
                        slug: this.form.slug || this.slugify(this.form.productName),
                        categoryId: this.form.categoryId,
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
                this.message = this.editId
                    ? (action === 'publish' ? 'Product updated and published successfully.' : 'Product updated as draft.')
                    : (action === 'publish' ? 'Product published successfully.' : 'Product saved as draft.');

                if (!this.editId) {
                    this.form = defaultProductForm();
                    this.media = [];
                    this.variants = [{ id: crypto.randomUUID(), label: 'Default', sku: '', price: '', compareAtPrice: '', stockQty: '' }];
                    this.specs = [];
                    this.bullets = ['', '', ''];
                    this.activeTab = 0;
                }

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
