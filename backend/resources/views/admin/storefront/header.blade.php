@extends('admin.layout')

@section('title', 'StoreFront Header')

@section('content')
    <div class="space-y-8" x-data="storefrontHeaderApp()" x-init="init()">
        <script id="navbar-json" type="application/json">@json($navbar)</script>

        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.25em] text-[#114f8f]">StoreFront</p>
                <h1 class="mt-2 text-[34px] font-black uppercase tracking-tight text-[#111827]">Header</h1>
                <p class="mt-3 max-w-2xl text-sm font-medium leading-6 text-gray-500">
                    Manage the live website header, utility links, and branding assets from the Laravel dashboard.
                </p>
            </div>

            <div x-show="notice" x-cloak class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-[12px] font-black uppercase tracking-[0.15em] text-emerald-700">
                Header saved
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.4fr_0.9fr]">
            <div class="space-y-6">
                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-black uppercase tracking-tight text-[#111827]">Branding</h2>
                            <p class="mt-1 text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Logo, title and tab icon</p>
                        </div>
                        <button type="button" @click="save()" :disabled="saving || uploading" class="inline-flex h-12 items-center justify-center rounded-xl bg-[#114f8f] px-5 text-[13px] font-black uppercase tracking-[0.14em] text-white transition hover:bg-[#0d3f74] disabled:opacity-50">
                            <span x-text="saving ? 'Saving...' : 'Save Header'"></span>
                        </button>
                    </div>

                    <div class="mt-6 grid gap-5 md:grid-cols-2">
                        <label class="block">
                            <span class="mb-2 block text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Site Title</span>
                            <input x-model="form.siteTitle" type="text" class="h-12 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 text-sm font-semibold text-[#111827] outline-none transition focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50">
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Search Placeholder</span>
                            <input x-model="form.searchPlaceholder" type="text" class="h-12 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 text-sm font-semibold text-[#111827] outline-none transition focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50">
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Logo URL</span>
                            <div class="flex gap-2">
                                <input x-model="form.logoUrl" type="text" class="h-12 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 text-sm font-semibold text-[#111827] outline-none transition focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50">
                                <button type="button" @click="$refs.logoInput.click()" :disabled="uploading" class="inline-flex h-12 w-12 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 transition hover:border-[#114f8f] hover:text-[#114f8f] disabled:opacity-50">
                                    <i data-lucide="upload" class="h-4 w-4"></i>
                                </button>
                            </div>
                            <input x-ref="logoInput" type="file" class="hidden" accept="image/*,.svg" @change="uploadAsset($event, 'logoUrl')">
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Logo Alt Text</span>
                            <input x-model="form.logoAlt" type="text" class="h-12 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 text-sm font-semibold text-[#111827] outline-none transition focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50">
                        </label>

                        <label class="block md:col-span-2">
                            <span class="mb-2 block text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Favicon URL</span>
                            <div class="flex gap-2">
                                <input x-model="form.faviconUrl" type="text" class="h-12 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 text-sm font-semibold text-[#111827] outline-none transition focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50">
                                <button type="button" @click="$refs.faviconInput.click()" :disabled="uploading" class="inline-flex h-12 w-12 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 transition hover:border-[#114f8f] hover:text-[#114f8f] disabled:opacity-50">
                                    <i data-lucide="upload" class="h-4 w-4"></i>
                                </button>
                            </div>
                            <input x-ref="faviconInput" type="file" class="hidden" accept="image/*,.ico,.svg" @change="uploadAsset($event, 'faviconUrl')">
                        </label>
                    </div>
                </section>

                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-black uppercase tracking-tight text-[#111827]">Top Links</h2>
                            <p class="mt-1 text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Utility bar links</p>
                        </div>
                        <button type="button" @click="addTopLink()" class="inline-flex h-11 items-center justify-center rounded-xl border border-gray-200 bg-gray-50 px-4 text-[12px] font-black uppercase tracking-[0.14em] text-[#111827] transition hover:bg-gray-100">
                            Add Link
                        </button>
                    </div>

                    <div class="mt-6 space-y-4">
                        <template x-for="(link, index) in form.topLinks" :key="`top-${index}`">
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                                <div class="grid gap-4 md:grid-cols-[1fr_1fr_180px_60px]">
                                    <input x-model="link.label" type="text" placeholder="Label" class="h-11 rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-[#111827] outline-none transition focus:border-[#114f8f]">
                                    <input x-model="link.href" type="text" placeholder="/path" class="h-11 rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-[#111827] outline-none transition focus:border-[#114f8f]">
                                    <select x-model="link.icon" class="h-11 rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-[#111827] outline-none transition focus:border-[#114f8f]">
                                        <option value="home">HOME</option>
                                        <option value="info">INFO</option>
                                        <option value="mail">MAIL</option>
                                    </select>
                                    <button type="button" @click="removeTopLink(index)" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-red-100 bg-red-50 text-red-500 transition hover:bg-red-100">
                                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </section>

                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-black uppercase tracking-tight text-[#111827]">Main Navigation</h2>
                            <p class="mt-1 text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Primary storefront links</p>
                        </div>
                        <button type="button" @click="addQuickLink()" class="inline-flex h-11 items-center justify-center rounded-xl border border-gray-200 bg-gray-50 px-4 text-[12px] font-black uppercase tracking-[0.14em] text-[#111827] transition hover:bg-gray-100">
                            Add Item
                        </button>
                    </div>

                    <div class="mt-6 space-y-4">
                        <template x-for="(link, index) in form.quickLinks" :key="`quick-${index}`">
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                                <div class="grid gap-4 md:grid-cols-[1fr_1fr_60px]">
                                    <input x-model="link.label" type="text" placeholder="Label" class="h-11 rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-[#111827] outline-none transition focus:border-[#114f8f]">
                                    <input x-model="link.href" type="text" placeholder="/path" class="h-11 rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-[#111827] outline-none transition focus:border-[#114f8f]">
                                    <button type="button" @click="removeQuickLink(index)" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-red-100 bg-red-50 text-red-500 transition hover:bg-red-100">
                                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </section>
            </div>

            <aside class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm xl:sticky xl:top-8 xl:self-start">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-black uppercase tracking-tight text-[#111827]">Preview</h2>
                        <p class="mt-1 text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Website header snapshot</p>
                    </div>
                    <span x-show="uploading" x-cloak class="text-[11px] font-black uppercase tracking-[0.18em] text-[#114f8f]">Uploading...</span>
                </div>

                <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-[#f8fbff]">
                    <div class="border-b border-gray-200 bg-[#111827] px-5 py-3 text-white">
                        <div class="flex flex-wrap gap-3 text-[11px] font-black uppercase tracking-[0.12em]">
                            <template x-for="(link, index) in form.topLinks" :key="`preview-top-${index}`">
                                <span x-text="link.label || 'Untitled'"></span>
                            </template>
                        </div>
                    </div>

                    <div class="space-y-5 px-5 py-5">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-xl border border-gray-200 bg-white">
                                <template x-if="form.logoUrl">
                                    <img :src="form.logoUrl" :alt="form.logoAlt || 'Logo preview'" class="h-full w-full object-contain">
                                </template>
                                <template x-if="!form.logoUrl">
                                    <span class="text-[10px] font-black uppercase tracking-[0.18em] text-gray-400">Logo</span>
                                </template>
                            </div>
                            <div>
                                <div class="text-base font-black uppercase tracking-tight text-[#111827]" x-text="form.siteTitle || 'Modern Electronics'"></div>
                                <div class="mt-1 text-sm font-medium text-gray-500" x-text="form.searchPlaceholder || 'Search here...'"></div>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <template x-for="(link, index) in form.quickLinks" :key="`preview-quick-${index}`">
                                <span class="rounded-full bg-white px-4 py-2 text-[11px] font-black uppercase tracking-[0.12em] text-[#111827] shadow-sm" x-text="link.label || 'Menu item'"></span>
                            </template>
                        </div>

                        <div class="rounded-xl border border-dashed border-gray-200 bg-white px-4 py-3">
                            <div class="text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Favicon</div>
                            <div class="mt-2 truncate text-sm font-semibold text-[#111827]" x-text="form.faviconUrl || '/favicon.ico'"></div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function storefrontHeaderApp() {
            return {
                form: {
                    siteTitle: '',
                    logoUrl: '',
                    logoAlt: '',
                    faviconUrl: '',
                    searchPlaceholder: '',
                    topLinks: [],
                    quickLinks: [],
                },
                saving: false,
                uploading: false,
                notice: false,
                token: @json(session('admin_token')),
                init() {
                    const raw = document.getElementById('navbar-json')?.textContent || '{}';
                    this.form = Object.assign(this.form, JSON.parse(raw));
                    this.form.topLinks = Array.isArray(this.form.topLinks) ? this.form.topLinks : [];
                    this.form.quickLinks = Array.isArray(this.form.quickLinks) ? this.form.quickLinks : [];
                    this.refreshIcons();
                },
                refreshIcons() {
                    this.$nextTick(() => lucide.createIcons());
                },
                addTopLink() {
                    this.form.topLinks.push({ label: '', href: '', icon: 'home' });
                    this.refreshIcons();
                },
                removeTopLink(index) {
                    this.form.topLinks.splice(index, 1);
                    this.refreshIcons();
                },
                addQuickLink() {
                    this.form.quickLinks.push({ label: '', href: '' });
                    this.refreshIcons();
                },
                removeQuickLink(index) {
                    this.form.quickLinks.splice(index, 1);
                    this.refreshIcons();
                },
                async uploadAsset(event, key) {
                    const file = event.target.files?.[0];
                    if (!file || !this.token) return;

                    this.uploading = true;

                    try {
                        this.form[key] = await uploadFile(file, this.token);
                    } catch (error) {
                        alert(error instanceof Error ? error.message : 'Upload failed.');
                    } finally {
                        this.uploading = false;
                        event.target.value = '';
                    }
                },
                async save() {
                    this.saving = true;

                    try {
                        const response = await fetch(@json(route('dashboard.storefront.header.update')), {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': @json(csrf_token()),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(this.form),
                        });

                        const payload = await response.json();
                        if (!response.ok) {
                            throw new Error(payload.message || payload.error || 'Failed to save header.');
                        }

                        this.notice = true;
                        setTimeout(() => this.notice = false, 3000);
                    } catch (error) {
                        alert(error instanceof Error ? error.message : 'Failed to save header.');
                    } finally {
                        this.saving = false;
                    }
                },
            };
        }
    </script>
@endpush
