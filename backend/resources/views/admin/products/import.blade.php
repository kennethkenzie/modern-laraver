@extends('admin.layout')

@section('title', 'Bulk Import')

@section('content')
<div class="-mx-4 -mt-4 sm:-mx-6 sm:-mt-6 min-h-screen bg-[#f7f7f8] px-4 pt-6 pb-16 sm:px-6 sm:pt-8 lg:px-8"
     x-data="importApp('{{ session('admin_token') }}')"
     x-init="init()">

    <div class="mx-auto max-w-[860px]">

        {{-- Header --}}
        <div class="mb-8 flex items-start gap-3">
            <span class="mt-[10px] h-2.5 w-8 rounded-full bg-[#f6c400] flex-shrink-0"></span>
            <div>
                <h1 class="text-[28px] font-black uppercase tracking-tight text-gray-900">Bulk Import</h1>
                <p class="mt-1 text-sm text-gray-500">Upload a JSON or CSV file to synchronise your product catalog.</p>
            </div>
        </div>

        {{-- Main card --}}
        <div class="rounded-3xl border border-gray-200 bg-white p-10 shadow-sm" :class="processing ? 'ring-4 ring-blue-50 border-[#114f8f]' : ''">

            {{-- Result state --}}
            <template x-if="result">
                <div class="text-center">
                    <div class="mx-auto mb-8 flex h-20 w-20 items-center justify-center rounded-3xl bg-green-50 text-green-600">
                        <i data-lucide="check-circle-2" class="h-10 w-10"></i>
                    </div>
                    <h2 class="text-2xl font-black uppercase text-gray-900" x-text="result.message"></h2>
                    <div class="mt-8 grid grid-cols-3 gap-6">
                        <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-6 text-center">
                            <span class="block text-[11px] font-black uppercase tracking-widest text-gray-400">Processed</span>
                            <span class="text-2xl font-black text-gray-900" x-text="result.created + result.updated"></span>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-6 text-center">
                            <span class="block text-[11px] font-black uppercase tracking-widest text-gray-400">Errors</span>
                            <span class="text-2xl font-black text-red-500" x-text="result.failed"></span>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-6 text-center text-[#114f8f]">
                            <span class="block text-[11px] font-black uppercase tracking-widest text-gray-400 opacity-60">Success</span>
                            <span class="text-2xl font-black" x-text="result.failed === 0 ? '100%' : Math.round(((result.created + result.updated) / (result.created + result.updated + result.failed)) * 100) + '%'"></span>
                        </div>
                    </div>
                    <template x-if="result.errors && result.errors.length > 0">
                        <div class="mt-8 rounded-xl border border-red-100 bg-red-50/50 p-6 text-left">
                            <h3 class="mb-4 text-xs font-black uppercase tracking-widest text-red-600">Error Log</h3>
                            <ul class="max-h-40 space-y-2 overflow-y-auto text-sm text-red-700">
                                <template x-for="(e, i) in result.errors" :key="i">
                                    <li class="flex gap-2"><span class="font-black opacity-40">•</span><span x-text="e"></span></li>
                                </template>
                            </ul>
                        </div>
                    </template>
                    <button @click="result = null"
                        class="mt-10 inline-flex h-14 items-center gap-3 rounded-2xl bg-[#111827] px-10 text-[15px] font-black uppercase tracking-wide text-white transition hover:bg-black active:scale-95">
                        Import Another File
                    </button>
                </div>
            </template>

            {{-- Upload state --}}
            <template x-if="!result">
                <div class="mx-auto max-w-lg text-center">
                    <div class="mb-8 inline-flex h-20 w-20 items-center justify-center rounded-3xl bg-[#114f8f]/5 text-[#114f8f]">
                        <template x-if="processing">
                            <i data-lucide="loader-2" class="h-10 w-10 animate-spin"></i>
                        </template>
                        <template x-if="!processing">
                            <i data-lucide="upload" class="h-10 w-10"></i>
                        </template>
                    </div>
                    <h2 class="text-2xl font-black text-gray-900">Upload Product Data</h2>
                    <p class="mt-4 font-medium text-gray-500">
                        Accepts <strong>.json</strong> or <strong>.csv</strong> files. JSON imports preserve all variants, media, and specs. CSV uses a flat schema.
                    </p>

                    <input type="file" id="importFile" accept=".json,.csv" class="hidden" @change="handleFile($event)" :disabled="processing" />

                    <div class="mt-10">
                        <button @click="$el.previousElementSibling.previousElementSibling.click()" type="button"
                            :disabled="processing"
                            class="inline-flex h-14 items-center gap-3 rounded-2xl bg-[#114f8f] px-10 text-[15px] font-black uppercase tracking-wide text-white shadow-xl shadow-blue-900/10 transition hover:bg-[#0d3f74] disabled:opacity-50 active:scale-95">
                            <template x-if="processing">
                                <i data-lucide="loader-2" class="h-5 w-5 animate-spin"></i>
                            </template>
                            <template x-if="!processing">
                                <i data-lucide="upload" class="h-5 w-5"></i>
                            </template>
                            <span x-text="processing ? 'Importing…' : 'Choose File (JSON / CSV)'"></span>
                        </button>
                    </div>

                    <div class="mt-10 rounded-xl border border-gray-100 bg-gray-50/50 p-5 text-left">
                        <div class="flex items-start gap-3">
                            <i data-lucide="alert-triangle" class="mt-0.5 h-5 w-5 text-gray-400"></i>
                            <div>
                                <p class="text-[12px] font-black uppercase tracking-widest text-gray-700">CSV Schema</p>
                                <p class="mt-1 text-[11px] font-medium text-gray-500">
                                    Name, Slug, Brand, ListPrice, SalePrice, Category, SKU, Stock, ImageURL
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
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
