@extends('admin.layout')

@section('title', 'Bulk Export')

@section('content')
<div class="-mx-4 -mt-4 sm:-mx-6 sm:-mt-6 min-h-screen bg-[#f7f7f8] px-4 pt-6 pb-16 sm:px-6 sm:pt-8 lg:px-8"
     x-data="exportApp('{{ session('admin_token') }}')"
     x-init="init()">

    <div class="mx-auto max-w-[860px]">

        {{-- Header --}}
        <div class="mb-8 flex items-start gap-3">
            <span class="mt-[10px] h-2.5 w-8 rounded-full bg-[#f6c400] flex-shrink-0"></span>
            <div>
                <h1 class="text-[28px] font-black uppercase tracking-tight text-gray-900">Bulk Export</h1>
                <p class="mt-1 text-sm text-gray-500">Download a complete JSON backup of your entire product catalog.</p>
            </div>
        </div>

        {{-- Card --}}
        <div class="rounded-3xl border border-gray-200 bg-white p-10 shadow-sm">
            <div class="mx-auto max-w-lg text-center">
                <div class="mb-8 inline-flex h-20 w-20 items-center justify-center rounded-3xl bg-blue-50 text-[#114f8f]">
                    <i data-lucide="download" class="h-10 w-10"></i>
                </div>
                <h2 class="text-2xl font-black text-gray-900">Download Catalog Backup</h2>
                <p class="mt-4 font-medium text-gray-500">
                    Generates a full high-fidelity JSON export of all products, including variants, categories, media URLs, and technical specifications.
                </p>

                <button @click="doExport()" :disabled="exporting" type="button"
                    class="mt-10 inline-flex h-14 items-center gap-3 rounded-2xl bg-[#111827] px-10 text-[15px] font-black uppercase tracking-wide text-white transition hover:bg-black disabled:opacity-50 active:scale-95">
                    <template x-if="exporting">
                        <i data-lucide="loader-2" class="h-5 w-5 animate-spin"></i>
                    </template>
                    <template x-if="!exporting">
                        <i data-lucide="download" class="h-5 w-5"></i>
                    </template>
                    <span x-text="exporting ? 'Compiling Dataset…' : 'Generate Export File'"></span>
                </button>

                <div class="mt-8 flex items-center justify-center gap-6 text-[11px] font-black uppercase tracking-widest text-gray-400">
                    <span class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Full Metadata</span>
                    <span class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Media URLs</span>
                    <span class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Inventory</span>
                </div>

                <template x-if="error">
                    <p class="mt-6 rounded-xl bg-red-50 px-4 py-3 text-sm font-bold text-red-600" x-text="error"></p>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function exportApp(token) {
    return {
        exporting: false,
        error: null,

        init() {
            this.$nextTick(() => lucide.createIcons());
        },

        async doExport() {
            this.exporting = true;
            this.error = null;
            try {
                const res = await fetch('/api/admin/products/export', {
                    headers: { 'Authorization': 'Bearer ' + token },
                });
                if (!res.ok) throw new Error('Export request failed.');
                const data = await res.json();
                const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'products-export-' + new Date().toISOString().split('T')[0] + '.json';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            } catch (e) {
                this.error = e.message;
            } finally {
                this.exporting = false;
                this.$nextTick(() => lucide.createIcons());
            }
        },
    };
}
</script>
@endpush
