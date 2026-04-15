@extends('admin.layout')

@section('title', 'Bulk Export')

@section('content')
<div class="-mx-4 -mt-4 sm:-mx-6 sm:-mt-6 min-h-screen bg-[#eaeded] px-4 pt-6 pb-16 sm:px-6 sm:pt-8 lg:px-8"
     x-data="exportApp('{{ session('admin_token') }}')"
     x-init="init()">

    {{-- Header --}}
    <div class="mb-5">
        <h1 class="text-[22px] font-bold text-[#0f1111]">Bulk Export</h1>
        <p class="mt-0.5 text-[13px] text-[#565959]">Download a complete JSON backup of your entire product catalog.</p>
    </div>

    {{-- Card --}}
    <div class="rounded-lg border border-[#d5d9d9] bg-white p-8 shadow-[0_1px_3px_rgba(15,17,17,0.08)]">
        <div class="mx-auto max-w-lg text-center">

            <div class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-lg border border-[#d5d9d9] bg-[#f0f2f2] text-[#565959]">
                <i data-lucide="download" class="h-8 w-8"></i>
            </div>

            <h2 class="text-[20px] font-bold text-[#0f1111]">Download Catalog Backup</h2>
            <p class="mt-3 text-[14px] leading-relaxed text-[#565959]">
                Generates a full high-fidelity JSON export of all products, including variants, categories, media URLs, and technical specifications.
            </p>

            <button @click="doExport()" :disabled="exporting" type="button"
                class="mt-8 inline-flex h-11 items-center gap-2 rounded-lg border border-[#fcd200] bg-[#ffd814] px-8 text-[14px] font-medium text-[#0f1111] shadow-[inset_0_-1px_0_rgba(0,0,0,0.15)] transition hover:bg-[#f7ca00] disabled:opacity-50 active:scale-[.99]">
                <template x-if="exporting">
                    <i data-lucide="loader-2" class="h-4 w-4 animate-spin"></i>
                </template>
                <template x-if="!exporting">
                    <i data-lucide="download" class="h-4 w-4"></i>
                </template>
                <span x-text="exporting ? 'Compiling Dataset…' : 'Generate Export File'"></span>
            </button>

            <div class="mt-8 flex items-center justify-center gap-6">
                <span class="flex items-center gap-1.5 text-[12px] font-bold text-[#565959]">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#007185]"></span>Full Metadata
                </span>
                <span class="flex items-center gap-1.5 text-[12px] font-bold text-[#565959]">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#007185]"></span>Media URLs
                </span>
                <span class="flex items-center gap-1.5 text-[12px] font-bold text-[#565959]">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#007185]"></span>Inventory
                </span>
            </div>

            <div x-show="error"
                class="mt-6 rounded-md border border-[#f5c6cb] bg-[#fef2f2] px-4 py-3 text-[13px] font-medium text-[#b12704]"
                x-text="error">
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
                const res = await fetch('{{ url('/api/admin/products/export') }}', {
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
