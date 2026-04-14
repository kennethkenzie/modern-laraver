@extends('admin.layout')

@section('title', $product->name)

@section('content')
<div class="-mx-4 -mt-4 sm:-mx-6 sm:-mt-6 min-h-screen bg-[#f7f7f8] px-4 pt-6 pb-16 sm:px-6 sm:pt-8 lg:px-8">
    <div class="mx-auto max-w-[1200px] space-y-6">

        {{-- Page header --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#0b63ce]">Product View</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-900">{{ $product->name }}</h1>
                <p class="mt-2 text-sm text-gray-500">{{ $product->slug }}</p>
            </div>
            <div class="flex items-center gap-3 flex-shrink-0">
                <a href="{{ route('dashboard.products') }}"
                    class="inline-flex h-11 items-center justify-center rounded-md bg-[#1f2937] px-5 text-sm font-bold text-white transition hover:bg-black">
                    Back to List
                </a>
            </div>
        </div>

        {{-- Main 2-col grid --}}
        <div class="grid gap-6 lg:grid-cols-[360px_minmax(0,1fr)]">

            {{-- LEFT: Media --}}
            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Media</h2>
                <div class="mt-5 space-y-4">
                    @if($product->media->isEmpty())
                        <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-4 py-10 text-center text-sm text-gray-400">
                            No product media
                        </div>
                    @else
                        @foreach($product->media as $item)
                            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-gray-50">
                                @if($item->kind === 'image')
                                    <img
                                        src="{{ $item->url }}"
                                        alt="{{ $item->alt_text ?? $product->name }}"
                                        class="h-48 w-full object-contain bg-white p-3"
                                        loading="lazy"
                                    />
                                @else
                                    <video
                                        src="{{ $item->url }}"
                                        class="h-48 w-full object-cover bg-black"
                                        controls
                                    ></video>
                                @endif
                                @if($item->alt_text)
                                    <p class="border-t border-gray-100 px-4 py-2 text-xs text-gray-500 truncate">
                                        {{ $item->alt_text }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </section>

            {{-- RIGHT: Detail panels --}}
            <div class="space-y-6">

                {{-- Overview --}}
                <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">Overview</h2>
                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        <div class="rounded-2xl bg-gray-50 px-4 py-3">
                            <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Category</div>
                            <div class="mt-1 text-sm font-medium text-gray-800">{{ $product->category?->name ?? 'Uncategorized' }}</div>
                        </div>
                        <div class="rounded-2xl bg-gray-50 px-4 py-3">
                            <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Brand</div>
                            <div class="mt-1 text-sm font-medium text-gray-800">{{ $product->brand ?? 'Not set' }}</div>
                        </div>
                        <div class="rounded-2xl bg-gray-50 px-4 py-3">
                            <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Currency</div>
                            <div class="mt-1 text-sm font-medium text-gray-800">{{ $product->currency_code ?? 'UGX' }}</div>
                        </div>
                        <div class="rounded-2xl bg-gray-50 px-4 py-3">
                            <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Status</div>
                            <div class="mt-1">
                                @if($product->is_published)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-2.5 py-1 text-xs font-bold text-green-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span> Published
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-600">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span> Draft
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="rounded-2xl bg-gray-50 px-4 py-3">
                            <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Featured</div>
                            <div class="mt-1 text-sm font-medium text-gray-800">{{ $product->is_featured_home ? 'Yes' : 'No' }}</div>
                        </div>
                        <div class="rounded-2xl bg-gray-50 px-4 py-3">
                            <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Created</div>
                            <div class="mt-1 text-sm font-medium text-gray-800">{{ $product->created_at->format('M j, Y g:i A') }}</div>
                        </div>
                    </div>
                </section>

                {{-- Description --}}
                <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">Description</h2>
                    @if($product->short_description)
                        <div class="mt-4 text-sm text-gray-700">{!! $product->short_description !!}</div>
                    @else
                        <p class="mt-4 text-sm text-gray-400">No short description.</p>
                    @endif
                    <div class="mt-4 rounded-2xl bg-gray-50 p-4 text-sm text-gray-700">
                        @if($product->description)
                            {!! $product->description !!}
                        @else
                            <span class="text-gray-400">No full description.</span>
                        @endif
                    </div>
                </section>

                {{-- Variants --}}
                <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">Variants</h2>
                    @if($product->variants->isEmpty())
                        <p class="mt-4 text-sm text-gray-400">No variants found.</p>
                    @else
                        <div class="mt-4 space-y-3">
                            @foreach($product->variants as $variant)
                                <div class="grid gap-3 rounded-2xl border border-gray-200 bg-gray-50 p-4 md:grid-cols-4">
                                    <div class="rounded-xl bg-white px-3 py-2.5">
                                        <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Variant</div>
                                        <div class="mt-1 text-sm font-medium text-gray-800">{{ $variant->option_value ?? '—' }}</div>
                                    </div>
                                    <div class="rounded-xl bg-white px-3 py-2.5">
                                        <div class="text-xs font-bold uppercase tracking-wider text-gray-400">SKU</div>
                                        <div class="mt-1 text-sm font-medium text-gray-800">{{ $variant->sku ?? 'Not set' }}</div>
                                    </div>
                                    <div class="rounded-xl bg-white px-3 py-2.5">
                                        <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Price</div>
                                        <div class="mt-1 text-sm font-medium text-gray-800">
                                            {{ $product->currency_code ?? 'UGX' }} {{ number_format((float)$variant->price) }}
                                        </div>
                                    </div>
                                    <div class="rounded-xl bg-white px-3 py-2.5">
                                        <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Stock</div>
                                        <div class="mt-1 text-sm font-medium text-gray-800">{{ (int)($variant->stock_qty ?? 0) }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                {{-- Specifications --}}
                <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">Specifications</h2>
                    @if($product->specs->isEmpty())
                        <p class="mt-4 text-sm text-gray-400">No specifications added.</p>
                    @else
                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            @foreach($product->specs as $spec)
                                <div class="rounded-2xl bg-gray-50 px-4 py-3">
                                    <div class="text-xs font-bold uppercase tracking-wider text-gray-400">{{ $spec->spec_name }}</div>
                                    <div class="mt-1 text-sm font-medium text-gray-800">{{ $spec->spec_value }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                {{-- Bullets --}}
                <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900">Bullets</h2>
                    @if($product->bullets->isEmpty())
                        <p class="mt-4 text-sm text-gray-400">No bullet points added.</p>
                    @else
                        <ul class="mt-4 list-disc space-y-2 pl-5 text-sm text-gray-700">
                            @foreach($product->bullets as $bullet)
                                <li>{{ $bullet->bullet_text }}</li>
                            @endforeach
                        </ul>
                    @endif
                </section>

            </div>
        </div>

        {{-- Danger zone --}}
        <section class="rounded-3xl border border-red-100 bg-white p-6 shadow-sm">
            <h2 class="text-base font-bold text-red-600">Danger Zone</h2>
            <p class="mt-1 text-sm text-gray-500">These actions are irreversible. Proceed with caution.</p>
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <button
                    type="button"
                    onclick="deleteProduct('{{ $product->id }}', '{{ addslashes($product->name) }}')"
                    class="inline-flex h-10 items-center gap-2 rounded-md border border-red-200 bg-red-50 px-5 text-sm font-bold text-red-600 transition hover:bg-red-100"
                >
                    <i data-lucide="trash-2" class="h-4 w-4"></i> Delete Product
                </button>
            </div>
        </section>

    </div>
</div>
@endsection

@push('scripts')
<script>
const _dashToken = '{{ session('admin_token') }}';

async function deleteProduct(id, name) {
    if (!confirm('Delete "' + name + '"? This cannot be undone.')) return;
    try {
        const res = await fetch(window.API_BASE + '/api/admin/products/' + id, {
            method: 'DELETE',
            headers: { 'Authorization': 'Bearer ' + _dashToken },
        });
        if (!res.ok) {
            const data = await res.json().catch(() => ({}));
            throw new Error(data.error || 'Delete failed.');
        }
        window.location.href = '{{ route('dashboard.products') }}';
    } catch (err) {
        alert(err.message || 'Delete failed.');
    }
}
</script>
@endpush
