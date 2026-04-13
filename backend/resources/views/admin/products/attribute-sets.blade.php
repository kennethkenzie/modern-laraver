@extends('admin.layout')

@section('title', 'Attribute Sets')

@section('content')
<script id="attribute-sets-json" type="application/json">{!! json_encode($attributeSets->values()) !!}</script>

<div class="-mx-4 -mt-4 sm:-mx-6 sm:-mt-6 min-h-screen bg-[#f7f7f8] px-4 pt-6 pb-16 sm:px-6 sm:pt-8 lg:px-8"
     x-data="attributeSetsApp('{{ session('admin_token') }}')"
     x-init="init()">

    <div class="mx-auto max-w-[1200px]">

        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-3">
                <span class="mt-[10px] h-2.5 w-8 rounded-full bg-[#f6c400] flex-shrink-0"></span>
                <div>
                    <h1 class="text-[28px] font-black uppercase tracking-tight text-gray-900">Attribute Sets</h1>
                    <p class="mt-1 text-sm text-gray-500">Define product variation groups such as Color, Size, or Storage.</p>
                </div>
            </div>
            <button type="button" @click="openForm(null)"
                class="inline-flex h-11 items-center gap-2 rounded-xl bg-[#1f2937] px-5 text-sm font-bold text-white transition hover:bg-black shadow-sm flex-shrink-0">
                <i data-lucide="plus" class="h-4 w-4"></i> New Set
            </button>
        </div>

        {{-- Stats --}}
        <div class="mb-6 grid gap-4 sm:grid-cols-2">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Sets</div>
                <div class="mt-2 text-3xl font-black text-gray-900" x-text="sets.length"></div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Active</div>
                <div class="mt-2 text-3xl font-black text-green-600" x-text="sets.filter(s => s.isActive).length"></div>
            </div>
        </div>

        {{-- List --}}
        <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">

            <div class="flex items-center gap-3 border-b border-gray-100 px-5 py-4">
                <i data-lucide="search" class="h-4 w-4 flex-shrink-0 text-gray-400"></i>
                <input x-model="search" type="text" placeholder="Search attribute sets…"
                    class="flex-1 bg-transparent text-sm text-gray-700 outline-none placeholder:text-gray-400" />
            </div>

            <div class="divide-y divide-gray-100">
                <template x-if="filtered.length === 0">
                    <div class="flex flex-col items-center gap-3 py-16 text-gray-400">
                        <i data-lucide="layers" class="h-9 w-9 opacity-40"></i>
                        <span class="text-sm font-bold">No attribute sets found.</span>
                    </div>
                </template>
                <template x-for="(set, idx) in filtered" :key="set.id">
                    <div>
                        {{-- Row --}}
                        <div class="flex items-center gap-4 px-5 py-4 transition hover:bg-gray-50/40">
                            <span class="w-6 text-center text-[12px] font-black text-gray-300" x-text="idx + 1"></span>
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#114f8f]/5 text-[#114f8f]">
                                <i data-lucide="layers" class="h-4 w-4"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-black text-gray-900" x-text="set.name"></span>
                                    <span x-text="inputTypeLabel(set.inputType)"
                                        class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-black uppercase tracking-widest"
                                        :class="inputTypeCss(set.inputType)"></span>
                                </div>
                                <div class="mt-0.5 text-[11px] font-bold text-gray-400" x-text="set.options.length + ' option(s)'"></div>
                            </div>
                            {{-- Active toggle --}}
                            <button @click="toggleActive(set)"
                                :class="set.isActive ? 'bg-[#114f8f]' : 'bg-gray-200'"
                                class="relative inline-flex h-6 w-12 shrink-0 items-center rounded-full transition">
                                <span :class="set.isActive ? 'translate-x-6' : 'translate-x-1'"
                                    class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition"></span>
                            </button>
                            {{-- Expand --}}
                            <button @click="expandedId = (expandedId === set.id ? null : set.id)"
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 transition hover:bg-gray-50">
                                <i :data-lucide="expandedId === set.id ? 'chevron-up' : 'chevron-down'" class="h-4 w-4"></i>
                            </button>
                            <button @click="openForm(set)"
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 transition hover:border-[#114f8f] hover:text-[#114f8f]">
                                <i data-lucide="pencil" class="h-4 w-4"></i>
                            </button>
                            <button @click="deleteSet(set.id)"
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-400 transition hover:border-red-500 hover:text-red-500">
                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                            </button>
                        </div>
                        {{-- Expanded options --}}
                        <div x-show="expandedId === set.id" x-collapse
                             class="border-t border-gray-100 bg-gray-50/50 px-5 py-4">
                            <p class="mb-3 text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Options</p>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="opt in set.options" :key="opt.id">
                                    <div class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-1.5 shadow-sm">
                                        <template x-if="set.inputType === 'color' && opt.colorHex">
                                            <span class="h-4 w-4 shrink-0 rounded-full border border-gray-200" :style="'background-color:' + opt.colorHex"></span>
                                        </template>
                                        <span class="text-[13px] font-bold text-gray-700" x-text="opt.value"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Slide-over form --}}
    <div x-show="showForm" x-cloak
         class="fixed inset-0 z-50 flex items-start justify-end bg-black/40 backdrop-blur-sm"
         @click.self="showForm = false">
        <div class="relative h-full w-full max-w-[480px] overflow-y-auto bg-white shadow-2xl">

            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-200 bg-white px-6 py-5">
                <h2 class="text-xl font-black text-gray-900" x-text="editingId ? 'Edit Attribute Set' : 'New Attribute Set'"></h2>
                <button @click="showForm = false" class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 text-gray-500 hover:text-gray-900 transition">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>

            <form @submit.prevent="saveSet" class="space-y-5 p-6">

                {{-- Name --}}
                <div>
                    <label class="mb-1.5 block text-[13px] font-black uppercase tracking-widest text-gray-500">Set Name <span class="text-red-500">*</span></label>
                    <input x-model="form.name" type="text" placeholder="e.g. Color, Size"
                        class="h-11 w-full rounded-xl border border-gray-300 px-4 text-sm font-bold text-gray-900 outline-none transition focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50" />
                </div>

                {{-- Input type --}}
                <div>
                    <label class="mb-2 block text-[13px] font-black uppercase tracking-widest text-gray-500">Input Type</label>
                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="t in inputTypes" :key="t.value">
                            <button type="button" @click="form.inputType = t.value"
                                :class="form.inputType === t.value ? 'border-[#114f8f] bg-[#114f8f] text-white' : 'border-gray-200 bg-white text-gray-500 hover:bg-gray-50'"
                                class="rounded-xl border px-3 py-2 text-[12px] font-black uppercase tracking-widest transition"
                                x-text="t.label"></button>
                        </template>
                    </div>
                </div>

                {{-- Options --}}
                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <label class="text-[13px] font-black uppercase tracking-widest text-gray-500">Options <span class="text-red-500">*</span></label>
                        <button type="button" @click="addOption()"
                            class="inline-flex h-7 items-center gap-1 rounded-lg bg-[#114f8f]/5 px-2.5 text-[11px] font-black uppercase tracking-widest text-[#114f8f] hover:bg-[#114f8f]/10 transition">
                            <i data-lucide="plus" class="h-3 w-3"></i> Add
                        </button>
                    </div>
                    <div class="max-h-[300px] space-y-2 overflow-y-auto pr-1">
                        <template x-for="(opt, i) in form.options" :key="opt._key">
                            <div class="flex items-center gap-2">
                                <template x-if="form.inputType === 'color'">
                                    <input type="color" x-model="opt.colorHex"
                                        class="h-9 w-9 shrink-0 cursor-pointer rounded-lg border border-gray-300 p-0.5" />
                                </template>
                                <span class="flex h-8 w-7 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-[11px] font-black text-gray-400" x-text="i+1"></span>
                                <input x-model="opt.value" type="text" :placeholder="'Option ' + (i+1)"
                                    class="h-9 flex-1 rounded-xl border border-gray-300 px-3 text-sm font-bold text-gray-900 outline-none transition focus:border-[#114f8f] focus:ring-2 focus:ring-blue-50" />
                                <button type="button" @click="form.options.splice(i, 1)"
                                    :disabled="form.options.length <= 1"
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-400 transition hover:border-red-400 hover:text-red-500 disabled:opacity-30">
                                    <i data-lucide="x" class="h-3.5 w-3.5"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Active --}}
                <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                    <span class="text-[13px] font-black uppercase tracking-widest text-gray-600">Active</span>
                    <button type="button" @click="form.isActive = !form.isActive"
                        :class="form.isActive ? 'bg-[#114f8f]' : 'bg-gray-200'"
                        class="relative inline-flex h-6 w-12 items-center rounded-full transition">
                        <span :class="form.isActive ? 'translate-x-6' : 'translate-x-1'"
                            class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition"></span>
                    </button>
                </div>

                <template x-if="error">
                    <p class="rounded-xl bg-red-50 px-4 py-3 text-sm font-bold text-red-600" x-text="error"></p>
                </template>

                <div class="flex gap-3 border-t border-gray-100 pt-4">
                    <button type="button" @click="showForm = false"
                        class="flex h-11 flex-1 items-center justify-center rounded-xl border border-gray-300 bg-white text-[13px] font-black uppercase text-gray-600 transition hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" :disabled="saving"
                        class="flex h-11 flex-1 items-center justify-center gap-2 rounded-xl bg-[#114f8f] text-[13px] font-black uppercase text-white shadow-lg transition hover:bg-[#0d3f74] disabled:opacity-50">
                        <span x-text="saving ? 'Saving…' : (editingId ? 'Update' : 'Create Set')"></span>
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
function attributeSetsApp(token) {
    return {
        sets: [],
        search: '',
        expandedId: null,
        showForm: false,
        editingId: null,
        saving: false,
        error: null,
        toast: null,
        inputTypes: [
            { value: 'dropdown', label: 'Dropdown' },
            { value: 'text',     label: 'Text' },
            { value: 'color',    label: 'Color' },
            { value: 'radio',    label: 'Radio' },
            { value: 'checkbox', label: 'Checkbox' },
        ],
        form: { name: '', inputType: 'dropdown', isActive: true, options: [] },

        get filtered() {
            const q = this.search.toLowerCase();
            return this.sets.filter(s => !q || s.name.toLowerCase().includes(q));
        },

        init() {
            const raw = document.getElementById('attribute-sets-json')?.textContent;
            this.sets = raw ? JSON.parse(raw) : [];
            this.$nextTick(() => lucide.createIcons());
        },

        inputTypeLabel(t) {
            return { dropdown:'Dropdown', text:'Text', color:'Color', radio:'Radio', checkbox:'Checkbox' }[t] || t;
        },

        inputTypeCss(t) {
            return {
                dropdown: 'bg-blue-50 text-blue-700 border-blue-200',
                text:     'bg-gray-100 text-gray-600 border-gray-200',
                color:    'bg-pink-50 text-pink-700 border-pink-200',
                radio:    'bg-green-50 text-green-700 border-green-200',
                checkbox: 'bg-amber-50 text-amber-700 border-amber-200',
            }[t] || 'bg-gray-100 text-gray-600 border-gray-200';
        },

        openForm(set) {
            this.error = null;
            if (set) {
                this.editingId = set.id;
                this.form = {
                    name: set.name,
                    inputType: set.inputType,
                    isActive: set.isActive,
                    options: set.options.length ? set.options.map(o => ({ ...o, _key: o.id })) : [{ _key: Date.now(), value: '', colorHex: '' }],
                };
            } else {
                this.editingId = null;
                this.form = { name: '', inputType: 'dropdown', isActive: true, options: [{ _key: Date.now(), value: '', colorHex: '' }] };
            }
            this.showForm = true;
            this.$nextTick(() => lucide.createIcons());
        },

        addOption() {
            this.form.options.push({ _key: Date.now() + Math.random(), value: '', colorHex: '' });
        },

        async saveSet() {
            if (!this.form.name.trim()) { this.error = 'Name is required.'; return; }
            const validOpts = this.form.options.filter(o => o.value.trim());
            if (validOpts.length === 0) { this.error = 'At least one option is required.'; return; }
            this.saving = true; this.error = null;
            try {
                const url = this.editingId ? `/api/admin/attribute-sets/${this.editingId}` : '/api/admin/attribute-sets';
                const method = this.editingId ? 'PATCH' : 'POST';
                const res = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
                    body: JSON.stringify({ ...this.form, options: validOpts.map(o => ({ value: o.value.trim(), colorHex: o.colorHex || null })) }),
                });
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || 'Save failed.');
                if (this.editingId) {
                    this.sets = this.sets.map(s => s.id === this.editingId ? json.attributeSet : s);
                } else {
                    this.sets.unshift(json.attributeSet);
                }
                this.showForm = false;
                this.showToast(this.editingId ? 'Attribute set updated.' : 'Attribute set created.');
            } catch (e) {
                this.error = e.message;
            } finally {
                this.saving = false;
            }
        },

        async toggleActive(set) {
            const res = await fetch(`/api/admin/attribute-sets/${set.id}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
                body: JSON.stringify({ isActive: !set.isActive }),
            });
            if (res.ok) set.isActive = !set.isActive;
        },

        async deleteSet(id) {
            if (!confirm('Delete this attribute set and all its options?')) return;
            const res = await fetch(`/api/admin/attribute-sets/${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': 'Bearer ' + token },
            });
            if (res.ok) {
                this.sets = this.sets.filter(s => s.id !== id);
                if (this.expandedId === id) this.expandedId = null;
                this.showToast('Attribute set deleted.');
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
