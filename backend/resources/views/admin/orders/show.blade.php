@extends('admin.layout')

@section('title', 'Order ' . $order->order_number)

@section('content')
<div class="-mx-4 -mt-4 min-h-screen bg-[#f7f7f8] px-4 pt-6 pb-16 sm:-mx-6 sm:px-6 sm:pt-8 xl:-mx-10 xl:px-10">
    <div class="mx-auto max-w-[1120px] space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="flex items-start gap-3">
                <span class="mt-[10px] h-2.5 w-8 shrink-0 rounded-full bg-[#114f8f]"></span>
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-[#114f8f]">Commerce Operations</p>
                    <h1 class="mt-2 text-[30px] font-black tracking-tight text-gray-900">{{ $order->order_number }}</h1>
                    <p class="mt-2 max-w-[760px] text-sm text-gray-500">Placed {{ $order->created_at->format('M j, Y g:i A') }} by {{ $order->customer_name }}.</p>
                </div>
            </div>
            <a href="{{ route('dashboard.orders') }}" class="inline-flex h-11 items-center justify-center rounded-2xl bg-[#111827] px-5 text-[14px] font-black tracking-wide text-white transition hover:bg-black">
                Back to orders
            </a>
        </div>

        <section class="grid gap-4 md:grid-cols-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Status</div>
                <div class="mt-3 text-[24px] font-black tracking-tight text-gray-900">{{ ucfirst($order->status) }}</div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Payment</div>
                <div class="mt-3 text-[24px] font-black tracking-tight text-gray-900">{{ $order->payment_method }}</div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Fulfillment</div>
                <div class="mt-3 text-[24px] font-black tracking-tight text-gray-900">{{ ucfirst($order->fulfillment_method) }}</div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Total</div>
                <div class="mt-3 text-[24px] font-black tracking-tight text-gray-900">UGX {{ number_format((float) $order->total, 0) }}</div>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
            <div class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-5">
                    <h2 class="text-[20px] font-black text-gray-900">Items</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($order->items as $item)
                        <div class="grid gap-4 px-6 py-5 sm:grid-cols-[minmax(0,1fr)_100px_140px]">
                            <div>
                                <div class="text-[15px] font-bold text-gray-900">{{ $item->name }}</div>
                                <div class="mt-1 text-[13px] text-gray-500">{{ $item->href }}</div>
                            </div>
                            <div class="text-[14px] text-gray-600">Qty {{ $item->quantity }}</div>
                            <div class="text-right text-[15px] font-black text-gray-900">UGX {{ number_format((float) $item->line_total, 0) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <aside class="space-y-6">
                <section class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-[20px] font-black text-gray-900">Customer</h2>
                    <div class="mt-4 space-y-3 text-sm text-gray-600">
                        <div><span class="font-bold text-gray-900">Name:</span> {{ $order->customer_name }}</div>
                        <div><span class="font-bold text-gray-900">Phone:</span> {{ $order->customer_phone ?: '—' }}</div>
                        <div><span class="font-bold text-gray-900">Email:</span> {{ $order->customer_email ?: '—' }}</div>
                    </div>
                </section>

                <section class="rounded-[28px] border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-[20px] font-black text-gray-900">Address</h2>
                    <div class="mt-4 text-sm leading-6 text-gray-600">
                        @if($order->fulfillment_method === 'pickup')
                            <div class="font-bold text-gray-900">{{ $order->pickup_location_title ?: 'Pickup location' }}</div>
                        @endif
                        <div>{{ $order->address ?: 'No address saved' }}</div>
                        <div>{{ collect([$order->city, $order->country])->filter()->join(', ') }}</div>
                    </div>
                </section>
            </aside>
        </section>
    </div>
</div>
@endsection
