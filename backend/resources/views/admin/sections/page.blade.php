@extends('admin.layout')

@section('title', $title)

@section('content')
@php
    $cards = collect($cards ?? []);
    $items = collect($items ?? []);
    $sideItems = collect($sideItems ?? []);

    $badgeClasses = static function (?string $tone): string {
        return match ($tone) {
            'green' => 'border-green-200 bg-green-50 text-green-700',
            'amber' => 'border-amber-200 bg-amber-50 text-amber-700',
            'red' => 'border-red-200 bg-red-50 text-red-700',
            'blue' => 'border-blue-200 bg-blue-50 text-blue-700',
            default => 'border-slate-200 bg-slate-50 text-slate-700',
        };
    };
@endphp

<div class="-mx-4 -mt-4 min-h-screen bg-[#f7f7f8] px-4 pt-6 pb-16 sm:-mx-6 sm:px-6 sm:pt-8 xl:-mx-10 xl:px-10">
    <div class="mx-auto max-w-[1320px]">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="flex items-start gap-3">
                <span class="mt-[10px] h-2.5 w-8 shrink-0 rounded-full bg-[#114f8f]"></span>
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-[#114f8f]">{{ $eyebrow }}</p>
                    <h1 class="mt-2 text-[30px] font-black tracking-tight text-gray-900">{{ $title }}</h1>
                    <p class="mt-2 max-w-[820px] text-sm text-gray-500">{{ $description }}</p>
                </div>
            </div>
            @if(!empty($actionLabel) && !empty($actionHref))
                <a href="{{ $actionHref }}" class="inline-flex h-11 items-center justify-center rounded-2xl bg-[#111827] px-5 text-[14px] font-black tracking-wide text-white transition hover:bg-black">
                    {{ $actionLabel }}
                </a>
            @endif
        </div>

        @if($cards->isNotEmpty())
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach($cards as $card)
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">{{ $card['label'] }}</div>
                        <div class="mt-3 text-[32px] font-black tracking-tight text-gray-900">{{ $card['value'] }}</div>
                        <p class="mt-3 text-sm text-gray-500">{{ $card['note'] }}</p>
                    </div>
                @endforeach
            </section>
        @endif

        <section class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_320px] xl:items-start">
            <div class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-5 sm:px-8">
                    <h2 class="text-[20px] font-black text-gray-900">{{ $listTitle }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ $listDescription }}</p>
                </div>

                @if($items->isEmpty())
                    <div class="px-6 py-16 text-center sm:px-8">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[#eff6ff] text-[#114f8f]">
                            <i data-lucide="layout-grid" class="h-7 w-7"></i>
                        </div>
                        <p class="mt-5 text-[16px] font-semibold text-gray-800">{{ $emptyTitle }}</p>
                        <p class="mt-2 text-sm text-gray-500">{{ $emptyBody }}</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($items as $item)
                            @php $isLink = !empty($item['href']); @endphp
                            @if($isLink)
                                <a href="{{ $item['href'] }}" class="block px-6 py-5 transition hover:bg-gray-50 sm:px-8">
                            @else
                                <div class="px-6 py-5 sm:px-8">
                            @endif
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <div class="text-[15px] font-bold text-gray-900">{{ $item['title'] }}</div>
                                        @if(!empty($item['meta']))
                                            <div class="mt-1 text-[12px] font-medium text-gray-500">{{ $item['meta'] }}</div>
                                        @endif
                                        @if(!empty($item['description']))
                                            <p class="mt-2 text-sm text-gray-600">{{ $item['description'] }}</p>
                                        @endif
                                    </div>
                                    <div class="shrink-0 sm:text-right">
                                        @if(!empty($item['value']))
                                            <div class="text-[14px] font-bold text-gray-900">{{ $item['value'] }}</div>
                                        @endif
                                        @if(!empty($item['badge']['label']))
                                            <span class="mt-2 inline-flex rounded-full border px-2.5 py-1 text-[11px] font-black uppercase tracking-[0.16em] {{ $badgeClasses($item['badge']['tone'] ?? null) }}">
                                                {{ $item['badge']['label'] }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @if($isLink)
                                </a>
                            @else
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            <aside class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="text-[14px] font-black uppercase tracking-[0.2em] text-gray-500">{{ $sideTitle }}</h3>
                <p class="mt-4 text-sm text-gray-600">{{ $sideDescription }}</p>
                @if($sideItems->isNotEmpty())
                    <div class="mt-5 space-y-4 text-sm text-gray-600">
                        @foreach($sideItems as $note)
                            <div class="flex items-start gap-3">
                                <span class="mt-1 h-2.5 w-2.5 rounded-full bg-[#114f8f]"></span>
                                <p>{{ $note }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </aside>
        </section>
    </div>
</div>
@endsection
