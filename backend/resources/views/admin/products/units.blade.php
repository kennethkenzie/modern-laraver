@extends('admin.layout')

@section('title', 'Units')

@section('content')
<script id="units-json" type="application/json">{!! json_encode($units->values()) !!}</script>

<div class="-mx-4 -mt-4 sm:-mx-6 sm:-mt-6 min-h-screen bg-[#eaeded] px-4 pt-6 pb-16 sm:px-6 sm:pt-8 lg:px-8"
     x-data="unitsApp('{{ session('admin_token') }}')"
     x-init="init()">

    {{-- Header --}}
    <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-[22px] font-bold text-[#0f1111]">Units</h1>
            <p class="mt-0.5 text-[13px] text-[#565959]">Manage units of measurement used in product listings.</p>
        </div>
        <button type="button" @click="openForm(null)"
            class="inline-flex h-10 items-center gap-2 rounded-lg border border-[#fcd200] bg-[#ffd814] px-5 text-[14px] font-medium text-[#0f1111] shadow-[inset_0_-1px_0_rgba(0,0,0,0.15)] transition hover:bg-[#f7ca00] active:scale-[.99] flex-shrink-0">
            <i data-lucide="plus" class="h-4 w-4"></i> Add Unit
        </button>
    </div>

    {{-- Stats --}}
    <div class="mb-5 grid gap-3 sm:grid-cols-2">
        <div class="rounded-lg border border-[#d5d9d9] bg-white p-4 shadow-[0_1px_3px_rgba(15,17,17,0.08)]">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-bold uppercase tracking-wider text-[#565959]">Total Units</span>
                <span class="flex h-7 w-7 items-center justify-center rounded-md bg-[#f0f2f2] text-[#565959]">
                    <i data-lucide="ruler" class="h-3.5 w-3.5"></i>
                </span>
            </div>
            <div class="mt-2.5 text-[28px] font-bold leading-none text-[#0f1111]" x-text="units.length"></div>
        </div>
        <div class="rounded-lg border border-[#d5d9d9] bg-white p-4 shadow-[0_1px_3px_rgba(15,17,17,0.08)]">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-bold uppercase tracking-wider text-[#565959]">Active</span>
                <span class="flex h-7 w-7 items-center justify-center rounded-md bg-[#eaf5f5] text-[#007185]">
                    <i data-lucide="circle-check" class="h-3.5 w-3.5"></i>
                </span>
            </div>
            <div class="mt-2.5 text-[28px] font-bold leading-none text-[#007185]" x-text="units.filter(u => u.isActive).length"></div>
        </div>
    </div>

    {{-- Table --}}
    <div class="rounded-lg border border-[#d5d9d9] bg-white shadow-[0_1px_3px_rgba(15,17,17,0.08)]">

        <div class="flex items-center gap-2.5 border-b border-[#d5d9d9] px-4 py-3">
            <div class="flex flex-1 items-center gap-2.5 rounded-md border border-[#a6a6a6] px-3 py-2 shadow-[inset_0_1px_2px_rgba(15,17,17,0.06)] focus-within:border-[#007185] focus-within:shadow-[inset_0_1px_2px_rgba(15,17,17,0.06),0_0_0_3px_rgba(0,113,133,0.1)] transition-shadow">
                <i data-lucide="search" class="h-4 w-4 flex-shrink-0 text-[#565959]"></i>
                <input x-model="search" type="text" placeholder="Search units…"
                    class="flex-1 bg-transparent text-[14px] text-[#0f1111] outline-none placeholder:text-[#8a8f98]" />
            </div>
            <span class="whitespace-nowrap rounded-md border border-[#d5d9d9] bg-[#f0f2f2] px-3 py-1.5 text-[12px] font-bold text-[#565959]"
                x-text="filtered.length + ' result' + (filtered.length !== 1 ? 's' : '')"></span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-[14px]">
                <thead>
                    <tr class="border-b border-[#d5d9d9] bg-[#f7fafa]">
                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#565959]">#</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#565959]">Name</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#565959]">Short Name</th>
                        <th class="hidden px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#565959] md:table-cell">Created</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-[#565959]">Status</th>
                        <th class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-[#565959]">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0f2f2]">
                    <tr x-show="filtered.length === 0">
                        <td colspan="6" class="px-4 py-14 text-center">
                            <div class="flex flex-col items-center gap-3 text-[#565959]">
                                <div class="flex h-12 w-12 items-center justify-center rounded-lg border border-[#d5d9d9] bg-[#f0f2f2]">
                                    <i data-lucide="ruler" class="h-5 w-5 opacity-50"></i>
                                </div>
                                <p class="text-[14px] font-semibold text-[#0f1111]">No units found</p>
                            </div>
                        </td>
                    </tr>
                    <template x-for="(unit, idx) in filtered" :key="unit.id">
                        <tr class="group transition hover:bg-[#f7fafa]">
                            <td class="px-4 py-3.5 text-[12px] font-bold text-[#8a8f98]" x-text="idx + 1"></td>
                            <td class="px-4 py-3.5 font-semibold text-[#0f1111]" x-text="unit.name"></td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex h-7 items-center rounded border border-[#d5d9d9] bg-[#f0f2f2] px-2.5 font-mono text-[12px] font-bold uppercase tracking-wider text-[#565959]" x-text="unit.shortName"></span>
                            </td>
                            <td class="hidden px-4 py-3.5 text-[13px] text-[#565959] md:table-cell" x-text="unit.createdAt"></td>
                            <td class="px-4 py-3.5">
                                <button @click="toggleActive(unit)"
                                    :class="unit.isActive
                                        ? 'border-[#c3e6cb] bg-[#d4edda] text-[#155724] hover:bg-[#c3e6cb]'
                                        : 'border-[#d5d9d9] bg-[#f0f2f2] text-[#565959] hover:bg-[#e3e6e6]'"
                                    class="inline-flex items-center gap-1 rounded border px-2 py-0.5 text-[12px] font-bold transition">
                                    <span :class="unit.isActive ? 'bg-[#28a745]' : 'bg-[#a6a6a6]'"
                                        class="h-1.5 w-1.5 rounded-full flex-shrink-0"></span>
                                    <span x-text="unit.isActive ? 'Active' : 'Inactive'"></span>
                                </button>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <button @click="openForm(unit)"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-[#d5d9d9] bg-white text-[#565959] transition hover:border-[#007185] hover:text-[#007185]">
                                        <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                    </button>
                                    <button @click="confirmDelete(unit)"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-[#d5d9d9] bg-white text-[#565959] transition hover:border-[#b12704] hover:text-[#b12704]">
                                        <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
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
        class="fixed inset-y-0 right-0 z-50 flex w-full max-w-[400px] flex-col border-l border-[#d5d9d9] bg-white shadow-2xl">

        <div class="flex items-center justify-between border-b border-[#d5d9d9] bg-[#f7fafa] px-5 py-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-[#565959]">Units</p>
                <h2 class="text-[16px] font-bold text-[#0f1111]" x-text="editingId ? 'Edit Unit' : 'Add New Unit'"></h2>
            </div>
            <button @click="showForm = false"
                class="flex h-8 w-8 items-center justify-center rounded-md border border-[#d5d9d9] bg-white text-[#565959] transition hover:text-[#0f1111]">
                <i data-lucide="x" class="h-4 w-4"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-5 py-5 space-y-4">
            <div>
                <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Unit Name <span class="text-[#b12704]">*</span></label>
                <input x-model="form.name" type="text" placeholder="e.g. Kilogram"
                    class="h-10 w-full rounded-md border border-[#a6a6a6] px-3 text-[14px] text-[#0f1111] outline-none shadow-[inset_0_1px_2px_rgba(15,17,17,0.06)] placeholder:text-[#8a8f98] focus:border-[#007185] focus:shadow-[inset_0_1px_2px_rgba(15,17,17,0.06),0_0_0_3px_rgba(0,113,133,0.1)] transition-shadow" />
            </div>
            <div>
                <label class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Short Name <span class="text-[#b12704]">*</span></label>
                <input x-model="form.shortName" type="text" placeholder="e.g. kg"
                    class="h-10 w-full rounded-md border border-[#a6a6a6] px-3 font-mono text-[14px] text-[#0f1111] outline-none shadow-[inset_0_1px_2px_rgba(15,17,17,0.06)] placeholder:text-[#8a8f98] focus:border-[#007185] focus:shadow-[inset_0_1px_2px_rgba(15,17,17,0.06),0_0_0_3px_rgba(0,113,133,0.1)] transition-shadow" />
            </div>
            <div class="flex items-center justify-between rounded-md border border-[#d5d9d9] bg-[#f7fafa] px-4 py-3">
                <div>
                    <div class="text-[13px] font-bold text-[#0f1111]">Active</div>
                    <div class="text-[12px] text-[#565959]">Visible in product forms</div>
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
                <button type="button" @click="saveUnit()" :disabled="saving"
                    class="flex-1 h-10 rounded-md border border-[#fcd200] bg-[#ffd814] text-[13px] font-medium text-[#0f1111] shadow-[inset_0_-1px_0_rgba(0,0,0,0.15)] transition hover:bg-[#f7ca00] disabled:opacity-50 inline-flex items-center justify-center gap-2">
                    <template x-if="saving">
                        <i data-lucide="loader-2" class="h-4 w-4 animate-spin"></i>
                    </template>
                    <span x-text="saving ? 'Saving…' : (editingId ? 'Save Changes' : 'Add Unit')"></span>
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
            <h3 class="text-[15px] font-bold text-[#0f1111]">Delete Unit</h3>
            <p class="mt-1 text-[13px] text-[#565959]">Are you sure you want to delete <strong class="text-[#0f1111]" x-text="deleteTarget?.name"></strong>? This cannot be undone.</p>
            <div class="mt-5 flex gap-2">
                <button @click="showDeleteModal = false"
                    class="flex-1 h-10 rounded-md border border-[#d5d9d9] bg-white text-[13px] font-bold text-[#565959] transition hover:bg-[#f0f2f2]">Cancel</button>
                <button @click="deleteUnit()"
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
function unitsApp(token) {
    return {
        units: [],
        search: '',
        showForm: false,
        editingId: null,
        saving: false,
        error: null,
        toast: null,
        showDeleteModal: false,
        deleteTarget: null,
        form: { name: '', shortName: '', isActive: true },

        get filtered() {
            const q = this.search.toLowerCase();
            return this.units.filter(u => !q || u.name.toLowerCase().includes(q) || u.shortName.toLowerCase().includes(q));
        },

        init() {
            const raw = document.getElementById('units-json')?.textContent;
            this.units = raw ? JSON.parse(raw) : [];
            this.$nextTick(() => lucide.createIcons());
        },

        openForm(unit) {
            this.error = null;
            if (unit) {
                this.editingId = unit.id;
                this.form = { name: unit.name, shortName: unit.shortName, isActive: unit.isActive };
            } else {
                this.editingId = null;
                this.form = { name: '', shortName: '', isActive: true };
            }
            this.showForm = true;
            this.$nextTick(() => lucide.createIcons());
        },

        async saveUnit() {
            if (!this.form.name.trim()) { this.error = 'Name is required.'; return; }
            if (!this.form.shortName.trim()) { this.error = 'Short name is required.'; return; }
            this.saving = true; this.error = null;
            try {
                const url = this.editingId ? `${window.API_BASE}/api/admin/units/${this.editingId}` : `${window.API_BASE}/api/admin/units`;
                const method = this.editingId ? 'PATCH' : 'POST';
                const res = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
                    body: JSON.stringify(this.form),
                });
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || 'Save failed.');
                if (this.editingId) {
                    this.units = this.units.map(u => u.id === this.editingId ? json.unit : u);
                } else {
                    this.units.unshift(json.unit);
                }
                this.showForm = false;
                this.showToast(this.editingId ? 'Unit updated.' : 'Unit added.');
            } catch (e) {
                this.error = e.message;
            } finally {
                this.saving = false;
            }
        },

        async toggleActive(unit) {
            const prev = unit.isActive;
            unit.isActive = !unit.isActive;
            const res = await fetch(`${window.API_BASE}/api/admin/units/${unit.id}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
                body: JSON.stringify({ isActive: unit.isActive }),
            });
            if (!res.ok) unit.isActive = prev;
        },

        confirmDelete(unit) {
            this.deleteTarget = unit;
            this.showDeleteModal = true;
            this.$nextTick(() => lucide.createIcons());
        },

        async deleteUnit() {
            if (!this.deleteTarget) return;
            const id = this.deleteTarget.id;
            this.showDeleteModal = false;
            const res = await fetch(`${window.API_BASE}/api/admin/units/${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': 'Bearer ' + token },
            });
            if (res.ok) {
                this.units = this.units.filter(u => u.id !== id);
                this.showToast('Unit deleted.');
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
