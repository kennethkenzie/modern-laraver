@extends('admin.layout')

@section('title', 'Categories')

@section('content')
{{-- Safe JSON data --}}
<script id="categories-json" type="application/json">{!! json_encode($categories->values()) !!}</script>

<div
    class="-mx-4 -mt-4 sm:-mx-6 sm:-mt-6 min-h-screen bg-[#f7f7f8] px-4 pt-6 pb-16 sm:px-6 sm:pt-8 lg:px-8"
    x-data="categoriesApp('{{ session('admin_token') }}')"
    x-init="init()"
>
    <div class="mx-auto max-w-[1200px]">

        {{-- Page header --}}
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-3">
                <span class="mt-[10px] h-2.5 w-8 rounded-full bg-[#0b63ce] flex-shrink-0"></span>
                <div>
                    <h1 class="text-[28px] font-semibold tracking-tight text-gray-900">Categories</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage product categories and sub-categories.</p>
                </div>
            </div>
            <button type="button" @click="openForm(null)"
                class="inline-flex h-11 items-center gap-2 rounded-xl bg-[#1f2937] px-5 text-sm font-bold text-white transition hover:bg-black shadow-sm flex-shrink-0">
                <i data-lucide="plus" class="h-4 w-4"></i> Add Category
            </button>
        </div>

        {{-- Stat cards --}}
        <div class="mb-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Total</div>
                <div class="mt-2 text-3xl font-black text-gray-900" x-text="categories.length"></div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Active</div>
                <div class="mt-2 text-3xl font-black text-green-600" x-text="categories.filter(c => c.isActive).length"></div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Featured</div>
                <div class="mt-2 text-3xl font-black text-amber-500" x-text="categories.filter(c => c.featuredOnHome).length"></div>
            </div>
        </div>

        {{-- Search + list --}}
        <div class="rounded-3xl border border-gray-200 bg-white shadow-sm">

            {{-- Search bar --}}
            <div class="flex items-center gap-3 border-b border-gray-100 px-5 py-4">
                <i data-lucide="search" class="h-4 w-4 flex-shrink-0 text-gray-400"></i>
                <input
                    x-model="search"
                    type="text"
                    placeholder="Search categories..."
                    class="flex-1 bg-transparent text-sm text-gray-700 outline-none placeholder:text-gray-400"
                />
                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-500"
                    x-text="filtered.length + ' results'">
                </span>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50">
                            <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-wider text-gray-400">Category</th>
                            <th class="hidden px-5 py-3 text-left text-xs font-black uppercase tracking-wider text-gray-400 md:table-cell">Parent</th>
                            <th class="hidden px-5 py-3 text-left text-xs font-black uppercase tracking-wider text-gray-400 lg:table-cell">Slug</th>
                            <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-wider text-gray-400">Status</th>
                            <th class="px-5 py-3 text-right text-xs font-black uppercase tracking-wider text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="cat in paginated" :key="cat.id">
                            <tr class="group transition hover:bg-gray-50/50">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <template x-if="cat.imageUrl">
                                            <img :src="cat.imageUrl" :alt="cat.name"
                                                class="h-10 w-10 rounded-xl object-cover border border-gray-200 bg-white" />
                                        </template>
                                        <template x-if="!cat.imageUrl">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-gray-100 text-gray-400">
                                                <i data-lucide="layers" class="h-4 w-4"></i>
                                            </div>
                                        </template>
                                        <div>
                                            <div class="font-semibold text-gray-900" x-text="cat.name"></div>
                                            <div class="text-xs text-gray-400" x-text="cat.description || '—'" style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden px-5 py-4 md:table-cell">
                                    <span x-text="cat.parentName || '—'" class="text-gray-500"></span>
                                </td>
                                <td class="hidden px-5 py-4 lg:table-cell">
                                    <span x-text="cat.slug" class="font-mono text-xs text-gray-400"></span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-col gap-1">
                                        <span :class="cat.isActive ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                                            class="inline-flex w-fit items-center rounded-full px-2.5 py-0.5 text-xs font-bold"
                                            x-text="cat.isActive ? 'Active' : 'Inactive'">
                                        </span>
                                        <span x-show="cat.featuredOnHome"
                                            class="inline-flex w-fit items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-700">
                                            Featured
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <button type="button" @click="openForm(cat)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-[#0b63ce] hover:text-[#0b63ce]">
                                            <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                        </button>
                                        <button type="button" @click="deleteCategory(cat)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-red-500 hover:text-red-500">
                                            <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filtered.length === 0">
                            <td colspan="5" class="px-5 py-12 text-center text-sm text-gray-400">No categories found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div x-show="totalPages > 1" class="flex items-center justify-between border-t border-gray-100 px-5 py-4">
                <span class="text-sm text-gray-500">
                    Page <span x-text="currentPage"></span> of <span x-text="totalPages"></span>
                </span>
                <div class="flex gap-2">
                    <button @click="currentPage = Math.max(1, currentPage - 1)" :disabled="currentPage === 1"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:bg-gray-50 disabled:opacity-40">
                        <i data-lucide="chevron-left" class="h-4 w-4"></i>
                    </button>
                    <button @click="currentPage = Math.min(totalPages, currentPage + 1)" :disabled="currentPage === totalPages"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:bg-gray-50 disabled:opacity-40">
                        <i data-lucide="chevron-right" class="h-4 w-4"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Slide-over form panel --}}
    <div x-show="showForm" x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 bg-black/50"
        @click.self="showForm = false">
    </div>

    <div x-show="showForm" x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 z-50 flex w-full max-w-md flex-col border-l border-gray-200 bg-white shadow-2xl">

        {{-- Form header --}}
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
            <h2 class="text-lg font-bold text-gray-900" x-text="editingId ? 'Edit Category' : 'Add Category'"></h2>
            <button @click="showForm = false" class="text-gray-400 transition hover:text-gray-600">
                <i data-lucide="x" class="h-5 w-5"></i>
            </button>
        </div>

        {{-- Form body --}}
        <div class="flex-1 overflow-y-auto px-6 py-6 space-y-5">

            <div>
                <label class="mb-2 block text-sm font-bold text-gray-700">Name <span class="text-red-500">*</span></label>
                <input x-model="form.name" @input="autoSlug()" type="text" placeholder="e.g. Televisions"
                    class="h-11 w-full rounded-xl border border-gray-300 px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-gray-700">Slug</label>
                <input x-model="form.slug" type="text" placeholder="televisions"
                    class="h-11 w-full rounded-xl border border-gray-300 px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-gray-700">Parent Category</label>
                <div class="relative">
                    <select x-model="form.parentId"
                        class="h-11 w-full appearance-none rounded-xl border border-gray-300 px-4 pr-10 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]">
                        <option value="">None (top-level)</option>
                        <template x-for="cat in categories.filter(c => c.id !== editingId)" :key="cat.id">
                            <option :value="cat.id" x-text="cat.name"></option>
                        </template>
                    </select>
                    <i data-lucide="chevron-down" class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-gray-700">Description</label>
                <textarea x-model="form.description" rows="3" placeholder="Short description of this category..."
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]"></textarea>
            </div>

            {{-- Category image --}}
            <div>
                <label class="mb-2 block text-sm font-bold text-gray-700">Category Image</label>

                {{-- Preview --}}
                <div x-show="form.imageUrl" class="mb-3 flex items-center gap-3">
                    <img :src="form.imageUrl"
                        class="h-16 w-16 rounded-xl border border-gray-200 bg-white object-contain p-1 shadow-sm" />
                    <button type="button" @click="form.imageUrl = ''"
                        class="inline-flex h-7 items-center gap-1 rounded-lg bg-red-50 px-2.5 text-xs font-bold text-red-500 hover:bg-red-100">
                        <i data-lucide="x" class="h-3 w-3"></i> Remove
                    </button>
                </div>

                {{-- Upload button --}}
                <input id="cat-img-input" type="file" accept="image/*" class="hidden"
                    @change="uploadCategoryImage($event.target.files[0]); $event.target.value = '';" />

                <div class="flex gap-2">
                    <button type="button" @click="document.getElementById('cat-img-input').click()"
                        :disabled="catImgUploading"
                        class="inline-flex h-11 items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 text-sm font-bold text-gray-700 transition hover:bg-gray-50 disabled:opacity-50">
                        <template x-if="catImgUploading">
                            <i data-lucide="loader-2" class="h-4 w-4 animate-spin"></i>
                        </template>
                        <template x-if="!catImgUploading">
                            <i data-lucide="upload" class="h-4 w-4"></i>
                        </template>
                        <span x-text="catImgUploading ? 'Uploading…' : 'Upload image'"></span>
                    </button>
                    <input x-model="form.imageUrl" type="url" placeholder="or paste URL"
                        class="h-11 flex-1 rounded-xl border border-gray-300 px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
                </div>
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-gray-700">Sort Order</label>
                <input x-model="form.sortOrder" type="number" min="0"
                    class="h-11 w-full rounded-xl border border-gray-300 px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
            </div>

            <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-5 py-4">
                <div>
                    <div class="text-sm font-bold text-gray-800">Active</div>
                    <div class="text-xs text-gray-500">Visible to shoppers</div>
                </div>
                <button type="button" @click="form.isActive = !form.isActive"
                    :class="form.isActive ? 'bg-[#0b63ce]' : 'bg-gray-300'"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200">
                    <span :class="form.isActive ? 'translate-x-5' : 'translate-x-0'"
                        class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200"></span>
                </button>
            </div>

            <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-5 py-4">
                <div>
                    <div class="text-sm font-bold text-gray-800">Featured on Home</div>
                    <div class="text-xs text-gray-500">Show in homepage category grid</div>
                </div>
                <button type="button" @click="form.featuredOnHome = !form.featuredOnHome"
                    :class="form.featuredOnHome ? 'bg-[#0b63ce]' : 'bg-gray-300'"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200">
                    <span :class="form.featuredOnHome ? 'translate-x-5' : 'translate-x-0'"
                        class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200"></span>
                </button>
            </div>

            {{-- Message --}}
            <div x-show="formMessage"
                :class="formTone === 'success' ? 'border-[#bbf7d0] bg-[#f0fdf4] text-[#166534]' : 'border-[#fecaca] bg-[#fef2f2] text-[#b91c1c]'"
                class="rounded-xl border px-4 py-3 text-sm font-medium"
                x-text="formMessage">
            </div>
        </div>

        {{-- Form footer --}}
        <div class="border-t border-gray-100 px-6 py-5">
            <div class="flex gap-3">
                <button type="button" @click="showForm = false"
                    class="flex-1 h-11 rounded-xl border border-gray-300 text-sm font-bold text-gray-700 transition hover:bg-gray-50">
                    Cancel
                </button>
                <button type="button" @click="saveCategory()" :disabled="isSaving"
                    class="flex-1 h-11 rounded-xl bg-[#1f2937] text-sm font-bold text-white transition hover:bg-black disabled:opacity-50 inline-flex items-center justify-center gap-2">
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
            name: '',
            slug: '',
            description: '',
            imageUrl: '',
            parentId: '',
            isActive: true,
            featuredOnHome: false,
            sortOrder: '0',
        },

        init() {
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
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

        get totalPages() {
            return Math.max(1, Math.ceil(this.filtered.length / this.perPage));
        },

        get paginated() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filtered.slice(start, start + this.perPage);
        },

        slugify(text) {
            return text.toString().toLowerCase()
                .replace(/\s+/g, '-')
                .replace(/[^\w\-]+/g, '')
                .replace(/\-\-+/g, '-')
                .replace(/^-+/, '').replace(/-+$/, '');
        },

        autoSlug() {
            if (!this.form.slug) {
                this.form.slug = this.slugify(this.form.name);
            }
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
                    name: cat.name,
                    slug: cat.slug,
                    description: cat.description || '',
                    imageUrl: cat.imageUrl || '',
                    parentId: cat.parentId || '',
                    isActive: cat.isActive,
                    featuredOnHome: cat.featuredOnHome,
                    sortOrder: String(cat.sortOrder),
                };
            } else {
                this.form = { name: '', slug: '', description: '', imageUrl: '', parentId: '', isActive: true, featuredOnHome: false, sortOrder: '0' };
            }
            this.showForm = true;
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        },

        async saveCategory() {
            if (!this.form.name.trim()) {
                this.formMessage = 'Name is required.';
                this.formTone = 'error';
                return;
            }

            this.isSaving = true;
            this.formMessage = '';

            const payload = {
                name:           this.form.name,
                slug:           this.form.slug || this.slugify(this.form.name),
                description:    this.form.description || null,
                imageUrl:       this.form.imageUrl || null,
                parentId:       this.form.parentId || null,
                isActive:       this.form.isActive,
                featuredOnHome: this.form.featuredOnHome,
                sortOrder:      parseInt(this.form.sortOrder) || 0,
            };

            try {
                const url    = this.editingId
                    ? `${window.API_BASE}/api/admin/categories/${this.editingId}`
                    : `${window.API_BASE}/api/admin/categories`;
                const method = this.editingId ? 'PATCH' : 'POST';

                const res = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + this.token,
                    },
                    body: JSON.stringify(payload),
                });

                const text = await res.text();
                let data = {};
                try { data = JSON.parse(text); } catch { data = { error: text }; }

                if (!res.ok) throw new Error(data.error || data.message || 'Failed to save.');

                if (this.editingId) {
                    // Update in local list
                    const parent = this.categories.find(c => c.id === payload.parentId);
                    this.categories = this.categories.map(c => c.id === this.editingId
                        ? { ...c, ...payload, imageUrl: payload.imageUrl || null, parentName: parent?.name || null }
                        : c
                    );
                } else {
                    // Add to local list
                    const parent = this.categories.find(c => c.id === payload.parentId);
                    this.categories.unshift({
                        id: data.category?.id || crypto.randomUUID(),
                        ...payload,
                        parentName: parent?.name || null,
                    });
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
