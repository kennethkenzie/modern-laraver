@extends('admin.layout')

@section('title', 'Brands')

@section('content')
<script id="brands-json" type="application/json">{!! json_encode($brands->values()) !!}</script>

<div class="-mx-4 -mt-4 sm:-mx-6 sm:-mt-6 min-h-screen bg-[#f7f7f8] px-4 pt-6 pb-16 sm:px-6 sm:pt-8 lg:px-8"
     x-data="brandsApp('{{ session('admin_token') }}')"
     x-init="init()">

    <div class="mx-auto max-w-[1200px]">

        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-3">
                <span class="mt-[10px] h-2.5 w-8 rounded-full bg-[#f6c400] flex-shrink-0"></span>
                <div>
                    <h1 class="text-[28px] font-black uppercase tracking-tight text-gray-900">Brands</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage product brands and manufacturer details.</p>
                </div>
            </div>
            <button type="button" @click="openForm(null)"
                class="inline-flex h-11 items-center gap-2 rounded-xl bg-[#1f2937] px-5 text-sm font-bold text-white transition hover:bg-black shadow-sm flex-shrink-0">
                <i data-lucide="plus" class="h-4 w-4"></i> Add Brand
            </button>
        </div>

        {{-- Stats --}}
        <div class="mb-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Total</div>
                <div class="mt-2 text-3xl font-black text-gray-900" x-text="brands.length"></div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Active</div>
                <div class="mt-2 text-3xl font-black text-green-600" x-text="brands.filter(b => b.isActive).length"></div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Featured</div>
                <div class="mt-2 text-3xl font-black text-amber-500" x-text="brands.filter(b => b.isFeatured).length"></div>
            </div>
        </div>

        {{-- Table card --}}
        <div class="rounded-3xl border border-gray-200 bg-white shadow-sm">

            {{-- Search --}}
            <div class="flex items-center gap-3 border-b border-gray-100 px-5 py-4">
                <i data-lucide="search" class="h-4 w-4 flex-shrink-0 text-gray-400"></i>
                <input x-model="search" type="text" placeholder="Search brands..."
                    class="flex-1 bg-transparent text-sm text-gray-700 outline-none placeholder:text-gray-400" />
                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-500"
                    x-text="filtered.length + ' results'"></span>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50 text-left text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">
                            <th class="px-5 py-4">#</th>
                            <th class="px-5 py-4">Logo</th>
                            <th class="px-5 py-4">Name</th>
                            <th class="px-5 py-4">Slug</th>
                            <th class="px-5 py-4 text-center">Featured</th>
                            <th class="px-5 py-4 text-center">Status</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <template x-if="filtered.length === 0">
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center">
                                    <div class="flex flex-col items-center gap-3 text-gray-400">
                                        <i data-lucide="tag" class="h-9 w-9 opacity-40"></i>
                                        <span class="text-sm font-bold" x-text="search ? 'No brands matching &quot;' + search + '&quot;' : 'No brands yet. Add your first brand.'"></span>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <template x-for="(brand, idx) in filtered" :key="brand.id">
                            <tr class="transition-colors hover:bg-gray-50/40">
                                <td class="px-5 py-4 font-bold text-gray-400" x-text="idx + 1"></td>
                                <td class="px-5 py-4">
                                    <div class="h-10 w-10 overflow-hidden rounded-xl border border-gray-100 bg-white p-1 shadow-sm">
                                        <template x-if="brand.logoUrl">
                                            <img :src="brand.logoUrl" :alt="brand.name" class="h-full w-full object-contain" />
                                        </template>
                                        <template x-if="!brand.logoUrl">
                                            <div class="flex h-full w-full items-center justify-center text-gray-300">
                                                <i data-lucide="image" class="h-5 w-5"></i>
                                            </div>
                                        </template>
                                    </div>
                                </td>
                                <td class="px-5 py-4 font-bold text-gray-900" x-text="brand.name"></td>
                                <td class="px-5 py-4 font-medium text-gray-500" x-text="brand.slug"></td>
                                <td class="px-5 py-4 text-center">
                                    <button @click="toggleFeatured(brand)"
                                        :class="brand.isFeatured ? 'bg-[#114f8f]' : 'bg-gray-200'"
                                        class="relative inline-flex h-6 w-12 items-center rounded-full transition">
                                        <span :class="brand.isFeatured ? 'translate-x-6' : 'translate-x-1'"
                                            class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition"></span>
                                    </button>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <button @click="toggleActive(brand)"
                                        :class="brand.isActive ? 'bg-[#114f8f]' : 'bg-gray-200'"
                                        class="relative inline-flex h-6 w-12 items-center rounded-full transition">
                                        <span :class="brand.isActive ? 'translate-x-6' : 'translate-x-1'"
                                            class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition"></span>
                                    </button>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button @click="openForm(brand)"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 text-gray-600 transition hover:bg-gray-200">
                                            <i data-lucide="pencil" class="h-4 w-4"></i>
                                        </button>
                                        <button @click="deleteBrand(brand.id)"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-500 transition hover:bg-red-100">
                                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Slide-over form ── --}}
    <div x-show="showForm" x-cloak
         class="fixed inset-0 z-50 flex items-start justify-end bg-black/40 backdrop-blur-sm"
         @click.self="showForm = false">

        <div class="relative h-full w-full max-w-[480px] overflow-y-auto bg-white shadow-2xl">

            {{-- Form header --}}
            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-200 bg-white px-6 py-5">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Brand</p>
                    <h2 class="text-xl font-black text-gray-900" x-text="editingId ? 'Edit Brand' : 'Add Brand'"></h2>
                </div>
                <button @click="showForm = false"
                    class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 text-gray-500 hover:text-gray-900 transition">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>

            <form @submit.prevent="saveBrand" class="space-y-5 p-6">

                {{-- Name --}}
                <div>
                    <label class="mb-1.5 block text-[13px] font-black uppercase tracking-widest text-gray-500">Name <span class="text-red-500">*</span></label>
                    <input x-model="form.name" @input="autoSlug()" type="text" placeholder="e.g. Samsung"
                        class="h-11 w-full rounded-xl border border-gray-300 px-4 text-sm font-bold text-gray-900 outline-none transition focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50" />
                </div>

                {{-- Slug --}}
                <div>
                    <label class="mb-1.5 block text-[13px] font-black uppercase tracking-widest text-gray-500">Slug</label>
                    <input x-model="form.slug" type="text" placeholder="samsung"
                        class="h-11 w-full rounded-xl border border-gray-300 px-4 text-sm font-bold text-gray-900 outline-none transition focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50" />
                </div>

                {{-- Logo URL --}}
                <div>
                    <label class="mb-1.5 block text-[13px] font-black uppercase tracking-widest text-gray-500">Logo</label>
                    <div class="flex gap-3">
                        <input x-model="form.logoUrl" type="text" placeholder="Upload or paste URL"
                            class="h-11 flex-1 rounded-xl border border-gray-300 px-4 text-sm font-bold text-gray-900 outline-none transition focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50" />
                        <label class="flex h-11 cursor-pointer items-center gap-2 rounded-xl border border-gray-300 bg-gray-50 px-4 text-sm font-bold text-gray-600 hover:bg-gray-100 transition">
                            <i data-lucide="upload" class="h-4 w-4"></i>
                            <span x-text="uploadingLogo ? 'Uploading…' : 'Upload'"></span>
                            <input type="file" accept="image/*" class="hidden"
                                @change="uploadImage($event, 'logoUrl')" :disabled="uploadingLogo" />
                        </label>
                    </div>
                    <template x-if="form.logoUrl">
                        <img :src="form.logoUrl" alt="logo preview" class="mt-2 h-14 rounded-lg border border-gray-200 object-contain p-1" />
                    </template>
                </div>

                {{-- Banner URL --}}
                <div>
                    <label class="mb-1.5 block text-[13px] font-black uppercase tracking-widest text-gray-500">Banner</label>
                    <div class="flex gap-3">
                        <input x-model="form.bannerUrl" type="text" placeholder="Upload or paste URL"
                            class="h-11 flex-1 rounded-xl border border-gray-300 px-4 text-sm font-bold text-gray-900 outline-none transition focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50" />
                        <label class="flex h-11 cursor-pointer items-center gap-2 rounded-xl border border-gray-300 bg-gray-50 px-4 text-sm font-bold text-gray-600 hover:bg-gray-100 transition">
                            <i data-lucide="upload" class="h-4 w-4"></i>
                            <span x-text="uploadingBanner ? 'Uploading…' : 'Upload'"></span>
                            <input type="file" accept="image/*" class="hidden"
                                @change="uploadImage($event, 'bannerUrl')" :disabled="uploadingBanner" />
                        </label>
                    </div>
                    <template x-if="form.bannerUrl">
                        <img :src="form.bannerUrl" alt="banner preview" class="mt-2 h-16 w-full rounded-lg border border-gray-200 object-cover" />
                    </template>
                </div>

                {{-- Meta Title --}}
                <div>
                    <label class="mb-1.5 block text-[13px] font-black uppercase tracking-widest text-gray-500">Meta Title</label>
                    <input x-model="form.metaTitle" type="text" placeholder="SEO title"
                        class="h-11 w-full rounded-xl border border-gray-300 px-4 text-sm font-bold text-gray-900 outline-none transition focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50" />
                </div>

                {{-- Meta Description --}}
                <div>
                    <label class="mb-1.5 block text-[13px] font-black uppercase tracking-widest text-gray-500">Meta Description</label>
                    <textarea x-model="form.metaDescription" rows="3" placeholder="Brief summary for search results…"
                        class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm font-medium text-gray-900 outline-none transition focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50"></textarea>
                </div>

                {{-- Toggles --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                        <span class="text-[13px] font-black uppercase tracking-widest text-gray-600">Active</span>
                        <button type="button" @click="form.isActive = !form.isActive"
                            :class="form.isActive ? 'bg-[#114f8f]' : 'bg-gray-200'"
                            class="relative inline-flex h-6 w-12 items-center rounded-full transition">
                            <span :class="form.isActive ? 'translate-x-6' : 'translate-x-1'"
                                class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition"></span>
                        </button>
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                        <span class="text-[13px] font-black uppercase tracking-widest text-gray-600">Featured</span>
                        <button type="button" @click="form.isFeatured = !form.isFeatured"
                            :class="form.isFeatured ? 'bg-[#114f8f]' : 'bg-gray-200'"
                            class="relative inline-flex h-6 w-12 items-center rounded-full transition">
                            <span :class="form.isFeatured ? 'translate-x-6' : 'translate-x-1'"
                                class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition"></span>
                        </button>
                    </div>
                </div>

                {{-- Error --}}
                <template x-if="error">
                    <p class="rounded-xl bg-red-50 px-4 py-3 text-sm font-bold text-red-600" x-text="error"></p>
                </template>

                {{-- Submit --}}
                <div class="flex gap-3 border-t border-gray-100 pt-4">
                    <button type="button" @click="showForm = false"
                        class="flex h-11 flex-1 items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white text-[13px] font-black uppercase text-gray-600 transition hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" :disabled="saving"
                        class="flex h-11 flex-1 items-center justify-center gap-2 rounded-xl bg-[#114f8f] text-[13px] font-black uppercase text-white shadow-lg shadow-blue-900/10 transition hover:bg-[#0d3f74] disabled:opacity-50">
                        <i data-lucide="save" class="h-4 w-4"></i>
                        <span x-text="saving ? 'Saving…' : (editingId ? 'Update Brand' : 'Save Brand')"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Toast --}}
    <div x-show="toast" x-cloak x-transition
         class="fixed bottom-6 right-6 z-[60] flex items-center gap-3 rounded-2xl border border-green-200 bg-white px-5 py-3.5 shadow-xl">
        <i data-lucide="check-circle-2" class="h-5 w-5 text-green-600"></i>
        <span class="text-sm font-bold text-gray-900" x-text="toast"></span>
    </div>
</div>
@endsection

@push('scripts')
<script>
function brandsApp(token) {
    return {
        brands: [],
        search: '',
        showForm: false,
        editingId: null,
        saving: false,
        uploadingLogo: false,
        uploadingBanner: false,
        error: null,
        toast: null,
        form: { name:'', slug:'', logoUrl:'', bannerUrl:'', metaTitle:'', metaDescription:'', isActive:true, isFeatured:false },

        get filtered() {
            const q = this.search.toLowerCase();
            return this.brands.filter(b => !q || b.name.toLowerCase().includes(q) || b.slug.toLowerCase().includes(q));
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
                this.form = { name: brand.name, slug: brand.slug, logoUrl: brand.logoUrl || '', bannerUrl: brand.bannerUrl || '', metaTitle: brand.metaTitle || '', metaDescription: brand.metaDescription || '', isActive: brand.isActive, isFeatured: brand.isFeatured };
            } else {
                this.editingId = null;
                this.form = { name:'', slug:'', logoUrl:'', bannerUrl:'', metaTitle:'', metaDescription:'', isActive:true, isFeatured:false };
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
            this.saving = true; this.error = null;
            try {
                const url = this.editingId ? `/api/admin/brands/${this.editingId}` : '/api/admin/brands';
                const method = this.editingId ? 'PATCH' : 'POST';
                const res = await fetch(url, {
                    method,
                    headers: { 'Content-Type':'application/json', 'Authorization':'Bearer ' + token },
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
                this.showToast(this.editingId ? 'Brand updated.' : 'Brand added.');
            } catch (e) {
                this.error = e.message;
            } finally {
                this.saving = false;
            }
        },

        async toggleActive(brand) {
            const res = await fetch(`/api/admin/brands/${brand.id}`, {
                method: 'PATCH',
                headers: { 'Content-Type':'application/json', 'Authorization':'Bearer ' + token },
                body: JSON.stringify({ isActive: !brand.isActive }),
            });
            if (res.ok) brand.isActive = !brand.isActive;
        },

        async toggleFeatured(brand) {
            const res = await fetch(`/api/admin/brands/${brand.id}`, {
                method: 'PATCH',
                headers: { 'Content-Type':'application/json', 'Authorization':'Bearer ' + token },
                body: JSON.stringify({ isFeatured: !brand.isFeatured }),
            });
            if (res.ok) brand.isFeatured = !brand.isFeatured;
        },

        async deleteBrand(id) {
            if (!confirm('Delete this brand?')) return;
            const res = await fetch(`/api/admin/brands/${id}`, {
                method: 'DELETE',
                headers: { 'Authorization':'Bearer ' + token },
            });
            if (res.ok) {
                this.brands = this.brands.filter(b => b.id !== id);
                this.showToast('Brand deleted.');
            }
        },

        showToast(msg) {
            this.toast = msg;
            this.$nextTick(() => lucide.createIcons());
            setTimeout(() => this.toast = null, 3000);
        },
    };
}
</script>
@endpush
