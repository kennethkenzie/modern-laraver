@extends('admin.layout')

@section('title', 'Contact Page')

@section('content')
<div class="bg-[#f7f7f8] -mx-4 -mt-8 min-h-screen px-4 py-8 md:-mx-6 md:px-6 xl:-mx-10 xl:px-10"
     x-data="contactPageApp()" x-init="init()">
    <div class="mx-auto max-w-[860px]">

        {{-- Header --}}
        <div class="mb-8 flex items-start gap-4">
            <span class="mt-3 h-3 w-10 shrink-0 rounded-full bg-[#f6c400]"></span>
            <div>
                <h1 class="text-[32px] font-bold tracking-tight text-gray-900">Contact Page</h1>
                <p class="mt-1.5 text-[16px] font-medium text-gray-500">Edit the information displayed on the public Contact page.</p>
            </div>
        </div>

        {{-- Flash --}}
        <div x-show="saved" x-cloak x-transition
             class="mb-6 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-5 py-3 text-[14px] font-semibold text-green-700">
            <i data-lucide="check-circle" class="h-5 w-5"></i>
            Page saved successfully.
        </div>
        <div x-show="error" x-cloak x-transition
             class="mb-6 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-5 py-3 text-[14px] font-semibold text-red-700">
            <i data-lucide="alert-circle" class="h-5 w-5"></i>
            <span x-text="error"></span>
        </div>

        <form @submit.prevent="save" class="space-y-8">

            {{-- Hero --}}
            <div class="rounded-2xl border border-[#dbe3ec] bg-white p-6 shadow-sm">
                <h2 class="mb-5 text-[16px] font-bold text-gray-800">Hero Section</h2>
                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-[13px] font-semibold text-gray-600">Hero Title</label>
                        <input type="text" x-model="form.hero_title"
                               class="w-full rounded-xl border border-[#dbe3ec] px-4 py-2.5 text-[15px] text-gray-800 outline-none focus:border-[#0b63ce] focus:ring-2 focus:ring-[#0b63ce]/20" />
                    </div>
                    <div>
                        <label class="mb-1 block text-[13px] font-semibold text-gray-600">Hero Subtitle</label>
                        <textarea x-model="form.hero_subtitle" rows="2"
                                  class="w-full rounded-xl border border-[#dbe3ec] px-4 py-2.5 text-[15px] text-gray-800 outline-none focus:border-[#0b63ce] focus:ring-2 focus:ring-[#0b63ce]/20 resize-none"></textarea>
                    </div>
                </div>
            </div>

            {{-- Contact Details --}}
            <div class="rounded-2xl border border-[#dbe3ec] bg-white p-6 shadow-sm">
                <h2 class="mb-5 text-[16px] font-bold text-gray-800">Contact Details</h2>
                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-[13px] font-semibold text-gray-600">Address</label>
                        <textarea x-model="form.address" rows="2"
                                  class="w-full rounded-xl border border-[#dbe3ec] px-4 py-2.5 text-[15px] text-gray-800 outline-none focus:border-[#0b63ce] focus:ring-2 focus:ring-[#0b63ce]/20 resize-none"></textarea>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-[13px] font-semibold text-gray-600">Phone</label>
                            <input type="text" x-model="form.phone"
                                   class="w-full rounded-xl border border-[#dbe3ec] px-4 py-2.5 text-[15px] text-gray-800 outline-none focus:border-[#0b63ce] focus:ring-2 focus:ring-[#0b63ce]/20" />
                        </div>
                        <div>
                            <label class="mb-1 block text-[13px] font-semibold text-gray-600">Email</label>
                            <input type="email" x-model="form.email"
                                   class="w-full rounded-xl border border-[#dbe3ec] px-4 py-2.5 text-[15px] text-gray-800 outline-none focus:border-[#0b63ce] focus:ring-2 focus:ring-[#0b63ce]/20" />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-[13px] font-semibold text-gray-600">Working Hours</label>
                        <input type="text" x-model="form.working_hours"
                               placeholder="e.g. Mon – Sat: 8 AM – 6 PM"
                               class="w-full rounded-xl border border-[#dbe3ec] px-4 py-2.5 text-[15px] text-gray-800 outline-none focus:border-[#0b63ce] focus:ring-2 focus:ring-[#0b63ce]/20" />
                    </div>
                </div>
            </div>

            {{-- Map --}}
            <div class="rounded-2xl border border-[#dbe3ec] bg-white p-6 shadow-sm">
                <h2 class="mb-5 text-[16px] font-bold text-gray-800">Google Maps Embed</h2>
                <div>
                    <label class="mb-1 block text-[13px] font-semibold text-gray-600">Embed URL <span class="text-gray-400 font-normal">(src="" value from Google Maps embed code)</span></label>
                    <input type="text" x-model="form.map_embed_url"
                           placeholder="https://www.google.com/maps/embed?pb=…"
                           class="w-full rounded-xl border border-[#dbe3ec] px-4 py-2.5 text-[15px] text-gray-800 outline-none focus:border-[#0b63ce] focus:ring-2 focus:ring-[#0b63ce]/20" />
                </div>
                <template x-if="form.map_embed_url">
                    <div class="mt-4 overflow-hidden rounded-xl border border-[#dbe3ec]">
                        <iframe :src="form.map_embed_url"
                                class="h-56 w-full"
                                style="border:0"
                                allowfullscreen
                                loading="lazy"></iframe>
                    </div>
                </template>
            </div>

            {{-- Social Links --}}
            <div class="rounded-2xl border border-[#dbe3ec] bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-[16px] font-bold text-gray-800">Social Links</h2>
                    <button type="button" @click="addSocial()"
                            class="flex items-center gap-1.5 rounded-lg bg-[#0b63ce] px-3 py-1.5 text-[13px] font-semibold text-white hover:bg-[#0952b3] transition">
                        <i data-lucide="plus" class="h-4 w-4"></i> Add Link
                    </button>
                </div>
                <div class="space-y-3">
                    <template x-for="(link, i) in form.social_links" :key="i">
                        <div class="flex items-center gap-3">
                            <input type="text" x-model="link.platform" placeholder="Platform (e.g. Facebook)"
                                   class="w-36 shrink-0 rounded-lg border border-[#dbe3ec] bg-white px-3 py-2 text-[14px] outline-none focus:border-[#0b63ce]" />
                            <input type="text" x-model="link.url" placeholder="URL"
                                   class="flex-1 rounded-lg border border-[#dbe3ec] bg-white px-3 py-2 text-[14px] outline-none focus:border-[#0b63ce]" />
                            <button type="button" @click="form.social_links.splice(i, 1)"
                                    class="rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-500 transition">
                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                            </button>
                        </div>
                    </template>
                    <p x-show="form.social_links.length === 0" class="text-[14px] text-gray-400">No social links added yet.</p>
                </div>
            </div>

            {{-- Save --}}
            <div class="flex justify-end">
                <button type="submit"
                        :disabled="saving"
                        class="flex items-center gap-2 rounded-xl bg-[#0b63ce] px-8 py-3 text-[15px] font-bold text-white shadow hover:bg-[#0952b3] transition disabled:opacity-60">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    <span x-text="saving ? 'Saving…' : 'Save Changes'"></span>
                </button>
            </div>

        </form>
    </div>
</div>

<script>
    const PAGE_DATA = @json($page);

    function contactPageApp() {
        return {
            form: {},
            saving: false,
            saved: false,
            error: null,

            init() {
                this.form = JSON.parse(JSON.stringify(PAGE_DATA));
                if (!Array.isArray(this.form.social_links)) this.form.social_links = [];
                this.$nextTick(() => lucide.createIcons());
            },

            addSocial() {
                this.form.social_links.push({ platform: '', url: '' });
                this.$nextTick(() => lucide.createIcons());
            },

            async save() {
                this.saving = true;
                this.saved = false;
                this.error = null;
                try {
                    const res = await fetch('{{ route('dashboard.pages.contact.update') }}', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                        },
                        body: JSON.stringify(this.form),
                    });
                    if (!res.ok) throw new Error((await res.json()).message ?? 'Save failed');
                    this.saved = true;
                    setTimeout(() => this.saved = false, 3000);
                } catch (e) {
                    this.error = e.message;
                } finally {
                    this.saving = false;
                }
            },
        };
    }

    document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
</script>
@endsection
