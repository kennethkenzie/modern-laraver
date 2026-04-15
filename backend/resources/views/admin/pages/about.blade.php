@extends('admin.layout')

@section('title', 'About Us Page')

@section('content')
<div class="bg-[#f7f7f8] -mx-4 -mt-8 min-h-screen px-4 py-8 md:-mx-6 md:px-6 xl:-mx-10 xl:px-10"
     x-data="aboutPageApp()" x-init="init()">
    <div class="mx-auto max-w-[860px]">

        {{-- Header --}}
        <div class="mb-8 flex items-start gap-4">
            <span class="mt-3 h-3 w-10 shrink-0 rounded-full bg-[#0b63ce]"></span>
            <div>
                <h1 class="text-[32px] font-bold tracking-tight text-gray-900">About Us Page</h1>
                <p class="mt-1.5 text-[16px] font-medium text-gray-500">Edit the content displayed on the public About Us page.</p>
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
                    <div>
                        <label class="mb-1 block text-[13px] font-semibold text-gray-600">Hero Image URL</label>
                        <input type="text" x-model="form.hero_image"
                               placeholder="https://…"
                               class="w-full rounded-xl border border-[#dbe3ec] px-4 py-2.5 text-[15px] text-gray-800 outline-none focus:border-[#0b63ce] focus:ring-2 focus:ring-[#0b63ce]/20" />
                        <template x-if="form.hero_image">
                            <img :src="form.hero_image" class="mt-3 h-32 w-full rounded-xl object-cover" />
                        </template>
                    </div>
                </div>
            </div>

            {{-- Mission --}}
            <div class="rounded-2xl border border-[#dbe3ec] bg-white p-6 shadow-sm">
                <h2 class="mb-5 text-[16px] font-bold text-gray-800">Mission</h2>
                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-[13px] font-semibold text-gray-600">Title</label>
                        <input type="text" x-model="form.mission_title"
                               class="w-full rounded-xl border border-[#dbe3ec] px-4 py-2.5 text-[15px] text-gray-800 outline-none focus:border-[#0b63ce] focus:ring-2 focus:ring-[#0b63ce]/20" />
                    </div>
                    <div>
                        <label class="mb-1 block text-[13px] font-semibold text-gray-600">Body</label>
                        <textarea x-model="form.mission_body" rows="4"
                                  class="w-full rounded-xl border border-[#dbe3ec] px-4 py-2.5 text-[15px] text-gray-800 outline-none focus:border-[#0b63ce] focus:ring-2 focus:ring-[#0b63ce]/20 resize-y"></textarea>
                    </div>
                </div>
            </div>

            {{-- Vision --}}
            <div class="rounded-2xl border border-[#dbe3ec] bg-white p-6 shadow-sm">
                <h2 class="mb-5 text-[16px] font-bold text-gray-800">Vision</h2>
                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-[13px] font-semibold text-gray-600">Title</label>
                        <input type="text" x-model="form.vision_title"
                               class="w-full rounded-xl border border-[#dbe3ec] px-4 py-2.5 text-[15px] text-gray-800 outline-none focus:border-[#0b63ce] focus:ring-2 focus:ring-[#0b63ce]/20" />
                    </div>
                    <div>
                        <label class="mb-1 block text-[13px] font-semibold text-gray-600">Body</label>
                        <textarea x-model="form.vision_body" rows="4"
                                  class="w-full rounded-xl border border-[#dbe3ec] px-4 py-2.5 text-[15px] text-gray-800 outline-none focus:border-[#0b63ce] focus:ring-2 focus:ring-[#0b63ce]/20 resize-y"></textarea>
                    </div>
                </div>
            </div>

            {{-- Team Members --}}
            <div class="rounded-2xl border border-[#dbe3ec] bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-[16px] font-bold text-gray-800">Team Members</h2>
                    <button type="button" @click="addMember()"
                            class="flex items-center gap-1.5 rounded-lg bg-[#0b63ce] px-3 py-1.5 text-[13px] font-semibold text-white hover:bg-[#0952b3] transition">
                        <i data-lucide="plus" class="h-4 w-4"></i> Add Member
                    </button>
                </div>
                <div>
                    <label class="mb-1 block text-[13px] font-semibold text-gray-600">Section Heading</label>
                    <input type="text" x-model="form.team_heading"
                           class="mb-5 w-full rounded-xl border border-[#dbe3ec] px-4 py-2.5 text-[15px] text-gray-800 outline-none focus:border-[#0b63ce] focus:ring-2 focus:ring-[#0b63ce]/20" />
                </div>
                <div class="space-y-4">
                    <template x-for="(member, i) in form.team_members" :key="i">
                        <div class="flex items-start gap-4 rounded-xl border border-[#dbe3ec] bg-[#f7f9fc] p-4">
                            <div class="flex-1 grid gap-3 sm:grid-cols-3">
                                <div>
                                    <label class="mb-1 block text-[12px] font-semibold text-gray-500">Name</label>
                                    <input type="text" x-model="member.name"
                                           class="w-full rounded-lg border border-[#dbe3ec] bg-white px-3 py-2 text-[14px] outline-none focus:border-[#0b63ce]" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-[12px] font-semibold text-gray-500">Role</label>
                                    <input type="text" x-model="member.role"
                                           class="w-full rounded-lg border border-[#dbe3ec] bg-white px-3 py-2 text-[14px] outline-none focus:border-[#0b63ce]" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-[12px] font-semibold text-gray-500">Avatar URL</label>
                                    <input type="text" x-model="member.avatar"
                                           class="w-full rounded-lg border border-[#dbe3ec] bg-white px-3 py-2 text-[14px] outline-none focus:border-[#0b63ce]" />
                                </div>
                            </div>
                            <button type="button" @click="form.team_members.splice(i, 1)"
                                    class="mt-5 rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-500 transition">
                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                            </button>
                        </div>
                    </template>
                    <p x-show="form.team_members.length === 0" class="text-[14px] text-gray-400">No team members yet.</p>
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

    function aboutPageApp() {
        return {
            form: {},
            saving: false,
            saved: false,
            error: null,

            init() {
                this.form = JSON.parse(JSON.stringify(PAGE_DATA));
                if (!Array.isArray(this.form.team_members)) this.form.team_members = [];
                this.$nextTick(() => lucide.createIcons());
            },

            addMember() {
                this.form.team_members.push({ name: '', role: '', avatar: '' });
                this.$nextTick(() => lucide.createIcons());
            },

            async save() {
                this.saving = true;
                this.saved = false;
                this.error = null;
                try {
                    const res = await fetch('{{ route('dashboard.pages.about.update') }}', {
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
