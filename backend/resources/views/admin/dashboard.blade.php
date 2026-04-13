@extends('admin.layout')

@section('title', 'Overview')

@section('content')

{{-- ═══════════════════════════════════════════════
     STAT CARDS  (4-column responsive grid)
════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 gap-4 md:grid-cols-2 2xl:grid-cols-4">

    {{-- Products --}}
    <div class="group rounded-2xl border border-gray-200 bg-white p-7 shadow-sm transition-all hover:shadow-xl hover:shadow-blue-900/[0.03]">
        <div class="flex items-start justify-between gap-3">
            <h3 class="text-[16px] font-bold uppercase tracking-widest text-gray-400">Products</h3>
            <span class="inline-flex items-center gap-1 rounded-full border border-green-100 bg-green-50 px-2.5 py-1 text-[13px] font-bold text-green-600">
                <i data-lucide="arrow-up-right" class="h-3.5 w-3.5" style="stroke-width:3"></i>
                {{ $recentUploads->count() }} recent
            </span>
        </div>
        <div class="mt-4 text-[42px] font-black leading-none tracking-tight text-[#111827]">{{ number_format($totalProducts) }}</div>
        <div class="mt-6 flex items-center gap-2 text-[16px] font-bold text-[#114f8f]">
            <span>Catalog count is live</span>
            <i data-lucide="arrow-up-right" class="h-[18px] w-[18px] text-[#f6c400]"></i>
        </div>
        <p class="mt-2 text-[14px] font-medium text-gray-500">Updates immediately after upload</p>
    </div>

    {{-- Published --}}
    <div class="group rounded-2xl border border-gray-200 bg-white p-7 shadow-sm transition-all hover:shadow-xl hover:shadow-blue-900/[0.03]">
        <div class="flex items-start justify-between gap-3">
            <h3 class="text-[16px] font-bold uppercase tracking-widest text-gray-400">Published</h3>
            <span class="inline-flex items-center gap-1 rounded-full border border-green-100 bg-green-50 px-2.5 py-1 text-[13px] font-bold text-green-600">
                <i data-lucide="arrow-up-right" class="h-3.5 w-3.5" style="stroke-width:3"></i>
                {{ $draftCount }} drafts
            </span>
        </div>
        <div class="mt-4 text-[42px] font-black leading-none tracking-tight text-[#111827]">{{ number_format($publishedCount) }}</div>
        <div class="mt-6 flex items-center gap-2 text-[16px] font-bold text-[#114f8f]">
            <span>Storefront-ready products</span>
            <i data-lucide="arrow-up-right" class="h-[18px] w-[18px] text-[#f6c400]"></i>
        </div>
        <p class="mt-2 text-[14px] font-medium text-gray-500">Visible to customers on the site</p>
    </div>

    {{-- Featured --}}
    <div class="group rounded-2xl border border-gray-200 bg-white p-7 shadow-sm transition-all hover:shadow-xl hover:shadow-blue-900/[0.03]">
        <div class="flex items-start justify-between gap-3">
            <h3 class="text-[16px] font-bold uppercase tracking-widest text-gray-400">Featured</h3>
            <span class="inline-flex items-center gap-1 rounded-full border border-green-100 bg-green-50 px-2.5 py-1 text-[13px] font-bold text-green-600">
                <i data-lucide="arrow-up-right" class="h-3.5 w-3.5" style="stroke-width:3"></i>
                {{ max($totalProducts - $featuredCount, 0) }} standard
            </span>
        </div>
        <div class="mt-4 text-[42px] font-black leading-none tracking-tight text-[#111827]">{{ number_format($featuredCount) }}</div>
        <div class="mt-6 flex items-center gap-2 text-[16px] font-bold text-[#114f8f]">
            <span>Homepage-featured items</span>
            <i data-lucide="arrow-up-right" class="h-[18px] w-[18px] text-[#f6c400]"></i>
        </div>
        <p class="mt-2 text-[14px] font-medium text-gray-500">Marked for highlighted placement</p>
    </div>

    {{-- Low Stock --}}
    @php $hasCritical = $lowStockProducts->contains('status', 'Critical'); @endphp
    <div class="group rounded-2xl border border-gray-200 bg-white p-7 shadow-sm transition-all hover:shadow-xl hover:shadow-blue-900/[0.03]">
        <div class="flex items-start justify-between gap-3">
            <h3 class="text-[16px] font-bold uppercase tracking-widest text-gray-400">Low Stock</h3>
            @if($hasCritical)
                <span class="inline-flex items-center gap-1 rounded-full border border-red-100 bg-red-50 px-2.5 py-1 text-[13px] font-bold text-red-600">
                    <i data-lucide="arrow-down-right" class="h-3.5 w-3.5" style="stroke-width:3"></i>
                    Needs attention
                </span>
            @else
                <span class="inline-flex items-center gap-1 rounded-full border border-green-100 bg-green-50 px-2.5 py-1 text-[13px] font-bold text-green-600">
                    <i data-lucide="arrow-up-right" class="h-3.5 w-3.5" style="stroke-width:3"></i>
                    Stable
                </span>
            @endif
        </div>
        <div class="mt-4 text-[42px] font-black leading-none tracking-tight text-[#111827]">{{ $lowStockProducts->count() }}</div>
        <div class="mt-6 flex items-center gap-2 text-[16px] font-bold {{ $hasCritical ? 'text-red-500' : 'text-[#114f8f]' }}">
            <span>{{ $lowStockProducts->count() > 0 ? 'Restock soon' : 'Inventory levels look good' }}</span>
            @if($hasCritical)
                <i data-lucide="arrow-down-right" class="h-[18px] w-[18px] text-red-400"></i>
            @else
                <i data-lucide="arrow-up-right" class="h-[18px] w-[18px] text-[#f6c400]"></i>
            @endif
        </div>
        <p class="mt-2 text-[14px] font-medium text-gray-500">Tracks items with 5 units or fewer</p>
    </div>

</div>


{{-- ═══════════════════════════════════════════════
     SALES CHART  +  SIDE CARDS
════════════════════════════════════════════════ --}}
<div class="mt-6 grid grid-cols-1 gap-6 2xl:grid-cols-[minmax(0,1.7fr)_minmax(320px,0.9fr)]">

    {{-- Sales Overview Chart --}}
    <div class="rounded-[28px] border border-[#e5e7eb] bg-white p-6 shadow-[0_1px_2px_rgba(16,24,40,0.03)]">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h3 class="text-[28px] font-semibold text-[#0b1220]">Sales Overview</h3>
                <p class="mt-1 text-[16px] text-[#7b8394]">Revenue and order activity for the last 3 months</p>
            </div>
            <div class="inline-flex w-full overflow-hidden rounded-2xl border border-[#e5e7eb] bg-[#fafafa] lg:w-auto" x-data="{ period: 0 }">
                @foreach(['Last 3 months', 'Last 30 days', 'Last 7 days'] as $i => $label)
                    <button @click="period = {{ $i }}"
                            :class="period === {{ $i }} ? 'bg-[#f3f4f6] text-[#111827]' : 'text-[#111827]'"
                            class="px-6 py-3 text-[15px] font-medium {{ $i > 0 ? 'border-l border-[#e5e7eb]' : '' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="mt-8 h-[320px] w-full rounded-2xl bg-white">
            <svg viewBox="0 0 1200 320" class="h-full w-full" preserveAspectRatio="none">
                {{-- Grid lines --}}
                @foreach([50,100,150,200,250] as $y)
                    <line x1="0" y1="{{ $y }}" x2="1200" y2="{{ $y }}" stroke="#edf0f4" stroke-width="1"/>
                @endforeach

                {{-- Bar area (blue fill) --}}
                <path d="M 0 211 L 16.2 211 L 32.4 194 L 48.6 194 L 64.9 150 L 81.1 127 L 97.3 193 L 113.5 124 L 129.7 216 L 145.9 172 L 162.2 135 L 178.4 177 L 194.6 128 L 210.8 201 L 227 204 L 243.2 204 L 259.5 104 L 275.7 136 L 291.9 216 L 308.1 204 L 324.3 194 L 340.5 194 L 356.8 127 L 373 218 L 389.2 113 L 405.4 150 L 421.6 106 L 437.8 128 L 454.1 161 L 470.3 211 L 486.5 106 L 502.7 107 L 518.9 75 L 535.1 177 L 551.4 205 L 567.6 172 L 583.8 143 L 600 150 L 616.2 216 L 632.4 222 L 648.6 80 L 664.9 139 L 681.1 115 L 697.3 128 L 713.5 161 L 729.7 218 L 745.9 161 L 762.2 150 L 778.4 204 L 794.6 113 L 810.8 148 L 827 204 L 843.2 113 L 859.5 150 L 875.7 216 L 891.9 113 L 908.1 128 L 924.3 136 L 940.5 93 L 956.8 204 L 973 113 L 989.2 150 L 1005.4 150 L 1021.6 113 L 1037.8 150 L 1054.1 150 L 1070.3 59 L 1086.5 107 L 1102.7 59 L 1118.9 106 L 1135.1 84 L 1151.4 113 L 1167.6 93 L 1183.8 125 L 1200 128 L 1200 260 L 0 260 Z"
                      fill="rgba(17, 79, 143, 0.08)" stroke="#114f8f" stroke-opacity="0.3" stroke-width="1.5"/>

                {{-- Trend line (yellow) --}}
                <path d="M 0 226 L 16.2 251 L 32.4 210 L 48.6 201 L 64.9 182 L 81.1 207 L 97.3 190 L 113.5 238 L 129.7 226 L 145.9 176 L 162.2 211 L 178.4 190 L 194.6 221 L 210.8 215 L 227 229 L 243.2 224 L 259.5 162 L 275.7 207 L 291.9 232 L 308.1 221 L 324.3 218 L 340.5 207 L 356.8 190 L 373 238 L 389.2 165 L 405.4 224 L 421.6 173 L 437.8 218 L 454.1 201 L 470.3 226 L 486.5 157 L 502.7 146 L 518.9 207 L 535.1 221 L 551.4 182 L 567.6 190 L 583.8 243 L 600 157 L 616.2 182 L 632.4 226 L 648.6 151 L 664.9 173 L 681.1 182 L 697.3 157 L 713.5 162 L 729.7 238 L 745.9 201 L 762.2 218 L 778.4 157 L 794.6 232 L 810.8 204 L 827 226 L 843.2 190 L 859.5 165 L 875.7 221 L 891.9 232 L 908.1 218 L 924.3 204 L 940.5 151 L 956.8 229 L 973 201 L 989.2 224 L 1005.4 146 L 1021.6 232 L 1037.8 213 L 1054.1 232 L 1070.3 229 L 1086.5 151 L 1102.7 218 L 1118.9 207 L 1135.1 162 L 1151.4 176 L 1167.6 213 L 1183.8 218 L 1200 176"
                      fill="none" stroke="#f6c400" stroke-linecap="round" stroke-width="3"/>
            </svg>
        </div>

        <div class="mt-3 grid grid-cols-6 gap-y-2 text-center text-[13px] text-[#7b8394] md:grid-cols-9 lg:grid-cols-[repeat(18,minmax(0,1fr))]">
            @foreach(['Apr 5','Apr 10','Apr 15','Apr 20','Apr 25','Apr 30','May 5','May 10','May 15','May 20','May 25','May 30','Jun 4','Jun 9','Jun 14','Jun 19','Jun 24','Jun 30'] as $label)
                <span>{{ $label }}</span>
            @endforeach
        </div>
    </div>

    {{-- Side cards: Revenue by Category + Customer Activity --}}
    <div class="grid gap-6">

        {{-- Revenue by Category --}}
        <div class="rounded-2xl border border-[#e5e7eb] bg-white p-5 shadow-[0_1px_2px_rgba(16,24,40,0.03)]">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="text-[18px] font-semibold text-[#0b1220]">Revenue by category</h3>
                <span class="cursor-pointer text-[13px] font-medium text-[#2554e8]">View report</span>
            </div>
            <div class="space-y-4">
                @foreach([
                    ['name' => 'TV Spare Parts', 'value' => 68, 'amount' => 'UGX 12.5M'],
                    ['name' => 'Accessories',    'value' => 48, 'amount' => 'UGX 8.1M'],
                    ['name' => 'Repair Tools',   'value' => 36, 'amount' => 'UGX 5.4M'],
                    ['name' => 'Audio Parts',    'value' => 24, 'amount' => 'UGX 3.2M'],
                ] as $cat)
                    <div>
                        <div class="mb-1 flex items-center justify-between gap-3">
                            <span class="text-[14px] font-medium text-[#111827]">{{ $cat['name'] }}</span>
                            <span class="text-[13px] text-[#6b7280]">{{ $cat['amount'] }}</span>
                        </div>
                        <div class="h-2.5 rounded-full bg-[#eef2f7]">
                            <div class="h-2.5 rounded-full bg-[#4f8cff]" style="width:{{ $cat['value'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Customer Activity --}}
        <div class="rounded-2xl border border-[#e5e7eb] bg-white p-5 shadow-[0_1px_2px_rgba(16,24,40,0.03)]">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="text-[18px] font-semibold text-[#0b1220]">Customer activity</h3>
                <span class="cursor-pointer text-[13px] font-medium text-[#2554e8]">See all</span>
            </div>
            <div class="space-y-4">
                @foreach([
                    ['customer' => 'Kevin M.',  'action' => 'placed an order for TDA2822M IC',    'time' => '5 min ago'],
                    ['customer' => 'Ruth A.',   'action' => 'added 3 products to cart',            'time' => '12 min ago'],
                    ['customer' => 'Paul K.',   'action' => 'completed checkout',                  'time' => '20 min ago'],
                    ['customer' => 'Janet N.',  'action' => 'requested return for TV remote',      'time' => '1 hour ago'],
                    ['customer' => 'Emma S.',   'action' => 'left a 5-star review',                'time' => '2 hours ago'],
                ] as $activity)
                    <div class="flex items-start gap-3">
                        <div class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#eef3ff] text-[#2554e8]">
                            <i data-lucide="users" class="h-4 w-4"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[14px] text-[#111827]">
                                <span class="font-semibold">{{ $activity['customer'] }}</span>
                                <span class="text-[#4b5563]"> {{ $activity['action'] }}</span>
                            </p>
                            <p class="mt-1 text-[12px] text-[#9ca3af]">{{ $activity['time'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</div>


{{-- ═══════════════════════════════════════════════
     RECENT UPLOADS TABLE  +  SIDE CARDS
════════════════════════════════════════════════ --}}
<div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(340px,0.9fr)]">

    {{-- Recent Uploads Table --}}
    <div class="overflow-hidden rounded-2xl border border-[#e5e7eb] bg-white shadow-[0_1px_2px_rgba(16,24,40,0.03)]">
        <div class="flex items-center justify-between gap-3 border-b border-[#eef1f4] px-5 py-4">
            <div>
                <h3 class="text-[18px] font-semibold text-[#0b1220]">Recent uploads</h3>
                <p class="text-[13px] text-[#7b8394]">Newly added products from your catalog</p>
            </div>
            <a href="#"
               class="inline-flex items-center gap-2 rounded-xl border border-[#e5e7eb] bg-white px-3 py-2 text-[14px] font-medium text-[#111827]">
                <i data-lucide="eye" class="h-4 w-4"></i>
                View all
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[880px]">
                <thead>
                    <tr class="bg-[#fbfbfc] text-left">
                        <th class="px-5 py-3 text-[13px] font-medium text-[#6b7280]">Product</th>
                        <th class="px-5 py-3 text-[13px] font-medium text-[#6b7280]">Category</th>
                        <th class="px-5 py-3 text-[13px] font-medium text-[#6b7280]">Price</th>
                        <th class="px-5 py-3 text-[13px] font-medium text-[#6b7280]">Status</th>
                        <th class="px-5 py-3 text-[13px] font-medium text-[#6b7280]">Added</th>
                        <th class="px-5 py-3 text-[13px] font-medium text-[#6b7280]"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentUploads as $i => $row)
                        <tr class="{{ !$loop->last ? 'border-t border-[#eef1f4]' : '' }}">
                            <td class="px-5 py-4 text-[14px] font-semibold text-[#111827]">{{ $row['name'] }}</td>
                            <td class="px-5 py-4 text-[14px] text-[#111827]">{{ $row['category'] }}</td>
                            <td class="px-5 py-4 text-[14px] font-medium text-[#111827]">{{ $row['price'] }}</td>
                            <td class="px-5 py-4">
                                @if($row['status'] === 'Published')
                                    <span class="rounded-full border border-[#bbf7d0] bg-[#f0fdf4] px-2.5 py-1 text-[12px] font-medium text-[#16a34a]">Published</span>
                                @else
                                    <span class="rounded-full border border-[#fde68a] bg-[#fffbeb] px-2.5 py-1 text-[12px] font-medium text-[#ca8a04]">Draft</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-[14px] text-[#6b7280]">{{ $row['createdAt'] }}</td>
                            <td class="px-5 py-4 text-right">
                                <a href="#" class="inline-flex items-center gap-2 text-[13px] font-medium text-[#2554e8] hover:underline">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-[14px] text-[#7b8394]">
                                No uploaded products found yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Low Stock + Recent Catalog Status --}}
    <div class="grid gap-6">

        {{-- Low Stock Products --}}
        <div class="rounded-2xl border border-[#e5e7eb] bg-white p-5 shadow-[0_1px_2px_rgba(16,24,40,0.03)]">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="text-[18px] font-semibold text-[#0b1220]">Low stock products</h3>
                <span class="cursor-pointer text-[13px] font-medium text-[#2554e8]">Restock</span>
            </div>
            <div class="space-y-4">
                @forelse($lowStockProducts as $item)
                    <div class="flex items-center justify-between gap-3 rounded-2xl border border-[#eef1f4] p-3">
                        <div class="min-w-0">
                            <div class="truncate text-[14px] font-medium text-[#111827]">{{ $item['name'] }}</div>
                            <div class="mt-1 text-[12px] text-[#7b8394]">Category: {{ $item['category'] }}</div>
                        </div>
                        <div class="shrink-0 text-right">
                            <div class="mb-1 text-[14px] font-semibold text-[#111827]">{{ $item['stock'] }} left</div>
                            @if($item['status'] === 'Critical')
                                <span class="rounded-full border border-[#fecaca] bg-[#fef2f2] px-2.5 py-1 text-[12px] font-medium text-[#dc2626]">Critical</span>
                            @else
                                <span class="rounded-full border border-[#fde68a] bg-[#fffbeb] px-2.5 py-1 text-[12px] font-medium text-[#ca8a04]">Low</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-[#d7dce3] px-4 py-6 text-center text-[14px] text-[#7b8394]">
                        No low-stock products right now.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Catalog Status --}}
        <div class="rounded-2xl border border-[#e5e7eb] bg-white p-5 shadow-[0_1px_2px_rgba(16,24,40,0.03)]">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="text-[18px] font-semibold text-[#0b1220]">Recent catalog status</h3>
                <span class="cursor-pointer text-[13px] font-medium text-[#2554e8]">Manage products</span>
            </div>
            <div class="space-y-4">
                @forelse($recentUploads as $index => $item)
                    <div class="flex items-center justify-between gap-3 rounded-2xl border border-[#eef1f4] p-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#111827] text-[14px] font-semibold text-white">
                                {{ $loop->iteration }}
                            </div>
                            <div class="min-w-0">
                                <div class="truncate text-[14px] font-medium text-[#111827]">{{ $item['name'] }}</div>
                                <div class="mt-1 text-[12px] text-[#7b8394]">{{ $item['category'] }}</div>
                            </div>
                        </div>
                        <div class="shrink-0 text-right">
                            <div class="text-[14px] font-semibold text-[#111827]">{{ $item['price'] }}</div>
                            <div class="mt-1">
                                @if($item['status'] === 'Published')
                                    <span class="rounded-full border border-[#bbf7d0] bg-[#f0fdf4] px-2.5 py-1 text-[12px] font-medium text-[#16a34a]">Published</span>
                                @else
                                    <span class="rounded-full border border-[#fde68a] bg-[#fffbeb] px-2.5 py-1 text-[12px] font-medium text-[#ca8a04]">Draft</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-[#d7dce3] px-4 py-6 text-center text-[14px] text-[#7b8394]">
                        Upload products to see them here.
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>


{{-- ═══════════════════════════════════════════════
     BOTTOM METRICS ROW
════════════════════════════════════════════════ --}}
<div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-3">

    {{-- Pending Shipments --}}
    <div class="rounded-2xl border border-[#e5e7eb] bg-white p-5 shadow-[0_1px_2px_rgba(16,24,40,0.03)]">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h3 class="text-[18px] font-semibold text-[#0b1220]">Pending shipments</h3>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#eff6ff] text-[#2563eb]">
                <i data-lucide="truck" class="h-5 w-5"></i>
            </div>
            <div>
                <div class="text-[28px] font-semibold text-[#111827]">48</div>
                <div class="text-[13px] text-[#7b8394]">Orders waiting for dispatch</div>
            </div>
        </div>
    </div>

    {{-- Returns Requested --}}
    <div class="rounded-2xl border border-[#e5e7eb] bg-white p-5 shadow-[0_1px_2px_rgba(16,24,40,0.03)]">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h3 class="text-[18px] font-semibold text-[#0b1220]">Returns requested</h3>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#fff7ed] text-[#ea580c]">
                <i data-lucide="refresh-ccw" class="h-5 w-5"></i>
            </div>
            <div>
                <div class="text-[28px] font-semibold text-[#111827]">12</div>
                <div class="text-[13px] text-[#7b8394]">Awaiting review and approval</div>
            </div>
        </div>
    </div>

    {{-- Products to Review --}}
    <div class="rounded-2xl border border-[#e5e7eb] bg-white p-5 shadow-[0_1px_2px_rgba(16,24,40,0.03)]">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h3 class="text-[18px] font-semibold text-[#0b1220]">Products to review</h3>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#fef2f2] text-[#dc2626]">
                <i data-lucide="alert-triangle" class="h-5 w-5"></i>
            </div>
            <div>
                <div class="text-[28px] font-semibold text-[#111827]">{{ $productsToReview }}</div>
                <div class="text-[13px] text-[#7b8394]">Missing images or usable pricing</div>
            </div>
        </div>
    </div>

</div>

@endsection
