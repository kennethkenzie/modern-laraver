@extends('admin.layout')

@section('title', 'Units')

@section('content')
<script id="units-json" type="application/json">{!! json_encode($units->values()) !!}</script>

<div class="-mx-4 -mt-4 sm:-mx-6 sm:-mt-6 min-h-screen bg-[#f7f7f8] px-4 pt-6 pb-16 sm:px-6 sm:pt-8 lg:px-8"
     x-data="unitsApp('{{ session('admin_token') }}')"
     x-init="init()">

    <div class="mx-auto max-w-[1200px]">

        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-3">
                <span class="mt-[10px] h-2.5 w-8 rounded-full bg-[#f6c400] flex-shrink-0"></span>
                <div>
                    <h1 class="text-[28px] font-black uppercase tracking-tight text-gray-900">Units</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage units of measurement used in product listings.</p>
                </div>
            </div>
            <button type="button" @click="openForm(null)"
                class="inline-flex h-11 items-center gap-2 rounded-xl bg-[#1f2937] px-5 text-sm font-bold text-white transition hover:bg-black shadow-sm flex-shrink-0">
                <i data-lucide="plus" class="h-4 w-4"></i> Add Unit
            </button>
        </div>

        {{-- Stats --}}
        <div class="mb-6 grid gap-4 sm:grid-cols-2">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Units</div>
                <div class="mt-2 text-3xl font-black text-gray-900" x-text="units.length"></div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Active</div>
                <div class="mt-2 text-3xl font-black text-green-600" x-text="units.filter(u => u.isActive).length"></div>
            </div>
        </div>

        {{-- Table --}}
        <div class="rounded-3xl border border-gray-200 bg-white shadow-sm">

            <div class="flex items-center gap-3 border-b border-gray-100 px-5 py-4">
                <i data-lucide="search" class="h-4 w-4 flex-shrink-0 text-gray-400"></i>
                <input x-model="search" type="text" placeholder="Search units…"
                    class="flex-1 bg-transparent text-sm text-gray-700 outline-none placeholder:text-gray-400" />
                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-500"
                    x-text="filtered.length + ' results'"></span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50 text-left text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">
                            <th class="px-5 py-4">#</th>
                            <th class="px-5 py-4">Name</th>
                            <th class="px-5 py-4">Short Name</th>
                            <th class="px-5 py-4">Created</th>
                            <th class="px-5 py-4 text-center">Status</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <template x-if="filtered.length === 0">
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <div class="flex flex-col items-center gap-3 text-gray-400">
                                        <i data-lucide="ruler" class="h-9 w-9 opacity-40"></i>
                                        <span class="text-sm font-bold">No units found.</span>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <template x-for="(unit, idx) in filtered" :key="unit.id">
                            <tr class="transition-colors hover:bg-gray-50/40">
                                <td class="px-5 py-4 font-bold text-gray-400" x-text="idx + 1"></td>
                                <td class="px-5 py-4 font-bold text-gray-900" x-text="unit.name"></td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex h-7 items-center rounded-lg bg-gray-100 px-3 text-[13px] font-black uppercase tracking-widest text-gray-600" x-text="unit.shortName"></span>
                                </td>
                                <td class="px-5 py-4 font-medium text-gray-500" x-text="unit.createdAt"></td>
                                <td class="px-5 py-4 text-center">
                                    <button @click="toggleActive(unit)"
                                        :class="unit.isActive ? 'bg-[#114f8f]' : 'bg-gray-200'"
                                        class="relative inline-flex h-6 w-12 items-center rounded-full transition">
                                        <span :class="unit.isActive ? 'translate-x-6' : 'translate-x-1'"
                                            class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition"></span>
                                    </button>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button @click="openForm(unit)"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 text-gray-600 transition hover:bg-gray-200">
                                            <i data-lucide="pencil" class="h-4 w-4"></i>
                                        </button>
                                        <button @click="deleteUnit(unit.id)"
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

    {{-- Slide-over form --}}
    <div x-show="showForm" x-cloak
         class="fixed inset-0 z-50 flex items-start justify-end bg-black/40 backdrop-blur-sm"
         @click.self="showForm = false">
        <div class="relative h-full w-full max-w-[420px] overflow-y-auto bg-white shadow-2xl">

            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-200 bg-white px-6 py-5">
                <h2 class="text-xl font-black text-gray-900" x-text="editingId ? 'Edit Unit' : 'Add Unit'"></h2>
                <button @click="showForm = false" class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 text-gray-500 hover:text-gray-900 transition">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>

            <form @submit.prevent="saveUnit" class="space-y-5 p-6">
                <div>
                    <label class="mb-1.5 block text-[13px] font-black uppercase tracking-widest text-gray-500">Unit Name <span class="text-red-500">*</span></label>
                    <input x-model="form.name" type="text" placeholder="e.g. Kilogram"
                        class="h-11 w-full rounded-xl border border-gray-300 px-4 text-sm font-bold text-gray-900 outline-none transition focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50" />
                </div>
                <div>
                    <label class="mb-1.5 block text-[13px] font-black uppercase tracking-widest text-gray-500">Short Name <span class="text-red-500">*</span></label>
                    <input x-model="form.shortName" type="text" placeholder="e.g. kg"
                        class="h-11 w-full rounded-xl border border-gray-300 px-4 text-sm font-bold text-gray-900 outline-none transition focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50" />
                </div>
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
                        <span x-text="saving ? 'Saving…' : (editingId ? 'Update' : 'Save Unit')"></span>
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
function unitsApp(token) {
    return {
        units: [],
        search: '',
        showForm: false,
        editingId: null,
        saving: false,
        error: null,
        toast: null,
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
            const res = await fetch(`${window.API_BASE}/api/admin/units/${unit.id}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
                body: JSON.stringify({ isActive: !unit.isActive }),
            });
            if (res.ok) unit.isActive = !unit.isActive;
        },

        async deleteUnit(id) {
            if (!confirm('Delete this unit?')) return;
            const res = await fetch(`${window.API_BASE}/api/admin/units/${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': 'Bearer ' + token },
            });
            if (res.ok) {
                this.units = this.units.filter(u => u.id !== id);
                this.showToast('Unit deleted.');
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
