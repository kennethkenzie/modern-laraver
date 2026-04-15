@extends('admin.layout')

@section('title', 'Attribute Sets')

@section('content')
<script id="attribute-sets-json" type="application/json">{!! json_encode($attributeSets->values()) !!}</script>

<div class="-mx-4 -mt-4 sm:-mx-6 sm:-mt-6 min-h-screen bg-[#eaeded] px-4 pt-6 pb-16 sm:px-6 sm:pt-8 lg:px-8"
     x-data="attributeSetsApp('{{ session('admin_token') }}')"
     x-init="init()">

    {{-- Header --}}
    <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-[22px] font-bold text-[#0f1111]">Attribute Sets</h1>
            <p class="mt-0.5 text-[13px] text-[#565959]">Define product variation groups such as Color, Size, or Storage.</p>
        </div>
        <button type="button" @click="openForm(null)"
            class="inline-flex h-10 items-center gap-2 rounded-lg border border-[#fcd200] bg-[#ffd814] px-5 text-[14px] font-medium text-[#0f1111] shadow-[inset_0_-1px_0_rgba(0,0,0,0.15)] transition hover:bg-[#f7ca00] active:scale-[.99] flex-shrink-0">
            <i data-lucide="plus" class="h-4 w-4"></i> New Set
        </button>
    </div>

    {{-- Stats --}}
    <div class="mb-5 grid gap-3 sm:grid-cols-2">
        <div class="rounded-lg border border-[#d5d9d9] bg-white p-4 shadow-[0_1px_3px_rgba(15,17,17,0.08)]">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-bold uppercase tracking-wider text-[#565959]">Total Sets</span>
                <span class="flex h-7 w-7 items-center justify-center rounded-md bg-[#f0f2f2] text-[#565959]">
                    <i data-lucide="sliders" class="h-3.5 w-3.5"></i>
                </span>
            </div>
            <div class="mt-2.5 text-[28px] font-bold leading-none text-[#0f1111]" x-text="sets.length"></div>
        </div>
        <div class="rounded-lg border border-[#d5d9d9] bg-white p-4 shadow-[0_1px_3px_rgba(15,17,17,0.08)]">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-bold uppercase tracking-wider text-[#565959]">Active</span>
                <span class="flex h-7 w-7 items-center justify-center rounded-md bg-[#eaf5f5] text-[#007185]">
                    <i data-lucide="circle-check" class="h-3.5 w-3.5"></i>
                </span>
            </div>
            <div class="mt-2.5 text-[28px] font-bold leading-none text-[#007185]" x-text="sets.filter(s => s.isActive).length"></div>
        </div>
    </div>

    {{-- List --}}
    <div class="rounded-lg border border-[#d5d9d9] bg-white shadow-[0_1px_3px_rgba(15,17,17,0.08)]">

        <div class="flex items-center gap-2.5 border-b border-[#d5d9d9] px-4 py-3">
            <div class="flex flex-1 items-center gap-2.5 rounded-md border border-[#a6a6a6] px-3 py-2 shadow-[inset_0_1px_2px_rgba(15,17,17,0.06)] focus-within:border-[#007185] focus-within:shadow-[inset_0_1px_2px_rgba(15,17,17,0.06),0_0_0_3px_rgba(0,113,133,0.1)] transition-shadow">
                <i data-lucide="search" class="h-4 w-4 flex-shrink-0 text-[#565959]"></i>
                <input x-model="search" type="text" placeholder="Search attribute sets…"
                    class="flex-1 bg-transparent text-[14px] text-[#0f1111] outline-none placeholder:text-[#8a8f98]" />
            </div>
        </div>

        <div class="divide-y divide-[#f0f2f2]">
            <div x-show="filtered.length === 0" class="flex flex-col items-center gap-3 py-14 text-[#565959]">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg border border-[#d5d9d9] bg-[#f0f2f2]">
                    <i data-lucide="sliders" class="h-5 w-5 opacity-50"></i>
                </div>
                <p class="text-[14px] font-semibold text-[#0f1111]">No attribute sets found</p>
            </div>

            <template x-for="(set, idx) in filtered" :key="set.id">
                <div>
                    {{-- Row --}}
                    <div class="flex items-center gap-3 px-4 py-3.5 transition hover:bg-[#f7fafa]">
                        <span class="w-6 text-center text-[12px] font-bold text-[#8a8f98]" x-text="idx + 1"></span>
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-[#d5d9d9] bg-[#f0f2f2] text-[#565959]">
                            <i data-lucide="sliders" class="h-4 w-4"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-semibold text-[#0f1111]" x-text="set.name"></span>
                                <span x-text="inputTypeLabel(set.inputType)"
                                    class="inline-flex items-center rounded border px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide"
                                    :class="inputTypeCss(set.inputType)"></span>
                            </div>
                            <div class="mt-0.5 text-[12px] text-[#565959]" x-text="set.options.length + ' option(s)'"></div>
                        </div>
                        {{-- Active badge --}}
                        <button @click="toggleActive(set)"
                            :class="set.isActive
                                ? 'border-[#c3e6cb] bg-[#d4edda] text-[#155724] hover:bg-[#c3e6cb]'
                                : 'border-[#d5d9d9] bg-[#f0f2f2] text-[#565959] hover:bg-[#e3e6e6]'"
                            class="inline-flex shrink-0 items-center gap-1 rounded border px-2 py-0.5 text-[12px] font-bold transition">
                            <span :class="set.isActive ? 'bg-[#28a745]' : 'bg-[#a6a6a6]'"
                                class="h-1.5 w-1.5 rounded-full flex-shrink-0"></span>
                            <span x-text="set.isActive ? 'Active' : 'Inactive'"></span>
                        </button>
                        {{-- Expand --}}
                        <button @click="expandedId = (expandedId === set.id ? null : set.id)"
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md border border-[#d5d9d9] bg-white text-[#565959] transition hover:bg-[#f0f2f2]">
                            <i :data-lucide="expandedId === set.id ? 'chevron-up' : 'chevron-down'" class="h-4 w-4"></i>
                        </button>
                        <button @click="openForm(set)"
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md border border-[#d5d9d9] bg-white text-[#565959] transition hover:border-[#007185] hover:text-[#007185]">
                            <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                        </button>
                        <button @click="confirmDelete(set)"
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md border border-[#d5d9d9] bg-white text-[#565959] transition hover:border-[#b12704] hover:text-[#b12704]">
                            <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                        </button>
                    </div>

                    {{-- Expanded options --}}
                    <div x-show="expandedId === set.id" x-collapse
                         class="border-t border-[#f0f2f2] bg-[#f7fafa] px-4 py-4">
                        <p class="mb-3 text-[11px] font-bold uppercase tracking-wider text-[#565959]">Options</p>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="opt in set.options" :key="opt.id">
                                <div class="inline-flex items-center gap-2 rounded-md border border-[#d5d9d9] bg-white px-3 py-1.5">
                                    <template x-if="set.inputType === 'color' && opt.colorHex">
                                        <span class="h-4 w-4 shrink-0 rounded-full border border-[#d5d9d9]" :style="'background-color:' + opt.colorHex"></span>
                                    </template>
                                    <span class="text-[13px] font-semibold text-[#0f1111]" x-text="opt.value"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
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
        class="fixed inset-y-0 right-0 z-50 flex w-full max-w-[480px] flex-col border-l border-[#d5d9d9] bg-white shadow-2xl">

        <div class="flex items-center justify-between border-b border-[#d5d9d9] bg-[#f7fafa] px-5 py-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-[#565959]">Attribute Sets</p>
                <h2 class="text-[16px] font-bold text-[#0f1111]" x-text="editingId ? 'Edit Attribute Set' : 'New Attribute Set'"></h2>
            </div>
            <button @click="showForm = false"
                class="flex h-8 w-8 items-center justify-center rounded-md border border-[#d5d9d9] bg-white text-[#565959] transition hover:text-[#0f1111]">
                <i data-lucide="x" class="h-4 w-4"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-5 py-5 space-y-4">

            <div>
                <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Set Name <span class="text-[#b12704]">*</span></label>
                <input x-model="form.name" type="text" placeholder="e.g. Color, Size"
                    class="h-10 w-full rounded-md border border-[#a6a6a6] px-3 text-[14px] text-[#0f1111] outline-none shadow-[inset_0_1px_2px_rgba(15,17,17,0.06)] placeholder:text-[#8a8f98] focus:border-[#007185] focus:shadow-[inset_0_1px_2px_rgba(15,17,17,0.06),0_0_0_3px_rgba(0,113,133,0.1)] transition-shadow" />
            </div>

            <div>
                <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Input Type</label>
                <div class="grid grid-cols-3 gap-2">
                    <template x-for="t in inputTypes" :key="t.value">
                        <button type="button" @click="form.inputType = t.value"
                            :class="form.inputType === t.value
                                ? 'border-[#007185] bg-[#eaf5f5] text-[#007185]'
                                : 'border-[#d5d9d9] bg-white text-[#565959] hover:bg-[#f0f2f2]'"
                            class="rounded-md border px-3 py-2 text-[12px] font-bold uppercase tracking-wide transition"
                            x-text="t.label"></button>
                    </template>
                </div>
            </div>

            <div>
                <div class="mb-2 flex items-center justify-between">
                    <label class="text-[13px] font-bold text-[#0f1111]">Options <span class="text-[#b12704]">*</span></label>
                    <button type="button" @click="addOption()"
                        class="inline-flex h-7 items-center gap-1 rounded border border-[#d5d9d9] bg-[#f0f2f2] px-2.5 text-[12px] font-bold text-[#565959] hover:bg-[#e3e6e6] transition">
                        <i data-lucide="plus" class="h-3 w-3"></i> Add
                    </button>
                </div>
                <div class="max-h-[280px] space-y-2 overflow-y-auto pr-1">
                    <template x-for="(opt, i) in form.options" :key="opt._key">
                        <div class="flex items-center gap-2">
                            <template x-if="form.inputType === 'color'">
                                <input type="color" x-model="opt.colorHex"
                                    class="h-9 w-9 shrink-0 cursor-pointer rounded-md border border-[#d5d9d9] p-0.5" />
                            </template>
                            <span class="flex h-8 w-7 shrink-0 items-center justify-center rounded-md border border-[#d5d9d9] bg-[#f0f2f2] text-[11px] font-bold text-[#565959]" x-text="i+1"></span>
                            <input x-model="opt.value" type="text" :placeholder="'Option ' + (i+1)"
                                class="h-9 flex-1 rounded-md border border-[#a6a6a6] px-3 text-[14px] text-[#0f1111] outline-none focus:border-[#007185] transition" />
                            <button type="button" @click="form.options.splice(i, 1)"
                                :disabled="form.options.length <= 1"
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-[#d5d9d9] bg-white text-[#565959] transition hover:border-[#b12704] hover:text-[#b12704] disabled:opacity-30">
                                <i data-lucide="x" class="h-3.5 w-3.5"></i>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <div class="border-t border-[#d5d9d9]"></div>

            <div class="flex items-center justify-between rounded-md border border-[#d5d9d9] bg-[#f7fafa] px-4 py-3">
                <div>
                    <div class="text-[13px] font-bold text-[#0f1111]">Active</div>
                    <div class="text-[12px] text-[#565959]">Available in product forms</div>
                </div>
                <button type="button" @click="form.isActive = !form.isActive"
                    :class="form.isActive ? 'bg-[#007185]' : 'bg-[#d5d9d9]'"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200">
                    <span :class="form.isActive ? 'translate-x-5' : 'translate-x-0'"
                        class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200"></span>
                </button>
            </div>

            <div x-show="error"
                class="rounded-md border border-[#f5c6cb] bg-[#fef2f2] px-4 py-3 text-[13px] font-medium text-[#b12704]"
                x-text="error">
            </div>
        </div>

        <div class="border-t border-[#d5d9d9] bg-[#f7fafa] px-5 py-4">
            <div class="flex gap-2">
                <button type="button" @click="showForm = false"
                    class="flex-1 h-10 rounded-md border border-[#d5d9d9] bg-white text-[13px] font-bold text-[#565959] transition hover:bg-[#f0f2f2]">
                    Cancel
                </button>
                <button type="button" @click="saveSet()" :disabled="saving"
                    class="flex-1 h-10 rounded-md border border-[#fcd200] bg-[#ffd814] text-[13px] font-medium text-[#0f1111] shadow-[inset_0_-1px_0_rgba(0,0,0,0.15)] transition hover:bg-[#f7ca00] disabled:opacity-50 inline-flex items-center justify-center gap-2">
                    <template x-if="saving">
                        <i data-lucide="loader-2" class="h-4 w-4 animate-spin"></i>
                    </template>
                    <span x-text="saving ? 'Saving…' : (editingId ? 'Save Changes' : 'Create Set')"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ── Delete Confirm Modal ── --}}
    <div x-show="showDeleteModal" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40 px-4"
        @click.self="showDeleteModal = false">
        <div class="w-full max-w-sm rounded-lg border border-[#d5d9d9] bg-white p-6 shadow-[0_8px_24px_rgba(15,17,17,0.18)]"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">
            <div class="flex h-10 w-10 items-center justify-center rounded-md border border-[#f5c6cb] bg-[#fef2f2] text-[#b12704] mb-4">
                <i data-lucide="trash-2" class="h-4 w-4"></i>
            </div>
            <h3 class="text-[15px] font-bold text-[#0f1111]">Delete Attribute Set</h3>
            <p class="mt-1 text-[13px] text-[#565959]">Delete <strong class="text-[#0f1111]" x-text="deleteTarget?.name"></strong> and all its options? This cannot be undone.</p>
            <div class="mt-5 flex gap-2">
                <button @click="showDeleteModal = false"
                    class="flex-1 h-10 rounded-md border border-[#d5d9d9] bg-white text-[13px] font-bold text-[#565959] transition hover:bg-[#f0f2f2]">Cancel</button>
                <button @click="deleteSet()"
                    class="flex-1 h-10 rounded-md border border-[#b12704] bg-[#b12704] text-[13px] font-bold text-white transition hover:bg-[#9b2401] inline-flex items-center justify-center gap-2">
                    <i data-lucide="trash-2" class="h-4 w-4"></i> Delete
                </button>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div x-show="toast" x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="fixed bottom-6 right-6 z-[70] flex items-center gap-3 rounded-md border border-[#d5d9d9] bg-white px-4 py-3 shadow-[0_4px_16px_rgba(15,17,17,0.15)]">
        <div class="flex h-6 w-6 items-center justify-center rounded border border-[#c3e6cb] bg-[#d4edda] text-[#155724]">
            <i data-lucide="check" class="h-3.5 w-3.5"></i>
        </div>
        <span class="text-[13px] font-bold text-[#0f1111]" x-text="toast"></span>
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
        showDeleteModal: false,
        deleteTarget: null,
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
                dropdown: 'bg-[#eaf5f5] text-[#007185] border-[#007185]/30',
                text:     'bg-[#f0f2f2] text-[#565959] border-[#d5d9d9]',
                color:    'bg-[#fef5ec] text-[#c45500] border-[#f5cba7]',
                radio:    'bg-[#d4edda] text-[#155724] border-[#c3e6cb]',
                checkbox: 'bg-[#f0f2f2] text-[#565959] border-[#d5d9d9]',
            }[t] || 'bg-[#f0f2f2] text-[#565959] border-[#d5d9d9]';
        },

        openForm(set) {
            this.error = null;
            if (set) {
                this.editingId = set.id;
                this.form = {
                    name: set.name, inputType: set.inputType, isActive: set.isActive,
                    options: set.options.length
                        ? set.options.map(o => ({ ...o, _key: o.id }))
                        : [{ _key: Date.now(), value: '', colorHex: '' }],
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
                const url = this.editingId
                    ? `${window.API_BASE}/api/admin/attribute-sets/${this.editingId}`
                    : `${window.API_BASE}/api/admin/attribute-sets`;
                const res = await fetch(url, {
                    method: this.editingId ? 'PATCH' : 'POST',
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
            const prev = set.isActive;
            set.isActive = !set.isActive;
            const res = await fetch(`${window.API_BASE}/api/admin/attribute-sets/${set.id}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
                body: JSON.stringify({ isActive: set.isActive }),
            });
            if (!res.ok) set.isActive = prev;
        },

        confirmDelete(set) {
            this.deleteTarget = set;
            this.showDeleteModal = true;
            this.$nextTick(() => lucide.createIcons());
        },

        async deleteSet() {
            if (!this.deleteTarget) return;
            const id = this.deleteTarget.id;
            this.showDeleteModal = false;
            const res = await fetch(`${window.API_BASE}/api/admin/attribute-sets/${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': 'Bearer ' + token },
            });
            if (res.ok) {
                this.sets = this.sets.filter(s => s.id !== id);
                if (this.expandedId === id) this.expandedId = null;
                this.showToast('Attribute set deleted.');
            }
            this.deleteTarget = null;
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
