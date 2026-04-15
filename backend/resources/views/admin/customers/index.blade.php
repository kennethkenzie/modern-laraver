@extends('admin.layout')

@section('title', 'Customers')

@section('content')
<div class="bg-[#f7f7f8] -mx-4 -mt-8 min-h-screen px-4 py-8 md:-mx-6 md:px-6 xl:-mx-10 xl:px-10">
    <div class="mx-auto max-w-[1440px]">

        {{-- Header --}}
        <div class="mb-8 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex items-start gap-4">
                <span class="mt-3 h-3 w-10 shrink-0 rounded-full bg-[#0b63ce]"></span>
                <div>
                    <h1 class="text-[32px] font-bold tracking-tight text-gray-900">Customers</h1>
                    <p class="mt-1.5 text-[16px] font-medium text-gray-500">All registered accounts on the storefront.</p>
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl bg-gradient-to-br from-[#111827] to-[#1f2937] p-5 text-white shadow">
                <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-white/70">Total Customers</div>
                <div class="mt-3 text-[30px] font-bold tracking-tight">{{ number_format($totalCustomers) }}</div>
            </div>
            <div class="rounded-xl bg-gradient-to-br from-[#065f46] to-[#10b981] p-5 text-white shadow">
                <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-white/70">New This Month</div>
                <div class="mt-3 text-[30px] font-bold tracking-tight">{{ number_format($newThisMonth) }}</div>
            </div>
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('dashboard.customers') }}" class="mb-6">
            <div class="flex gap-3">
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Search by name, email or phone…"
                       class="w-full max-w-sm rounded-xl border border-[#dbe3ec] bg-white px-4 py-2.5 text-[15px] text-gray-700 shadow-sm outline-none focus:border-[#0b63ce] focus:ring-2 focus:ring-[#0b63ce]/20" />
                <button type="submit"
                        class="rounded-xl bg-[#0b63ce] px-5 py-2.5 text-[14px] font-semibold text-white shadow hover:bg-[#0952b3] transition">
                    Search
                </button>
                @if ($search)
                    <a href="{{ route('dashboard.customers') }}"
                       class="rounded-xl border border-[#dbe3ec] bg-white px-5 py-2.5 text-[14px] font-semibold text-gray-600 shadow hover:bg-gray-50 transition">
                        Clear
                    </a>
                @endif
            </div>
        </form>

        {{-- Table --}}
        <div class="overflow-hidden rounded-2xl border border-[#dbe3ec] bg-white shadow-sm">
            <table class="w-full text-left text-[14px]">
                <thead class="border-b border-[#dbe3ec] bg-[#f7f9fc]">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold text-gray-500">#</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-500">Name</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-500">Email</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-500">Phone</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-500">Role</th>
                        <th class="px-5 py-3.5 font-semibold text-gray-500">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0f4f8]">
                    @forelse ($customers as $index => $customer)
                        <tr class="hover:bg-[#f7f9fc] transition">
                            <td class="px-5 py-4 text-gray-400 font-mono text-[13px]">
                                {{ $customers->firstItem() + $index }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if ($customer->avatar_url)
                                        <img src="{{ $customer->avatar_url }}"
                                             alt="{{ $customer->full_name }}"
                                             class="h-9 w-9 rounded-full object-cover ring-2 ring-[#dbe3ec]" />
                                    @else
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#e8f0fe] text-[14px] font-bold text-[#0b63ce]">
                                            {{ strtoupper(substr($customer->full_name ?? 'U', 0, 1)) }}
                                        </div>
                                    @endif
                                    <span class="font-medium text-gray-900">{{ $customer->full_name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-gray-700">{{ $customer->email }}</td>
                            <td class="px-5 py-4 text-gray-500">{{ $customer->phone ?? '—' }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide
                                    {{ $customer->role === 'admin' ? 'bg-[#fef3c7] text-[#92400e]' : 'bg-[#ecfdf5] text-[#065f46]' }}">
                                    {{ ucfirst($customer->role ?? 'customer') }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-gray-500">
                                {{ $customer->created_at->format('M j, Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center text-gray-400">
                                <i data-lucide="users" class="mx-auto mb-3 h-10 w-10 text-gray-300"></i>
                                <p class="text-[15px]">No customers found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            @if ($customers->hasPages())
                <div class="border-t border-[#dbe3ec] px-5 py-4">
                    {{ $customers->links() }}
                </div>
            @endif
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
</script>
@endsection
