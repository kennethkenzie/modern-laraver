@extends('admin.layout')

@section('title', 'Orders')

@section('content')
<style>
    @keyframes orderCardReveal {
        from {
            opacity: 0;
            transform: translateY(18px) scale(0.985);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .order-card-motion {
        opacity: 0;
        animation: orderCardReveal 560ms cubic-bezier(0.22, 1, 0.36, 1) forwards;
        animation-delay: var(--delay, 0ms);
        will-change: transform, opacity;
    }

    .order-card-hover {
        transition: transform 220ms ease, box-shadow 220ms ease, border-color 220ms ease;
    }

    .order-card-hover:hover {
        transform: translateY(-4px);
    }
</style>
<script id="orders-json" type="application/json">{!! json_encode($orders->values()) !!}</script>

<div class="bg-[#f7f7f8] -mx-4 -mt-8 min-h-screen px-4 py-8 md:-mx-6 md:px-6 xl:-mx-10 xl:px-10"
     x-data="ordersApp()">
    <div class="mx-auto max-w-[1440px]">
        <div class="mb-8 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex items-start gap-4">
                <span class="mt-3 h-3 w-10 shrink-0 rounded-full bg-[#0b63ce]"></span>
                <div>
                    <h1 class="text-[32px] font-bold tracking-tight text-gray-900">Orders</h1>
                    <p class="mt-1.5 text-[16px] font-medium text-gray-500">Track fulfillment, payment progress, and exceptions across incoming orders.</p>
                </div>
            </div>
            <div class="order-card-motion order-card-hover rounded-xl border border-[#dbe3ec] bg-white px-5 py-3 shadow-sm" style="--delay: 40ms;">
                <div class="text-[12px] font-bold uppercase tracking-wider text-gray-400">Operational Snapshot</div>
                <div class="mt-1 text-[24px] font-bold text-gray-900">{{ $todayRevenueLabel }}</div>
                <div class="text-[13px] text-gray-500">{{ $todayOrderCount }} orders placed today</div>
            </div>
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="order-card-motion order-card-hover rounded-xl bg-gradient-to-br from-[#111827] to-[#1f2937] p-5 text-white shadow-[0_14px_32px_rgba(15,23,42,0.08)]" style="--delay: 80ms;">
                <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-white/70">All Orders</div>
                <div class="mt-3 text-[30px] font-bold tracking-tight">{{ number_format($totalOrders) }}</div>
                <div class="mt-2 text-[13px] text-white/75">{{ $todayOrderCount }} created today</div>
            </div>
            <div class="order-card-motion order-card-hover rounded-xl bg-gradient-to-br from-[#92400e] to-[#f59e0b] p-5 text-white shadow-[0_14px_32px_rgba(15,23,42,0.08)]" style="--delay: 130ms;">
                <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-white/70">Needs Action</div>
                <div class="mt-3 text-[30px] font-bold tracking-tight">{{ number_format($pendingOrdersCount) }}</div>
                <div class="mt-2 text-[13px] text-white/75">Pending, processing, or on hold</div>
            </div>
            <div class="order-card-motion order-card-hover rounded-xl bg-gradient-to-br from-[#065f46] to-[#10b981] p-5 text-white shadow-[0_14px_32px_rgba(15,23,42,0.08)]" style="--delay: 180ms;">
                <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-white/70">Delivered</div>
                <div class="mt-3 text-[30px] font-bold tracking-tight">{{ number_format($deliveredOrdersCount) }}</div>
                <div class="mt-2 text-[13px] text-white/75">Successfully completed orders</div>
            </div>
            <div class="order-card-motion order-card-hover rounded-xl bg-gradient-to-br from-[#7f1d1d] to-[#ef4444] p-5 text-white shadow-[0_14px_32px_rgba(15,23,42,0.08)]" style="--delay: 230ms;">
                <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-white/70">Payment / Returns Issues</div>
                <div class="mt-3 text-[30px] font-bold tracking-tight">{{ number_format($issuesCount) }}</div>
                <div class="mt-2 text-[13px] text-white/75">Failed, refunded, or exception orders</div>
            </div>
        </section>

        <div class="mt-6 grid grid-cols-1 gap-6 2xl:grid-cols-[minmax(0,1.55fr)_minmax(340px,0.9fr)]">
            <section class="space-y-6">
                <section class="order-card-motion order-card-hover rounded-xl border border-[#e3e6ea] bg-white p-5 shadow-[0_18px_45px_rgba(15,23,42,0.05)] sm:p-6" style="--delay: 280ms;">
                    <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_220px_220px_220px] xl:items-center">
                        <div class="relative flex items-center overflow-hidden rounded-full border border-[#d5d9d9] bg-[#f7f8fa] px-4 focus-within:border-[#f59e0b] focus-within:bg-white">
                            <i data-lucide="search" class="h-4 w-4 shrink-0 text-gray-400"></i>
                            <input type="text" x-model="search" @input="currentPage = 1" placeholder="Search by order number, customer, email, or phone" class="h-12 w-full bg-transparent px-3 text-[14px] text-gray-700 outline-none placeholder:text-gray-400" />
                        </div>
                        <select x-model="filterStatus" @change="currentPage = 1" class="h-12 rounded-full border border-[#d5d9d9] bg-white px-4 text-[14px] text-gray-700 outline-none hover:border-[#aab7c4] focus:border-[#f59e0b]">
                            <template x-for="status in statuses" :key="status"><option :value="status" x-text="status"></option></template>
                        </select>
                        <select x-model="filterPayment" @change="currentPage = 1" class="h-12 rounded-full border border-[#d5d9d9] bg-white px-4 text-[14px] text-gray-700 outline-none hover:border-[#aab7c4] focus:border-[#f59e0b]">
                            <template x-for="status in paymentStatuses" :key="status"><option :value="status" x-text="status"></option></template>
                        </select>
                        <select x-model="filterFulfillment" @change="currentPage = 1" class="h-12 rounded-full border border-[#d5d9d9] bg-white px-4 text-[14px] text-gray-700 outline-none hover:border-[#aab7c4] focus:border-[#f59e0b]">
                            <template x-for="status in fulfillmentStatuses" :key="status"><option :value="status" x-text="status"></option></template>
                        </select>
                    </div>
                </section>

                <section class="order-card-motion order-card-hover overflow-hidden rounded-xl border border-[#e3e6ea] bg-white shadow-[0_20px_50px_rgba(15,23,42,0.06)]" style="--delay: 340ms;">
                    <div class="border-b border-[#edf0f2] bg-gradient-to-b from-white to-[#fafbfc] px-5 py-4 sm:px-6">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-[20px] font-bold text-[#111827]">Order queue</h2>
                                <p class="text-[13px] text-gray-500">Showing <span x-text="visibleStart"></span>–<span x-text="visibleEnd"></span> of <span x-text="filtered.length"></span> matching orders</p>
                            </div>
                            <div class="inline-flex rounded-full bg-[#f3f4f6] px-3 py-1 text-[12px] font-semibold text-gray-600">
                                Total revenue <span class="ml-1 font-bold text-[#111827]" x-text="formatCurrency(filteredRevenue)"></span>
                            </div>
                        </div>
                    </div>

                    <div x-show="paginated.length === 0" class="px-6 py-20 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#f3f4f6] text-gray-400"><i data-lucide="shopping-bag" class="h-7 w-7"></i></div>
                        <h3 class="mt-4 text-[18px] font-bold text-gray-900">No orders match these filters</h3>
                        <p class="mt-2 text-[14px] text-gray-500">Try broadening the search or switching the status filters.</p>
                    </div>

                    <div x-show="paginated.length > 0" class="hidden xl:block">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-[#edf0f2] bg-[#fafafa] text-[11px] font-bold uppercase tracking-[0.18em] text-gray-400">
                                    <th class="px-6 py-4">Order</th>
                                    <th class="px-6 py-4">Customer</th>
                                    <th class="px-6 py-4">Value</th>
                                    <th class="px-6 py-4">Payment</th>
                                    <th class="px-6 py-4">Fulfillment</th>
                                    <th class="px-6 py-4">Placed</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="order in paginated" :key="order.id">
                                    <tr class="border-b border-[#f2f4f7] last:border-b-0 hover:bg-[#fcfcfd]">
                                        <td class="px-6 py-5">
                                            <div class="flex items-center gap-4">
                                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#111827] text-sm font-bold text-white" x-text="order.number.replace('#', '')"></div>
                                                <div class="min-w-0">
                                                    <a :href="`/dashboard/orders/${order.id}`" class="text-[15px] font-bold text-[#111827] hover:text-[#114f8f]" x-text="order.number"></a>
                                                    <div class="mt-1 text-[12px] text-gray-400"><span x-text="order.items"></span> items • <span x-text="order.channel"></span></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5">
                                            <div class="text-[14px] font-semibold text-[#111827]" x-text="order.customer"></div>
                                            <div class="mt-1 text-[12px] text-gray-500" x-text="order.email"></div>
                                            <div class="mt-1 text-[12px] text-gray-400" x-text="order.destination"></div>
                                        </td>
                                        <td class="px-6 py-5">
                                            <div class="text-[15px] font-bold text-[#111827]" x-text="formatCurrency(order.total)"></div>
                                            <div class="mt-1 text-[12px] text-gray-400" x-text="order.paymentMethod"></div>
                                        </td>
                                        <td class="px-6 py-5"><span class="inline-flex rounded-full border px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em]" :class="paymentBadge(order.paymentStatus)" x-text="order.paymentStatus"></span></td>
                                        <td class="px-6 py-5">
                                            <div class="flex flex-wrap gap-2">
                                                <span class="inline-flex rounded-full border px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em]" :class="statusBadge(order.status)" x-text="order.status"></span>
                                                <span class="inline-flex rounded-full border px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em]" :class="fulfillmentBadge(order.fulfillmentStatus)" x-text="order.fulfillmentStatus"></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5">
                                            <div class="text-[14px] font-medium text-[#111827]" x-text="order.placedAtLabel"></div>
                                            <div class="mt-1 text-[12px] text-gray-400" x-text="order.placedAtRelative"></div>
                                            <a :href="`/dashboard/orders/${order.id}`" class="mt-2 inline-flex text-[12px] font-semibold text-[#2554e8] hover:underline">Open order</a>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div x-show="paginated.length > 0" class="grid gap-4 p-4 sm:p-5 xl:hidden">
                        <template x-for="order in paginated" :key="order.id">
                            <article class="order-card-hover rounded-xl border border-[#e7ebef] bg-[#fcfcfd] p-4 shadow-[0_10px_24px_rgba(15,23,42,0.04)]">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <a :href="`/dashboard/orders/${order.id}`" class="text-[16px] font-bold text-[#111827] hover:text-[#114f8f]" x-text="order.number"></a>
                                        <div class="mt-1 text-[12px] text-gray-400" x-text="order.placedAtLabel"></div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-[15px] font-bold text-[#111827]" x-text="formatCurrency(order.total)"></div>
                                        <div class="mt-1 text-[12px] text-gray-400" x-text="order.paymentMethod"></div>
                                    </div>
                                </div>
                                <div class="mt-4 rounded-xl bg-white px-4 py-3">
                                    <div class="text-[14px] font-semibold text-[#111827]" x-text="order.customer"></div>
                                    <div class="mt-1 text-[12px] text-gray-500" x-text="order.email"></div>
                                    <div class="mt-1 text-[12px] text-gray-400"><span x-text="order.items"></span> items • <span x-text="order.destination"></span></div>
                                </div>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <span class="inline-flex rounded-full border px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em]" :class="paymentBadge(order.paymentStatus)" x-text="order.paymentStatus"></span>
                                    <span class="inline-flex rounded-full border px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em]" :class="statusBadge(order.status)" x-text="order.status"></span>
                                    <span class="inline-flex rounded-full border px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em]" :class="fulfillmentBadge(order.fulfillmentStatus)" x-text="order.fulfillmentStatus"></span>
                                </div>
                                <a :href="`/dashboard/orders/${order.id}`" class="mt-4 inline-flex text-[13px] font-semibold text-[#2554e8] hover:underline">Open order details</a>
                            </article>
                        </template>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-[#edf0f2] bg-[#fafbfc] px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <p class="text-[13px] text-gray-500">
                            <template x-if="filtered.length === 0"><span>No orders match the current filters.</span></template>
                            <template x-if="filtered.length > 0"><span>Showing <span x-text="visibleStart"></span>–<span x-text="visibleEnd"></span> of <span x-text="filtered.length"></span> orders</span></template>
                        </p>
                        <div class="flex flex-wrap items-center gap-2">
                            <button @click="currentPage = Math.max(1, safePage - 1)" :disabled="safePage === 1" class="inline-flex h-10 items-center justify-center rounded-full border border-[#d5d9d9] bg-white px-4 text-[13px] font-semibold text-gray-700 hover:bg-[#f8f9fb] disabled:opacity-40">Previous</button>
                            <template x-for="page in pages" :key="page"><button @click="currentPage = page" :class="page === safePage ? 'bg-[#f59e0b] text-[#111827]' : 'border border-[#d5d9d9] bg-white text-gray-700 hover:bg-[#f8f9fb]'" class="inline-flex h-10 w-10 items-center justify-center rounded-full text-[13px] font-bold" x-text="page"></button></template>
                            <button @click="currentPage = Math.min(totalPages, safePage + 1)" :disabled="safePage === totalPages" class="inline-flex h-10 items-center justify-center rounded-full border border-[#d5d9d9] bg-white px-4 text-[13px] font-semibold text-gray-700 hover:bg-[#f8f9fb] disabled:opacity-40">Next</button>
                        </div>
                    </div>
                </section>
            </section>

            <aside class="grid gap-6">
                <section class="order-card-motion order-card-hover rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-[0_1px_2px_rgba(16,24,40,0.03)]" style="--delay: 400ms;">
                    <div class="mb-4 flex items-center justify-between gap-3"><h3 class="text-[18px] font-semibold text-[#0b1220]">Fulfillment queue</h3><span class="text-[13px] font-medium text-[#2554e8]">Warehouse</span></div>
                    <div class="space-y-4">
                        @foreach($fulfillmentQueues as $queue)
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-[#eef1f4] p-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl @if($queue['tone'] === 'amber') bg-[#fff7ed] text-[#c2410c] @elseif($queue['tone'] === 'blue') bg-[#eff6ff] text-[#2563eb] @elseif($queue['tone'] === 'indigo') bg-[#eef2ff] text-[#4f46e5] @else bg-[#f0fdf4] text-[#16a34a] @endif"><i data-lucide="{{ $queue['icon'] }}" class="h-5 w-5"></i></div>
                                    <div class="min-w-0"><div class="text-[14px] font-medium text-[#111827]">{{ $queue['label'] }}</div><div class="mt-1 text-[12px] text-[#7b8394]">Current workload</div></div>
                                </div>
                                <div class="text-[20px] font-bold text-[#111827]">{{ $queue['count'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="order-card-motion order-card-hover rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-[0_1px_2px_rgba(16,24,40,0.03)]" style="--delay: 460ms;">
                    <div class="mb-4 flex items-center justify-between gap-3"><h3 class="text-[18px] font-semibold text-[#0b1220]">Payment breakdown</h3><span class="text-[13px] font-medium text-[#2554e8]">{{ $totalRevenueLabel }}</span></div>
                    <div class="space-y-4">
                        @foreach($paymentBreakdown as $payment)
                            <div>
                                <div class="mb-1 flex items-center justify-between gap-3"><span class="text-[14px] font-medium text-[#111827]">{{ $payment['label'] }}</span><span class="text-[13px] text-[#6b7280]">{{ $payment['count'] }} orders</span></div>
                                <div class="h-2.5 rounded-full bg-[#eef2f7]"><div class="h-2.5 rounded-full @if($payment['tone'] === 'green') bg-[#10b981] @elseif($payment['tone'] === 'amber') bg-[#f59e0b] @elseif($payment['tone'] === 'red') bg-[#ef4444] @else bg-[#94a3b8] @endif" style="width: {{ $totalOrders > 0 ? max(8, round(($payment['count'] / $totalOrders) * 100)) : 0 }}%"></div></div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="order-card-motion order-card-hover rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-[0_1px_2px_rgba(16,24,40,0.03)]" style="--delay: 520ms;">
                    <div class="mb-4 flex items-center justify-between gap-3"><h3 class="text-[18px] font-semibold text-[#0b1220]">Recent order activity</h3><span class="text-[13px] font-medium text-[#2554e8]">Live feed</span></div>
                    <div class="space-y-4">
                        @forelse($recentEvents as $event)
                            <div class="flex items-start gap-3">
                                <div class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#eef3ff] text-[#2554e8]"><i data-lucide="{{ $event['icon'] }}" class="h-4 w-4"></i></div>
                                <div class="min-w-0"><p class="text-[14px] text-[#111827]">{{ $event['label'] }}</p><p class="mt-1 text-[12px] text-[#9ca3af]">{{ $event['time'] }}</p></div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-[#d7dce3] px-4 py-6 text-center text-[14px] text-[#7b8394]">
                                No order activity available yet.
                            </div>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function ordersApp() {
    const el = document.getElementById('orders-json');
    const allOrders = el ? JSON.parse(el.textContent) : [];
    return {
        allOrders,
        search: '',
        filterStatus: 'All statuses',
        filterPayment: 'All payment states',
        filterFulfillment: 'All fulfillment states',
        currentPage: 1,
        pageSize: 8,
        get statuses() { return ['All statuses', ...new Set(this.allOrders.map(order => order.status))]; },
        get paymentStatuses() { return ['All payment states', ...new Set(this.allOrders.map(order => order.paymentStatus))]; },
        get fulfillmentStatuses() { return ['All fulfillment states', ...new Set(this.allOrders.map(order => order.fulfillmentStatus))]; },
        get filtered() {
            const q = this.search.trim().toLowerCase();
            return this.allOrders.filter(order => {
                const matchesSearch = !q || [order.number, order.customer, order.email, order.phone, order.destination].some(value => String(value).toLowerCase().includes(q));
                const matchesStatus = this.filterStatus === 'All statuses' || order.status === this.filterStatus;
                const matchesPayment = this.filterPayment === 'All payment states' || order.paymentStatus === this.filterPayment;
                const matchesFulfillment = this.filterFulfillment === 'All fulfillment states' || order.fulfillmentStatus === this.filterFulfillment;
                return matchesSearch && matchesStatus && matchesPayment && matchesFulfillment;
            });
        },
        get filteredRevenue() { return this.filtered.reduce((sum, order) => sum + Number(order.total || 0), 0); },
        get totalPages() { return Math.max(1, Math.ceil(this.filtered.length / this.pageSize)); },
        get safePage() { return Math.min(this.currentPage, this.totalPages); },
        get paginated() { const start = (this.safePage - 1) * this.pageSize; return this.filtered.slice(start, start + this.pageSize); },
        get visibleStart() { return this.filtered.length === 0 ? 0 : (this.safePage - 1) * this.pageSize + 1; },
        get visibleEnd() { return Math.min(this.filtered.length, this.safePage * this.pageSize); },
        get pages() { const start = Math.max(1, this.safePage - 2); const end = Math.min(this.totalPages, start + 4); return Array.from({ length: end - start + 1 }, (_, i) => start + i); },
        formatCurrency(value) { return 'UGX ' + new Intl.NumberFormat('en-UG').format(value || 0); },
        paymentBadge(status) { return ({'Paid':'border-[#86efac] bg-[#f0fdf4] text-[#166534]','Pending':'border-[#fde68a] bg-[#fffbeb] text-[#ca8a04]','Failed':'border-[#fecaca] bg-[#fef2f2] text-[#b91c1c]','Refunded':'border-[#cbd5e1] bg-[#f8fafc] text-[#475569]'})[status] || 'border-[#e5e7eb] bg-[#f3f4f6] text-gray-600'; },
        statusBadge(status) { return ({'New':'border-[#bfdbfe] bg-[#eff6ff] text-[#1d4ed8]','Processing':'border-[#c7d2fe] bg-[#eef2ff] text-[#4338ca]','Completed':'border-[#86efac] bg-[#f0fdf4] text-[#166534]','Awaiting Payment':'border-[#fde68a] bg-[#fffbeb] text-[#ca8a04]','Issue':'border-[#fecaca] bg-[#fef2f2] text-[#b91c1c]','Returned':'border-[#e9d5ff] bg-[#faf5ff] text-[#7e22ce]'})[status] || 'border-[#e5e7eb] bg-[#f3f4f6] text-gray-600'; },
        fulfillmentBadge(status) { return ({'Pending':'border-[#fde68a] bg-[#fffbeb] text-[#ca8a04]','Processing':'border-[#bfdbfe] bg-[#eff6ff] text-[#1d4ed8]','Shipped':'border-[#c7d2fe] bg-[#eef2ff] text-[#4338ca]','Delivered':'border-[#86efac] bg-[#f0fdf4] text-[#166534]','On Hold':'border-[#fecaca] bg-[#fef2f2] text-[#b91c1c]','Returned':'border-[#e9d5ff] bg-[#faf5ff] text-[#7e22ce]'})[status] || 'border-[#e5e7eb] bg-[#f3f4f6] text-gray-600'; },
        init() { this.$nextTick(() => lucide.createIcons()); },
    };
}
</script>
@endpush
