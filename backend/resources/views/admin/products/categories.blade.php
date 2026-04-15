@extends('admin.layout')

@section('title', 'Categories')

@section('content')
<script id="categories-json" type="application/json">{!! json_encode($categories->values()) !!}</script>

<div class="-mx-4 -mt-4 sm:-mx-6 sm:-mt-6 min-h-screen bg-[#eaeded] px-4 pt-6 pb-16 sm:px-6 sm:pt-8 lg:px-8"
    x-data="categoriesApp('{{ session('admin_token') }}')"
    x-init="init()">

    {{-- Header --}}
    <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-[22px] font-bold text-[#0f1111]">Categories</h1>
            <p class="mt-0.5 text-[13px] text-[#565959]">Manage product categories and sub-categories.</p>
        </div>
        <button type="button" @click="openForm(null)"
            class="inline-flex h-10 items-center gap-2 rounded-lg border border-[#fcd200] bg-[#ffd814] px-5 text-[14px] font-medium text-[#0f1111] shadow-[inset_0_-1px_0_rgba(0,0,0,0.15)] transition hover:bg-[#f7ca00] active:scale-[.99] flex-shrink-0">
            <i data-lucide="plus" class="h-4 w-4"></i> Add Category
        </button>
    </div>

    {{-- Stat cards --}}
    <div class="mb-5 grid gap-3 sm:grid-cols-3">
        <div class="rounded-lg border border-[#d5d9d9] bg-white p-4 shadow-[0_1px_3px_rgba(15,17,17,0.08)]">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-bold uppercase tracking-wider text-[#565959]">Total</span>
                <span class="flex h-7 w-7 items-center justify-center rounded-md bg-[#f0f2f2] text-[#565959]">
                    <i data-lucide="layers" class="h-3.5 w-3.5"></i>
                </span>
            </div>
            <div class="mt-2.5 text-[28px] font-bold leading-none text-[#0f1111]" x-text="categories.length"></div>
        </div>
        <div class="rounded-lg border border-[#d5d9d9] bg-white p-4 shadow-[0_1px_3px_rgba(15,17,17,0.08)]">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-bold uppercase tracking-wider text-[#565959]">Active</span>
                <span class="flex h-7 w-7 items-center justify-center rounded-md bg-[#eaf5f5] text-[#007185]">
                    <i data-lucide="circle-check" class="h-3.5 w-3.5"></i>
                </span>
            </div>
            <div class="mt-2.5 text-[28px] font-bold leading-none text-[#007185]" x-text="categories.filter(c => c.isActive).length"></div>
        </div>
        <div class="rounded-lg border border-[#d5d9d9] bg-white p-4 shadow-[0_1px_3px_rgba(15,17,17,0.08)]">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-bold uppercase tracking-wider text-[#565959]">Featured</span>
                <span class="flex h-7 w-7 items-center justify-center rounded-md bg-[#fef9e7] text-[#c45500]">
                    <i data-lucide="star" class="h-3.5 w-3.5"></i>
                </span>
            </div>
            <div class="mt-2.5 text-[28px] font-bold leading-none text-[#c45500]" x-text="categories.filter(c => c.featuredOnHome).length"></div>
        </div>
    </div>

    {{-- Table card --}}
    <div class="rounded-lg border border-[#d5d9d9] bg-white shadow-[0_1px_3px_rgba(15,17,17,0.08)]">

        <div class="flex items-center gap-2.5 border-b border-[#d5d9d9] px-4 py-3">
            <div class="flex flex-1 items-center gap-2.5 rounded-md border border-[#a6a6a6] px-3 py-2 shadow-[inset_0_1px_2px_rgba(15,17,17,0.06)] focus-within:border-[#007185] focus-within:shadow-[inset_0_1px_2px_rgba(15,17,17,0.06),0_0_0_3px_rgba(0,113,133,0.1)] transition-shadow">
                <i data-lucide="search" class="h-4 w-4 flex-shrink-0 text-[#565959]"></i>
                <input x-model="search" @input="currentPage = 1" type="text" placeholder="Search categories…"
                    class="flex-1 bg-transparent text-[14px] text-[#0f1111] outline-none placeholder:text-[#8a8f98]" />
            </div>
            <span class="whitespace-nowrap rounded-md border border-[#d5d9d9] bg-[#f0f2f2] px-3 py-1.5 text-[12px] font-bold text-[#565959]"
                x-text="filtered.length + ' result' + (filtered.length !== 1 ? 's' : '')"></span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-[14px]">
                <thead>
                    <tr class="border-b border-[#d5d9d9] bg-[#f7fafa]">
                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#565959]">Category</th>
                        <th class="hidden px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#565959] md:table-cell">Parent</th>
                        <th class="hidden px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#565959] lg:table-cell">Slug</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#565959]">Status</th>
                        <th class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-[#565959]">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0f2f2]">
                    <template x-for="cat in paginated" :key="cat.id">
                        <tr class="group transition hover:bg-[#f7fafa]">
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3">
                                    <template x-if="cat.imageUrl">
                                        <img :src="cat.imageUrl" :alt="cat.name"
                                            class="h-9 w-9 flex-shrink-0 rounded-md border border-[#d5d9d9] bg-white object-cover" />
                                    </template>
                                    <template x-if="!cat.imageUrl">
                                        <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-md border border-[#d5d9d9] bg-[#f0f2f2] text-[#565959]">
                                            <i data-lucide="layers" class="h-4 w-4"></i>
                                        </div>
                                    </template>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-[#0f1111]" x-text="cat.name"></div>
                                        <div class="text-[12px] text-[#565959] truncate max-w-[180px]" x-text="cat.description || '—'"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="hidden px-4 py-3.5 md:table-cell">
                                <span class="text-[13px] text-[#565959]" x-text="cat.parentName || '—'"></span>
                            </td>
                            <td class="hidden px-4 py-3.5 lg:table-cell">
                                <span class="font-mono text-[12px] text-[#565959]" x-text="cat.slug"></span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex flex-col gap-1.5">
                                    <span :class="cat.isActive
                                            ? 'border-[#c3e6cb] bg-[#d4edda] text-[#155724]'
                                            : 'border-[#d5d9d9] bg-[#f0f2f2] text-[#565959]'"
                                        class="inline-flex w-fit items-center gap-1 rounded border px-2 py-0.5 text-[12px] font-bold">
                                        <span :class="cat.isActive ? 'bg-[#28a745]' : 'bg-[#a6a6a6]'"
                                            class="h-1.5 w-1.5 rounded-full"></span>
                                        <span x-text="cat.isActive ? 'Active' : 'Inactive'"></span>
                                    </span>
                                    <span x-show="cat.featuredOnHome"
                                        class="inline-flex w-fit items-center gap-1 rounded border border-[#f5cba7] bg-[#fef5ec] px-2 py-0.5 text-[12px] font-bold text-[#c45500]">
                                        <i data-lucide="star" class="h-3 w-3"></i> Featured
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" @click="openForm(cat)"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-[#d5d9d9] bg-white text-[#565959] transition hover:border-[#007185] hover:text-[#007185]">
                                        <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                    </button>
                                    <button type="button" @click="deleteCategory(cat)"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-[#d5d9d9] bg-white text-[#565959] transition hover:border-[#b12704] hover:text-[#b12704]">
                                        <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filtered.length === 0">
                        <td colspan="5" class="px-4 py-14 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-lg border border-[#d5d9d9] bg-[#f0f2f2] text-[#565959]">
                                    <i data-lucide="layers" class="h-5 w-5 opacity-50"></i>
                                </div>
                                <p class="text-[14px] font-semibold text-[#0f1111]">No categories found</p>
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
            </span>
            <div class="flex gap-1.5">
                <button @click="currentPage = Math.max(1, currentPage - 1)" :disabled="currentPage === 1"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-[#d5d9d9] bg-white text-[#565959] transition hover:bg-[#f0f2f2] disabled:opacity-40">
                    <i data-lucide="chevron-left" class="h-4 w-4"></i>
                </button>
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

    {{-- ── Slide-over form ── --}}
    <div x-show="showForm" x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 z-50 flex w-full max-w-md flex-col border-l border-[#d5d9d9] bg-white shadow-2xl">

        <div class="flex items-center justify-between border-b border-[#d5d9d9] bg-[#f7fafa] px-5 py-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-[#565959]">Categories</p>
                <h2 class="text-[16px] font-bold text-[#0f1111]" x-text="editingId ? 'Edit Category' : 'Add New Category'"></h2>
            </div>
            <button @click="showForm = false"
                class="flex h-8 w-8 items-center justify-center rounded-md border border-[#d5d9d9] bg-white text-[#565959] transition hover:text-[#0f1111]">
                <i data-lucide="x" class="h-4 w-4"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-5 py-5 space-y-4">

            <div>
                <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Name <span class="text-[#b12704]">*</span></label>
                <input x-model="form.name" @input="autoSlug()" type="text" placeholder="e.g. Televisions"
                    class="h-10 w-full rounded-md border border-[#a6a6a6] px-3 text-[14px] text-[#0f1111] outline-none shadow-[inset_0_1px_2px_rgba(15,17,17,0.06)] placeholder:text-[#8a8f98] focus:border-[#007185] focus:shadow-[inset_0_1px_2px_rgba(15,17,17,0.06),0_0_0_3px_rgba(0,113,133,0.1)] transition-shadow" />
            </div>

            <div>
                <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Slug</label>
                <input x-model="form.slug" type="text" placeholder="televisions"
                    class="h-10 w-full rounded-md border border-[#a6a6a6] px-3 font-mono text-[13px] text-[#0f1111] outline-none shadow-[inset_0_1px_2px_rgba(15,17,17,0.06)] placeholder:text-[#8a8f98] focus:border-[#007185] focus:shadow-[inset_0_1px_2px_rgba(15,17,17,0.06),0_0_0_3px_rgba(0,113,133,0.1)] transition-shadow" />
            </div>

            <div>
                <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Parent Category</label>
                <div class="relative">
                    <select x-model="form.parentId"
                        class="h-10 w-full appearance-none rounded-md border border-[#a6a6a6] px-3 pr-10 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition">
                        <option value="">None (top-level)</option>
                        <template x-for="cat in categories.filter(c => c.id !== editingId)" :key="cat.id">
                            <option :value="cat.id" x-text="cat.name"></option>
                        </template>
                    </select>
                    <i data-lucide="chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#565959]"></i>
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Description</label>
                <textarea x-model="form.description" rows="3" placeholder="Short description of this category..."
                    class="w-full rounded-md border border-[#a6a6a6] px-3 py-2.5 text-[14px] text-[#0f1111] outline-none shadow-[inset_0_1px_2px_rgba(15,17,17,0.06)] placeholder:text-[#8a8f98] focus:border-[#007185] focus:shadow-[inset_0_1px_2px_rgba(15,17,17,0.06),0_0_0_3px_rgba(0,113,133,0.1)] transition-shadow resize-none"></textarea>
            </div>

            <div>
                <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Category Image</label>
                <div x-show="form.imageUrl" class="mb-2 flex items-center gap-3">
                    <img :src="form.imageUrl"
                        class="h-12 w-12 rounded-md border border-[#d5d9d9] bg-white object-contain p-1" />
                    <button type="button" @click="form.imageUrl = ''"
                        class="inline-flex h-7 items-center gap-1 rounded border border-[#d5d9d9] bg-[#f0f2f2] px-2.5 text-[12px] font-bold text-[#565959] hover:border-[#b12704] hover:text-[#b12704] transition">
                        <i data-lucide="x" class="h-3 w-3"></i> Remove
                    </button>
                </div>
                <input id="cat-img-input" type="file" accept="image/*" class="hidden"
                    @change="uploadCategoryImage($event.target.files[0]); $event.target.value = '';" />
                <div class="flex gap-2">
                    <button type="button" @click="document.getElementById('cat-img-input').click()"
                        :disabled="catImgUploading"
                        class="inline-flex h-10 items-center gap-2 rounded-md border border-[#d5d9d9] bg-[#f0f2f2] px-3 text-[13px] font-bold text-[#0f1111] transition hover:bg-[#e3e6e6] disabled:opacity-50">
                        <template x-if="catImgUploading">
                            <i data-lucide="loader-2" class="h-3.5 w-3.5 animate-spin"></i>
                        </template>
                        <template x-if="!catImgUploading">
                            <i data-lucide="upload" class="h-3.5 w-3.5"></i>
                        </template>
                        <span x-text="catImgUploading ? 'Uploading…' : 'Upload'"></span>
                    </button>
                    <input x-model="form.imageUrl" type="url" placeholder="or paste URL"
                        class="h-10 flex-1 rounded-md border border-[#a6a6a6] px-3 text-[13px] text-[#0f1111] outline-none focus:border-[#007185] transition" />
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Sort Order</label>
                <input x-model="form.sortOrder" type="number" min="0"
                    class="h-10 w-full rounded-md border border-[#a6a6a6] px-3 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition" />
            </div>

            <div class="border-t border-[#d5d9d9]"></div>

            <div class="flex items-center justify-between rounded-md border border-[#d5d9d9] bg-[#f7fafa] px-4 py-3">
                <div>
                    <div class="text-[13px] font-bold text-[#0f1111]">Active</div>
                    <div class="text-[12px] text-[#565959]">Visible to shoppers</div>
                </div>
                <button type="button" @click="form.isActive = !form.isActive"
                    :class="form.isActive ? 'bg-[#007185]' : 'bg-[#d5d9d9]'"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200">
                    <span :class="form.isActive ? 'translate-x-5' : 'translate-x-0'"
                        class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200"></span>
                </button>
            </div>

            <div class="flex items-center justify-between rounded-md border border-[#d5d9d9] bg-[#f7fafa] px-4 py-3">
                <div>
                    <div class="text-[13px] font-bold text-[#0f1111]">Featured on Home</div>
                    <div class="text-[12px] text-[#565959]">Show in homepage category grid</div>
                </div>
                <button type="button" @click="form.featuredOnHome = !form.featuredOnHome"
                    :class="form.featuredOnHome ? 'bg-[#007185]' : 'bg-[#d5d9d9]'"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200">
                    <span :class="form.featuredOnHome ? 'translate-x-5' : 'translate-x-0'"
                        class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200"></span>
                </button>
            </div>

            <div x-show="formMessage"
                :class="formTone === 'success'
                    ? 'border-[#c3e6cb] bg-[#d4edda] text-[#155724]'
                    : 'border-[#f5c6cb] bg-[#fef2f2] text-[#b12704]'"
                class="rounded-md border px-4 py-3 text-[13px] font-medium"
                x-text="formMessage">
            </div>
        </div>

        <div class="border-t border-[#d5d9d9] bg-[#f7fafa] px-5 py-4">
            <div class="flex gap-2">
                <button type="button" @click="showForm = false"
                    class="flex-1 h-10 rounded-md border border-[#d5d9d9] bg-white text-[13px] font-bold text-[#565959] transition hover:bg-[#f0f2f2]">
                    Cancel
                </button>
                <button type="button" @click="saveCategory()" :disabled="isSaving"
                    class="flex-1 h-10 rounded-md border border-[#fcd200] bg-[#ffd814] text-[13px] font-medium text-[#0f1111] shadow-[inset_0_-1px_0_rgba(0,0,0,0.15)] transition hover:bg-[#f7ca00] disabled:opacity-50 inline-flex items-center justify-center gap-2">
                    <template x-if="isSaving">
                        <i data-lucide="loader-2" class="h-4 w-4 animate-spin"></i>
                    </template>
                    <span x-text="editingId ? 'Save Changes' : 'Add Category'"></span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function categoriesApp(token) {
    const el = document.getElementById('categories-json');
    const initialCategories = el ? JSON.parse(el.textContent) : [];
    return {
        token,
        categories: initialCategories,
        search: '',
        currentPage: 1,
        perPage: 20,
        showForm: false,
        editingId: null,
        isSaving: false,
        catImgUploading: false,
        formMessage: '',
        formTone: 'success',
        form: {
            name: '', slug: '', description: '', imageUrl: '',
            parentId: '', isActive: true, featuredOnHome: false, sortOrder: '0',
        },

        init() {
            this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
        },

        get filtered() {
            const q = this.search.toLowerCase().trim();
            if (!q) return this.categories;
            return this.categories.filter(c =>
                c.name.toLowerCase().includes(q) ||
                (c.slug || '').toLowerCase().includes(q) ||
                (c.parentName || '').toLowerCase().includes(q)
            );
        },

        get totalPages() { return Math.max(1, Math.ceil(this.filtered.length / this.perPage)); },

        get paginated() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filtered.slice(start, start + this.perPage);
        },

        slugify(text) {
            return text.toString().toLowerCase()
                .replace(/\s+/g, '-').replace(/[^\w\-]+/g, '')
                .replace(/\-\-+/g, '-').replace(/^-+/, '').replace(/-+$/, '');
        },

        autoSlug() {
            if (!this.form.slug) this.form.slug = this.slugify(this.form.name);
        },

        async uploadCategoryImage(file) {
            if (!file) return;
            this.catImgUploading = true;
            try {
                this.form.imageUrl = await uploadFile(file, this.token);
            } catch (err) {
                alert('Upload failed: ' + (err.message || 'Unknown error'));
            } finally {
                this.catImgUploading = false;
                this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
            }
        },

        openForm(cat) {
            this.formMessage = '';
            this.editingId = cat ? cat.id : null;
            if (cat) {
                this.form = {
                    name: cat.name, slug: cat.slug, description: cat.description || '',
                    imageUrl: cat.imageUrl || '', parentId: cat.parentId || '',
                    isActive: cat.isActive, featuredOnHome: cat.featuredOnHome,
                    sortOrder: String(cat.sortOrder),
                };
            } else {
                this.form = { name: '', slug: '', description: '', imageUrl: '', parentId: '', isActive: true, featuredOnHome: false, sortOrder: '0' };
            }
            this.showForm = true;
            this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
        },

        async saveCategory() {
            if (!this.form.name.trim()) { this.formMessage = 'Name is required.'; this.formTone = 'error'; return; }
            this.isSaving = true; this.formMessage = '';
            const payload = {
                name: this.form.name, slug: this.form.slug || this.slugify(this.form.name),
                description: this.form.description || null, imageUrl: this.form.imageUrl || null,
                parentId: this.form.parentId || null, isActive: this.form.isActive,
                featuredOnHome: this.form.featuredOnHome, sortOrder: parseInt(this.form.sortOrder) || 0,
            };
            try {
                const url = this.editingId
                    ? `${window.API_BASE}/api/admin/categories/${this.editingId}`
                    : `${window.API_BASE}/api/admin/categories`;
                const res = await fetch(url, {
                    method: this.editingId ? 'PATCH' : 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + this.token },
                    body: JSON.stringify(payload),
                });
                const text = await res.text();
                let data = {};
                try { data = JSON.parse(text); } catch { data = { error: text }; }
                if (!res.ok) throw new Error(data.error || data.message || 'Failed to save.');
                if (this.editingId) {
                    const parent = this.categories.find(c => c.id === payload.parentId);
                    this.categories = this.categories.map(c => c.id === this.editingId
                        ? { ...c, ...payload, imageUrl: payload.imageUrl || null, parentName: parent?.name || null } : c);
                } else {
                    const parent = this.categories.find(c => c.id === payload.parentId);
                    this.categories.unshift({ id: data.category?.id || crypto.randomUUID(), ...payload, parentName: parent?.name || null });
                }
                this.formTone = 'success';
                this.formMessage = this.editingId ? 'Category updated.' : 'Category added.';
                setTimeout(() => { this.showForm = false; }, 800);
            } catch (err) {
                this.formTone = 'error';
                this.formMessage = err.message || 'Failed to save.';
            } finally {
                this.isSaving = false;
            }
        },

        async deleteCategory(cat) {
            if (!confirm(`Delete "${cat.name}"? Sub-categories will be moved up.`)) return;
            try {
                const res = await fetch(`${window.API_BASE}/api/admin/categories/${cat.id}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': 'Bearer ' + this.token },
                });
                if (!res.ok) throw new Error('Delete failed.');
                this.categories = this.categories
                    .filter(c => c.id !== cat.id)
                    .map(c => c.parentId === cat.id ? { ...c, parentId: cat.parentId, parentName: null } : c);
            } catch (err) {
                alert(err.message || 'Delete failed.');
            }
        },
    };
}
</script>
@endpush
