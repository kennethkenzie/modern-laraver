@extends('admin.layout')

@section('title', $title)

@section('content')
<div class="-mx-4 -mt-4 min-h-screen bg-[#f7f7f8] px-4 pt-6 pb-16 sm:-mx-6 sm:px-6 sm:pt-8 xl:-mx-10 xl:px-10">
    <div class="mx-auto max-w-[1280px]">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-3">
                <span class="mt-[10px] h-2.5 w-8 shrink-0 rounded-full bg-[#114f8f]"></span>
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-[#114f8f]">{{ $eyebrow }}</p>
                    <h1 class="mt-2 text-[30px] font-black tracking-tight text-gray-900">{{ $title }}</h1>
                    <p class="mt-2 text-sm text-gray-500">{{ $description }}</p>
                </div>
            </div>
            <button type="button" class="inline-flex h-12 items-center gap-2 rounded-2xl bg-[#111827] px-5 text-[14px] font-black uppercase tracking-wide text-white transition hover:bg-black">
                <i data-lucide="plus" class="h-4 w-4"></i>
                {{ $primaryAction }}
            </button>
        </div>

        <section class="grid gap-4 md:grid-cols-3">
            @foreach($cards as $card)
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">{{ $card['label'] }}</div>
                    <div class="mt-3 text-[32px] font-black tracking-tight text-gray-900">{{ $card['value'] }}</div>
                    <p class="mt-3 text-sm text-gray-500">{{ $card['note'] }}</p>
                </div>
            @endforeach
        </section>

        <section class="mt-6 rounded-[28px] border border-gray-200 bg-white p-8 shadow-sm">
            <div class="grid gap-8 xl:grid-cols-[minmax(0,1.2fr)_320px] xl:items-start">
                <div>
                    <div class="mb-5 flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#eff6ff] text-[#114f8f]">
                            <i data-lucide="truck" class="h-6 w-6"></i>
                        </div>
                        <div>
                            <h2 class="text-[20px] font-black text-gray-900">{{ $emptyTitle }}</h2>
                            <p class="mt-1 text-sm text-gray-500">{{ $emptyBody }}</p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-dashed border-gray-200 bg-[#fafafa] px-6 py-12 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-gray-300 shadow-sm">
                            <i data-lucide="map-pinned" class="h-8 w-8"></i>
                        </div>
                        <p class="mt-5 text-[15px] font-semibold text-gray-700">No records available</p>
                        <p class="mt-2 text-sm text-gray-500">This shipping section is linked and ready, but there are no configured records to display yet.</p>
                    </div>
                </div>

                <aside class="rounded-2xl border border-gray-200 bg-[#fcfcfd] p-5">
                    <h3 class="text-[14px] font-black uppercase tracking-[0.2em] text-gray-500">Next Steps</h3>
                    <div class="mt-5 space-y-4 text-sm text-gray-600">
                        <div class="flex items-start gap-3">
                            <span class="mt-1 h-2.5 w-2.5 rounded-full bg-[#114f8f]"></span>
                            <p>Define the underlying database structure or config source for this shipping section.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="mt-1 h-2.5 w-2.5 rounded-full bg-[#114f8f]"></span>
                            <p>Expose create, edit, and delete actions once the data model is ready.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="mt-1 h-2.5 w-2.5 rounded-full bg-[#114f8f]"></span>
                            <p>Connect checkout and fulfillment flows so the storefront can use these shipping records.</p>
                        </div>
                    </div>
                </aside>
            </div>
        </section>
    </div>
</div>
@endsection
