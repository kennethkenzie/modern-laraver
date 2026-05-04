@extends('admin.layout')

@section('title', 'Orders')

@section('content')
@php
    $statusClasses = static function (?string $status): string {
        return match ($status) {
            'delivered' => 'border-green-200 bg-green-50 text-green-700',
            'cancelled' => 'border-red-200 bg-red-50 text-red-700',
            'processing', 'ready', 'shipped' => 'border-blue-200 bg-blue-50 text-blue-700',
            default => 'border-amber-200 bg-amber-50 text-amber-700',
        };
    };
@endphp

<div class="-mx-4 -mt-4 min-h-screen bg-[#f7f7f8] px-4 pt-6 pb-16 sm:-mx-6 sm:px-6 sm:pt-8 xl:-mx-10 xl:px-10">
    <div class="mx-auto max-w-[1320px] space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="flex items-start gap-3">
                <span class="mt-[10px] h-2.5 w-8 shrink-0 rounded-full bg-[#114f8f]"></span>
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-[#114f8f]">Commerce Operations</p>
                    <h1 class="mt-2 text-[30px] font-black tracking-tight text-gray-900">Orders</h1>
                    <p class="mt-2 max-w-[840px] text-sm text-gray-500">Live storefront orders created from checkout. Review customer details, payment method, fulfillment route, and line items.</p>
                </div>
            </div>
            <a href="{{ route('dashboard.customers') }}" class="inline-flex h-11 items-center justify-center rounded-2xl bg-[#111827] px-5 text-[14px] font-black tracking-wide text-white transition hover:bg-black">
                Customers: {{ number_format($customerCount) }}
            </a>
        </div>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach($cards as $card)
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">{{ $card['label'] }}</div>
                    <div class="mt-3 text-[32px] font-black tracking-tight text-gray-900">{{ $card['value'] }}</div>
                    <p class="mt-3 text-sm text-gray-500">{{ $card['note'] }}</p>
                </div>
            @endforeach
        </section>

        <section class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-6 py-5 sm:px-8">
                <h2 class="text-[20px] font-black text-gray-900">Recent orders</h2>
                <p class="mt-1 text-sm text-gray-500">Newest {{ $orders->count() }} orders placed from the storefront.</p>
            </div>

            @forelse($orders as $order)
                <a href="{{ route('dashboard.orders.show', $order->id) }}" class="grid gap-4 border-b border-gray-100 px-6 py-5 transition last:border-b-0 hover:bg-gray-50 sm:px-8 xl:grid-cols-[150px_minmax(0,1fr)_180px_150px_120px]">
                    <div>
                        <div class="text-[13px] font-black text-gray-900">{{ $order->order_number }}</div>
                        <div class="mt-1 text-[12px] text-gray-500">{{ $order->created_at->format('M j, Y g:i A') }}</div>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[14px] font-bold text-gray-900">{{ $order->customer_name }}</div>
                        <div class="mt-1 text-[13px] text-gray-500">{{ $order->customer_phone ?: $order->customer_email ?: 'No contact saved' }}</div>
                        <div class="mt-2 text-[13px] text-gray-600">{{ $order->items->pluck('name')->take(2)->join(', ') }}{{ $order->items->count() > 2 ? ' +' . ($order->items->count() - 2) . ' more' : '' }}</div>
                    </div>
                    <div class="text-[13px] text-gray-600">
                        <div class="font-bold text-gray-900">{{ ucfirst($order->fulfillment_method) }}</div>
                        <div class="mt-1">{{ $order->pickup_location_title ?: trim(collect([$order->city, $order->country])->filter()->join(', ')) ?: 'No location' }}</div>
                    </div>
                    <div>
                        <span class="inline-flex rounded-full border px-2.5 py-1 text-[11px] font-black uppercase tracking-[0.16em] {{ $statusClasses($order->status) }}">
                            {{ $order->status }}
                        </span>
                        <div class="mt-2 text-[12px] text-gray-500">{{ $order->payment_method }}</div>
                    </div>
                    <div class="text-right text-[15px] font-black text-gray-900">
                        UGX {{ number_format((float) $order->total, 0) }}
                    </div>
                </a>
            @empty
                <div class="px-6 py-14 text-center sm:px-8">
                    <div class="text-[18px] font-black text-gray-900">No orders yet</div>
                    <p class="mx-auto mt-2 max-w-[520px] text-sm text-gray-500">Orders placed through the storefront checkout will appear here as soon as customers submit them.</p>
                    <a href="{{ route('dashboard.products') }}" class="mt-5 inline-flex h-11 items-center justify-center rounded-2xl bg-[#111827] px-5 text-[14px] font-black text-white">
                        Review products
                    </a>
                </div>
            @endforelse
        </section>
    </div>
</div>
@endsection
