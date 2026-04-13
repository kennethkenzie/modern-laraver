@extends('admin.layout')

@section('title', 'StoreFront Slider')

@section('content')
    <div class="space-y-8" x-data="storefrontSliderApp()" x-init="init()">
        <script id="slides-json" type="application/json">@json($slides)</script>

        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.25em] text-[#114f8f]">StoreFront</p>
                <h1 class="mt-2 text-[34px] font-black uppercase tracking-tight text-[#111827]">Slider</h1>
                <p class="mt-3 max-w-2xl text-sm font-medium leading-6 text-gray-500">
                    Manage the live homepage hero slides shown on the frontend website.
                </p>
            </div>

            <div x-show="notice" x-cloak class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-[12px] font-black uppercase tracking-[0.15em] text-emerald-700">
                Slider saved
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.2fr_0.95fr]">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-black uppercase tracking-tight text-[#111827]">Slides</h2>
                        <p class="mt-1 text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Homepage hero content</p>
                    </div>
                    <button type="button" @click="addSlide()" class="inline-flex h-11 items-center justify-center rounded-xl border border-gray-200 bg-gray-50 px-4 text-[12px] font-black uppercase tracking-[0.14em] text-[#111827] transition hover:bg-gray-100">
                        Add Slide
                    </button>
                </div>

                <div class="mt-6 space-y-5">
                    <template x-if="slides.length === 0">
                        <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-6 py-12 text-center">
                            <div class="text-[12px] font-black uppercase tracking-[0.16em] text-gray-400">No slides yet</div>
                        </div>
                    </template>

                    <template x-for="(slide, index) in slides" :key="slide.id">
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <div class="text-[11px] font-black uppercase tracking-[0.18em] text-[#114f8f]" x-text="`Slide ${index + 1}`"></div>
                                    <div class="mt-2 text-lg font-black uppercase tracking-tight text-[#111827]" x-text="slide.title || 'Untitled slide'"></div>
                                </div>
                                <button type="button" @click="removeSlide(index)" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-red-100 bg-red-50 text-red-500 transition hover:bg-red-100">
                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                </button>
                            </div>

                            <div class="mt-5 grid gap-4">
                                <label class="block">
                                    <span class="mb-2 block text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Image URL</span>
                                    <div class="flex gap-2">
                                        <input x-model="slide.image" type="text" class="h-12 w-full rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-[#111827] outline-none transition focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50">
                                        <button type="button" @click="pickImage(index)" :disabled="uploading" class="inline-flex h-12 w-12 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 transition hover:border-[#114f8f] hover:text-[#114f8f] disabled:opacity-50">
                                            <i data-lucide="upload" class="h-4 w-4"></i>
                                        </button>
                                    </div>
                                    <input :id="`slide-image-${index}`" type="file" class="hidden" accept="image/*" @change="uploadSlide($event, index)">
                                </label>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <input x-model="slide.title" type="text" placeholder="Title" class="h-12 rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-[#111827] outline-none transition focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50">
                                    <input x-model="slide.ctaLabel" type="text" placeholder="CTA label" class="h-12 rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-[#111827] outline-none transition focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50">
                                </div>

                                <textarea x-model="slide.description" placeholder="Description" class="min-h-[96px] rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-[#111827] outline-none transition focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50"></textarea>

                                <input x-model="slide.ctaHref" type="text" placeholder="/shop" class="h-12 rounded-xl border border-gray-200 bg-white px-4 text-sm font-semibold text-[#111827] outline-none transition focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50">
                            </div>
                        </div>
                    </template>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="button" @click="save()" :disabled="saving || uploading" class="inline-flex h-12 items-center justify-center rounded-xl bg-[#114f8f] px-5 text-[13px] font-black uppercase tracking-[0.14em] text-white transition hover:bg-[#0d3f74] disabled:opacity-50">
                        <span x-text="saving ? 'Saving...' : 'Save Slider'"></span>
                    </button>
                </div>
            </section>

            <aside class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm xl:sticky xl:top-8 xl:self-start">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-black uppercase tracking-tight text-[#111827]">Preview</h2>
                        <p class="mt-1 text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Frontend hero stack</p>
                    </div>
                    <span x-show="uploading" x-cloak class="text-[11px] font-black uppercase tracking-[0.18em] text-[#114f8f]">Uploading...</span>
                </div>

                <div class="mt-6 space-y-4">
                    <template x-if="slides.length === 0">
                        <div class="rounded-2xl border border-dashed border-gray-200 bg-[#f8fbff] px-6 py-12 text-center">
                            <div class="text-[12px] font-black uppercase tracking-[0.16em] text-gray-400">Preview empty</div>
                        </div>
                    </template>

                    <template x-for="(slide, index) in slides" :key="`preview-${slide.id}`">
                        <article class="overflow-hidden rounded-2xl border border-gray-200 bg-[#f8fbff]">
                            <div class="flex h-48 items-center justify-center overflow-hidden bg-gray-100">
                                <template x-if="slide.image">
                                    <img :src="slide.image" :alt="slide.title || 'Slide preview'" class="h-full w-full object-cover">
                                </template>
                                <template x-if="!slide.image">
                                    <span class="text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">No image</span>
                                </template>
                            </div>
                            <div class="space-y-3 px-5 py-5">
                                <div class="text-[11px] font-black uppercase tracking-[0.18em] text-[#114f8f]" x-text="`Slide ${index + 1}`"></div>
                                <h3 class="text-lg font-black uppercase tracking-tight text-[#111827]" x-text="slide.title || 'Untitled slide'"></h3>
                                <p class="text-sm font-medium leading-6 text-gray-500" x-text="slide.description || 'Add a description for this hero card.'"></p>
                                <div class="flex items-center justify-between gap-4">
                                    <span class="rounded-full bg-white px-4 py-2 text-[11px] font-black uppercase tracking-[0.12em] text-[#111827] shadow-sm" x-text="slide.ctaLabel || 'CTA'"></span>
                                    <span class="truncate text-xs font-bold text-gray-400" x-text="slide.ctaHref || '/'"></span>
                                </div>
                            </div>
                        </article>
                    </template>
                </div>
            </aside>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function storefrontSliderApp() {
            return {
                slides: [],
                saving: false,
                uploading: false,
                notice: false,
                token: @json(session('admin_token')),
                init() {
                    const raw = document.getElementById('slides-json')?.textContent || '[]';
                    const parsed = JSON.parse(raw);
                    this.slides = Array.isArray(parsed) ? parsed : [];
                    this.refreshIcons();
                },
                refreshIcons() {
                    this.$nextTick(() => lucide.createIcons());
                },
                pickImage(index) {
                    document.getElementById(`slide-image-${index}`)?.click();
                },
                addSlide() {
                    this.slides.push({
                        id: crypto.randomUUID(),
                        image: '',
                        title: '',
                        description: '',
                        ctaLabel: '',
                        ctaHref: '',
                    });
                    this.refreshIcons();
                },
                removeSlide(index) {
                    this.slides.splice(index, 1);
                    this.refreshIcons();
                },
                async uploadSlide(event, index) {
                    const file = event.target.files?.[0];
                    if (!file || !this.token) return;

                    this.uploading = true;

                    try {
                        this.slides[index].image = await uploadFile(file, this.token);
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
                        const response = await fetch(@json(route('dashboard.storefront.slider.update')), {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': @json(csrf_token()),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ slides: this.slides }),
                        });

                        const payload = await response.json();
                        if (!response.ok) {
                            throw new Error(payload.message || payload.error || 'Failed to save slider.');
                        }

                        this.notice = true;
                        setTimeout(() => this.notice = false, 3000);
                    } catch (error) {
                        alert(error instanceof Error ? error.message : 'Failed to save slider.');
                    } finally {
                        this.saving = false;
                    }
                },
            };
        }
    </script>
@endpush
