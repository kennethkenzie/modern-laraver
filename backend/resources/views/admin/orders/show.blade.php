@extends('admin.layout')

@section('title', 'Order Details')

@section('content')
<style>
    @keyframes orderDetailReveal {
        from {
            opacity: 0;
            transform: translateY(16px) scale(0.987);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .order-detail-motion {
        opacity: 0;
        animation: orderDetailReveal 540ms cubic-bezier(0.22, 1, 0.36, 1) forwards;
        animation-delay: var(--delay, 0ms);
        will-change: transform, opacity;
    }

    .order-detail-hover {
        transition: transform 220ms ease, box-shadow 220ms ease, border-color 220ms ease;
    }

    .order-detail-hover:hover {
        transform: translateY(-3px);
    }
</style>
<script id="order-json" type="application/json">{!! json_encode($order) !!}</script>

<div class="bg-[#f7f7f8] -mx-4 -mt-8 min-h-screen px-4 py-8 md:-mx-6 md:px-6 xl:-mx-10 xl:px-10"
     x-data="orderDetailApp()">
    <div class="mx-auto max-w-[1440px]">
        <div class="mb-8 flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div class="flex items-start gap-4">
                <span class="mt-3 h-3 w-10 shrink-0 rounded-full bg-[#0b63ce]"></span>
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-[32px] font-bold tracking-tight text-gray-900">Order <span x-text="order.number"></span></h1>
                        <span class="inline-flex rounded-full border px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em]" :class="statusBadge(order.status)" x-text="order.status"></span>
                    </div>
                    <p class="mt-1.5 text-[16px] font-medium text-gray-500">Placed <span x-text="order.placedAtLabel"></span> by <span x-text="order.customer"></span>.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('dashboard.orders') }}" class="inline-flex h-12 items-center gap-2 rounded-full border border-[#d5d9d9] bg-white px-5 text-[14px] font-bold text-gray-700 hover:bg-[#f8f9fb]">
                    <i data-lucide="arrow-left" class="h-4 w-4"></i>
                    Back to orders
                </a>
            </div>
        </div>

        <div x-show="notice" x-cloak x-transition
             :class="notice?.tone === 'success' ? 'border-[#bbf7d0] bg-[#f0fdf4] text-[#166534]' : 'border-[#fecaca] bg-[#fef2f2] text-[#b91c1c]'"
             class="mb-6 rounded-xl border px-4 py-3 text-sm font-medium">
            <span x-text="notice?.text"></span>
        </div>

        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-[minmax(0,1.5fr)_minmax(360px,0.85fr)]">
            <section class="space-y-6">
                <section class="order-detail-motion order-detail-hover rounded-xl border border-[#e3e6ea] bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.05)]" style="--delay: 70ms;">
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-xl bg-[#f8fafc] p-4">
                            <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-gray-400">Order Total</div>
                            <div class="mt-2 text-[24px] font-bold text-[#111827]" x-text="formatCurrency(order.total)"></div>
                        </div>
                        <div class="rounded-xl bg-[#f8fafc] p-4">
                            <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-gray-400">Payment</div>
                            <div class="mt-2"><span class="inline-flex rounded-full border px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em]" :class="paymentBadge(order.paymentStatus)" x-text="order.paymentStatus"></span></div>
                        </div>
                        <div class="rounded-xl bg-[#f8fafc] p-4">
                            <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-gray-400">Fulfillment</div>
                            <div class="mt-2"><span class="inline-flex rounded-full border px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em]" :class="fulfillmentBadge(order.fulfillmentStatus)" x-text="order.fulfillmentStatus"></span></div>
                        </div>
                        <div class="rounded-xl bg-[#f8fafc] p-4">
                            <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-gray-400">Sales Channel</div>
                            <div class="mt-2 text-[15px] font-semibold text-[#111827]" x-text="order.channel"></div>
                        </div>
                    </div>
                </section>

                <section class="order-detail-motion order-detail-hover rounded-xl border border-[#e3e6ea] bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.05)]" style="--delay: 130ms;">
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-[20px] font-bold text-[#111827]">Line items</h2>
                            <p class="text-[13px] text-gray-500">Products attached to this order.</p>
                        </div>
                    </div>
                    <div class="overflow-hidden rounded-xl border border-[#edf0f2]">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-[#fafafa] text-[11px] font-bold uppercase tracking-[0.18em] text-gray-400">
                                    <th class="px-5 py-4">Item</th>
                                    <th class="px-5 py-4">SKU</th>
                                    <th class="px-5 py-4">Qty</th>
                                    <th class="px-5 py-4">Unit Price</th>
                                    <th class="px-5 py-4">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="item in order.lineItems" :key="`${item.sku}-${item.name}`">
                                    <tr class="border-t border-[#f2f4f7]">
                                        <td class="px-5 py-4 text-[14px] font-semibold text-[#111827]" x-text="item.name"></td>
                                        <td class="px-5 py-4 text-[13px] text-gray-500" x-text="item.sku"></td>
                                        <td class="px-5 py-4 text-[14px] text-[#111827]" x-text="item.qty"></td>
                                        <td class="px-5 py-4 text-[14px] text-[#111827]" x-text="formatCurrency(item.unitPrice)"></td>
                                        <td class="px-5 py-4 text-[14px] font-semibold text-[#111827]" x-text="formatCurrency(item.qty * item.unitPrice)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-5 ml-auto grid max-w-[320px] gap-3 text-[14px]">
                        <div class="flex items-center justify-between"><span class="text-gray-500">Subtotal</span><span class="font-semibold text-[#111827]" x-text="order.subtotalFormatted"></span></div>
                        <div class="flex items-center justify-between"><span class="text-gray-500">Shipping</span><span class="font-semibold text-[#111827]" x-text="order.shippingAmountFormatted"></span></div>
                        <div class="flex items-center justify-between"><span class="text-gray-500">Discount</span><span class="font-semibold text-[#111827]" x-text="order.discountAmountFormatted"></span></div>
                        <div class="flex items-center justify-between border-t border-[#edf0f2] pt-3"><span class="font-bold text-[#111827]">Grand Total</span><span class="text-[18px] font-bold text-[#111827]" x-text="order.totalFormatted"></span></div>
                    </div>
                </section>

                <section class="order-detail-motion order-detail-hover rounded-xl border border-[#e3e6ea] bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.05)]" style="--delay: 190ms;">
                    <div class="mb-5">
                        <h2 class="text-[20px] font-bold text-[#111827]">Customer & delivery</h2>
                        <p class="text-[13px] text-gray-500">Contact information and destination.</p>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-xl bg-[#f8fafc] p-4">
                            <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-gray-400">Customer</div>
                            <div class="mt-2 text-[16px] font-semibold text-[#111827]" x-text="order.customer"></div>
                            <div class="mt-1 text-[14px] text-gray-500" x-text="order.email"></div>
                            <div class="mt-1 text-[14px] text-gray-500" x-text="order.phone"></div>
                        </div>
                        <div class="rounded-xl bg-[#f8fafc] p-4">
                            <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-gray-400">Shipping Address</div>
                            <div class="mt-2 text-[15px] font-semibold text-[#111827]" x-text="order.destination"></div>
                            <div class="mt-1 text-[14px] text-gray-500" x-text="order.shippingAddress"></div>
                        </div>
                    </div>
                </section>
            </section>

            <aside class="grid gap-6">
                <section class="order-detail-motion order-detail-hover rounded-xl border border-[#e3e6ea] bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.05)]" style="--delay: 250ms;">
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-[20px] font-bold text-[#111827]">Edit statuses</h2>
                            <p class="text-[13px] text-gray-500">Update workflow state for this order.</p>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full bg-[#f3f4f6] px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-gray-600" x-show="saving">
                            <i data-lucide="loader-circle" class="h-4 w-4 animate-spin"></i>
                            Saving
                        </span>
                    </div>

                    <form @submit.prevent="saveStatuses" class="space-y-5">
                        <div>
                            <label class="mb-2 block text-[13px] font-bold text-gray-700">Order Status</label>
                            <select x-model="form.status" class="h-12 w-full rounded-2xl border border-[#d5d9d9] bg-white px-4 text-[14px] text-gray-700 outline-none focus:border-[#0b63ce]">
                                @foreach($orderStatuses as $status)
                                    <option value="{{ $status }}">{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-[13px] font-bold text-gray-700">Payment Status</label>
                            <select x-model="form.paymentStatus" class="h-12 w-full rounded-2xl border border-[#d5d9d9] bg-white px-4 text-[14px] text-gray-700 outline-none focus:border-[#0b63ce]">
                                @foreach($paymentStatuses as $status)
                                    <option value="{{ $status }}">{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-[13px] font-bold text-gray-700">Fulfillment Status</label>
                            <select x-model="form.fulfillmentStatus" class="h-12 w-full rounded-2xl border border-[#d5d9d9] bg-white px-4 text-[14px] text-gray-700 outline-none focus:border-[#0b63ce]">
                                @foreach($fulfillmentStatuses as $status)
                                    <option value="{{ $status }}">{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" :disabled="saving" class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-full bg-[#111827] px-5 text-[14px] font-bold text-white transition hover:bg-black disabled:opacity-50">
                            <i data-lucide="save" class="h-4 w-4"></i>
                            Save Status Changes
                        </button>
                    </form>
                </section>

                <section class="order-detail-motion order-detail-hover rounded-xl border border-[#e3e6ea] bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.05)]" style="--delay: 310ms;">
                    <div class="mb-5">
                        <h2 class="text-[20px] font-bold text-[#111827]">Order timeline</h2>
                        <p class="text-[13px] text-gray-500">Recent events captured on this order.</p>
                    </div>
                    <div class="space-y-4">
                        <template x-for="event in order.timeline" :key="`${event.label}-${event.time}`">
                            <div class="flex items-start gap-3">
                                <div class="mt-1 h-3 w-3 shrink-0 rounded-full bg-[#0b63ce]"></div>
                                <div>
                                    <div class="text-[14px] font-medium text-[#111827]" x-text="event.label"></div>
                                    <div class="mt-1 text-[12px] text-gray-400" x-text="event.time"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </section>

                <section class="order-detail-motion order-detail-hover rounded-xl border border-[#e3e6ea] bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.05)]" style="--delay: 370ms;">
                    <div class="mb-5">
                        <h2 class="text-[20px] font-bold text-[#111827]">Related orders</h2>
                        <p class="text-[13px] text-gray-500">Other orders from this customer.</p>
                    </div>
                    @if($relatedOrders->isEmpty())
                        <div class="rounded-xl border border-dashed border-[#d7dce3] px-4 py-6 text-center text-[14px] text-[#7b8394]">
                            No additional orders for this customer yet.
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($relatedOrders as $related)
                                <a href="{{ route('dashboard.orders.show', $related['id']) }}" class="block rounded-xl border border-[#eef1f4] p-4 transition hover:bg-[#fcfcfd]">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <div class="text-[14px] font-semibold text-[#111827]">{{ $related['number'] }}</div>
                                            <div class="mt-1 text-[12px] text-gray-400">{{ $related['placedAtLabel'] }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-[14px] font-semibold text-[#111827]">{{ $related['totalFormatted'] }}</div>
                                            <div class="mt-1 text-[12px] text-gray-400">{{ $related['status'] }}</div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>
            </aside>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function orderDetailApp() {
    const initialOrder = JSON.parse(document.getElementById('order-json').textContent);
    return {
        order: initialOrder,
        form: {
            status: initialOrder.status,
            paymentStatus: initialOrder.paymentStatus,
            fulfillmentStatus: initialOrder.fulfillmentStatus,
        },
        saving: false,
        notice: null,

        formatCurrency(value) {
            return 'UGX ' + new Intl.NumberFormat('en-UG').format(value || 0);
        },

        paymentBadge(status) {
            return ({'Paid':'border-[#86efac] bg-[#f0fdf4] text-[#166534]','Pending':'border-[#fde68a] bg-[#fffbeb] text-[#ca8a04]','Failed':'border-[#fecaca] bg-[#fef2f2] text-[#b91c1c]','Refunded':'border-[#cbd5e1] bg-[#f8fafc] text-[#475569]'})[status] || 'border-[#e5e7eb] bg-[#f3f4f6] text-gray-600';
        },

        statusBadge(status) {
            return ({'New':'border-[#bfdbfe] bg-[#eff6ff] text-[#1d4ed8]','Processing':'border-[#c7d2fe] bg-[#eef2ff] text-[#4338ca]','Completed':'border-[#86efac] bg-[#f0fdf4] text-[#166534]','Awaiting Payment':'border-[#fde68a] bg-[#fffbeb] text-[#ca8a04]','Issue':'border-[#fecaca] bg-[#fef2f2] text-[#b91c1c]','Returned':'border-[#e9d5ff] bg-[#faf5ff] text-[#7e22ce]'})[status] || 'border-[#e5e7eb] bg-[#f3f4f6] text-gray-600';
        },

        fulfillmentBadge(status) {
            return ({'Pending':'border-[#fde68a] bg-[#fffbeb] text-[#ca8a04]','Processing':'border-[#bfdbfe] bg-[#eff6ff] text-[#1d4ed8]','Shipped':'border-[#c7d2fe] bg-[#eef2ff] text-[#4338ca]','Delivered':'border-[#86efac] bg-[#f0fdf4] text-[#166534]','On Hold':'border-[#fecaca] bg-[#fef2f2] text-[#b91c1c]','Returned':'border-[#e9d5ff] bg-[#faf5ff] text-[#7e22ce]'})[status] || 'border-[#e5e7eb] bg-[#f3f4f6] text-gray-600';
        },

        showNotice(tone, text) {
            this.notice = { tone, text };
            setTimeout(() => this.notice = null, 3500);
        },

        async saveStatuses() {
            this.saving = true;
            try {
                const response = await fetch(`{{ route('dashboard.orders.status', $order['id']) }}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify(this.form),
                });

                const payload = await response.json();
                if (!response.ok || !payload.order) {
                    throw new Error(payload.error || payload.message || 'Failed to update order.');
                }

                this.order = payload.order;
                this.form = {
                    status: payload.order.status,
                    paymentStatus: payload.order.paymentStatus,
                    fulfillmentStatus: payload.order.fulfillmentStatus,
                };
                this.showNotice('success', payload.message || 'Order statuses updated.');
            } catch (error) {
                this.showNotice('error', error instanceof Error ? error.message : 'Failed to update order.');
            } finally {
                this.saving = false;
                this.$nextTick(() => lucide.createIcons());
            }
        },

        init() {
            this.$nextTick(() => lucide.createIcons());
        },
    };
}
</script>
@endpush
