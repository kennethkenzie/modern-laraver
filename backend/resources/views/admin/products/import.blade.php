@extends('admin.layout')

@section('title', 'Bulk Import')

@section('content')
<div class="-mx-4 -mt-4 sm:-mx-6 sm:-mt-6 min-h-screen bg-[#eaeded] px-4 pt-6 pb-16 sm:px-6 sm:pt-8 lg:px-8"
     x-data="importApp('{{ session('admin_token') }}')"
     x-init="init()">

    {{-- Header --}}
    <div class="mb-5">
        <h1 class="text-[22px] font-bold text-[#0f1111]">Bulk Import</h1>
        <p class="mt-0.5 text-[13px] text-[#565959]">Upload a JSON or CSV file to synchronise your product catalog.</p>
    </div>

    {{-- Main card --}}
    <div class="rounded-lg border bg-white p-8 shadow-[0_1px_3px_rgba(15,17,17,0.08)]"
         :class="processing ? 'border-[#007185] ring-2 ring-[rgba(0,113,133,0.12)]' : 'border-[#d5d9d9]'">

        {{-- Result state --}}
        <template x-if="result">
            <div class="text-center">
                <div class="mx-auto mb-6 flex h-14 w-14 items-center justify-center rounded-lg border border-[#c3e6cb] bg-[#d4edda] text-[#155724]">
                    <i data-lucide="check-circle-2" class="h-7 w-7"></i>
                </div>
                <h2 class="text-[20px] font-bold text-[#0f1111]" x-text="result.message"></h2>
                <div class="mt-6 grid grid-cols-3 gap-4">
                    <div class="rounded-md border border-[#d5d9d9] bg-[#f7fafa] p-5 text-center">
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-[#565959]">Processed</span>
                        <span class="mt-2 block text-[26px] font-bold text-[#0f1111]" x-text="result.created + result.updated"></span>
                    </div>
                    <div class="rounded-md border border-[#d5d9d9] bg-[#f7fafa] p-5 text-center">
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-[#565959]">Errors</span>
                        <span class="mt-2 block text-[26px] font-bold text-[#b12704]" x-text="result.failed"></span>
                    </div>
                    <div class="rounded-md border border-[#d5d9d9] bg-[#f7fafa] p-5 text-center">
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-[#565959]">Success</span>
                        <span class="mt-2 block text-[26px] font-bold text-[#007185]"
                            x-text="result.failed === 0 ? '100%' : Math.round(((result.created + result.updated) / (result.created + result.updated + result.failed)) * 100) + '%'"></span>
                    </div>
                </div>
                <template x-if="result.errors && result.errors.length > 0">
                    <div class="mt-6 rounded-md border border-[#f5c6cb] bg-[#fef2f2] p-5 text-left">
                        <h3 class="mb-3 text-[11px] font-bold uppercase tracking-wider text-[#b12704]">Error Log</h3>
                        <ul class="max-h-40 space-y-1.5 overflow-y-auto text-[13px] text-[#b12704]">
                            <template x-for="(e, i) in result.errors" :key="i">
                                <li class="flex gap-2"><span class="opacity-40">•</span><span x-text="e"></span></li>
                            </template>
                        </ul>
                    </div>
                </template>
                <button @click="result = null"
                    class="mt-8 inline-flex h-10 items-center gap-2 rounded-lg border border-[#fcd200] bg-[#ffd814] px-8 text-[14px] font-medium text-[#0f1111] shadow-[inset_0_-1px_0_rgba(0,0,0,0.15)] transition hover:bg-[#f7ca00]">
                    Import Another File
                </button>
            </div>
        </template>

        {{-- Upload state --}}
        <template x-if="!result">
            <div class="mx-auto max-w-lg text-center">
                <div class="mb-6 inline-flex h-14 w-14 items-center justify-center rounded-lg border border-[#d5d9d9] bg-[#f0f2f2] text-[#565959]">
                    <template x-if="processing">
                        <i data-lucide="loader-2" class="h-7 w-7 animate-spin"></i>
                    </template>
                    <template x-if="!processing">
                        <i data-lucide="upload" class="h-7 w-7"></i>
                    </template>
                </div>
                <h2 class="text-[20px] font-bold text-[#0f1111]">Upload Product Data</h2>
                <p class="mt-3 text-[14px] leading-relaxed text-[#565959]">
                    Accepts <strong class="text-[#0f1111]">.json</strong> or <strong class="text-[#0f1111]">.csv</strong> files. JSON imports preserve all variants, media, and specs. CSV uses a flat schema.
                </p>

                <input type="file" id="importFile" accept=".json,.csv" class="hidden" @change="handleFile($event)" :disabled="processing" />

                <div class="mt-8">
                    <button @click="document.getElementById('importFile').click()" type="button"
                        :disabled="processing"
                        class="inline-flex h-11 items-center gap-2 rounded-lg border border-[#fcd200] bg-[#ffd814] px-8 text-[14px] font-medium text-[#0f1111] shadow-[inset_0_-1px_0_rgba(0,0,0,0.15)] transition hover:bg-[#f7ca00] disabled:opacity-50 active:scale-[.99]">
                        <template x-if="processing">
                            <i data-lucide="loader-2" class="h-4 w-4 animate-spin"></i>
                        </template>
                        <template x-if="!processing">
                            <i data-lucide="upload" class="h-4 w-4"></i>
                        </template>
                        <span x-text="processing ? 'Importing…' : 'Choose File (JSON / CSV)'"></span>
                    </button>
                </div>

                <div class="mt-8 rounded-md border border-[#d5d9d9] bg-[#f7fafa] p-4 text-left">
                    <div class="flex items-start gap-3">
                        <i data-lucide="info" class="mt-0.5 h-4 w-4 shrink-0 text-[#565959]"></i>
                        <div>
                            <p class="text-[12px] font-bold text-[#0f1111]">CSV Schema</p>
                            <p class="mt-0.5 text-[12px] text-[#565959]">
                                Name, Slug, Brand, ListPrice, SalePrice, Category, SKU, Stock, ImageURL
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
@endsection

@push('scripts')
<script>
function importApp(token) {
    return {
        processing: false,
        result: null,

        init() {
            this.$nextTick(() => lucide.createIcons());
        },

        async handleFile(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.processing = true;
            this.result = null;
            try {
                const text = await file.text();
                let data;
                if (file.name.endsWith('.csv')) {
                    data = this.csvToJson(text);
                } else {
                    data = JSON.parse(text);
                }
                const res = await fetch('{{ url('/api/admin/products/import') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
                    body: JSON.stringify(data),
                });
                if (!res.ok) throw new Error('Import request failed.');
                this.result = await res.json();
            } catch (err) {
                alert('Import failed: ' + err.message);
            } finally {
                this.processing = false;
                e.target.value = '';
                this.$nextTick(() => lucide.createIcons());
            }
        },

        csvToJson(csv) {
            const lines = csv.split('\n').filter(l => l.trim());
            const headers = lines[0].split(',').map(h => h.trim().toLowerCase());
            return lines.slice(1).map(line => {
                const vals = line.split(',').map(v => v.trim());
                const obj = {};
                headers.forEach((h, i) => obj[h] = vals[i]);
                return {
                    name: obj.name,
                    slug: obj.slug || obj.name?.toLowerCase().replace(/ /g, '-'),
                    brand: obj.brand,
                    listPrice: parseFloat(obj.listprice) || 0,
                    salePrice: parseFloat(obj.saleprice) || 0,
                    category: obj.category ? { name: obj.category } : null,
                    variants: [{ sku: obj.sku, stockQty: parseInt(obj.stock) || 0, price: parseFloat(obj.saleprice) || 0, isDefault: true }],
                    media: obj.imageurl ? [{ url: obj.imageurl, isPrimary: true }] : [],
                };
            });
        },
    };
}
</script>
@endpush
