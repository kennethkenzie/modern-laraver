@extends('admin.layout')

@section('title', 'Messages')

@section('content')
<div class="bg-[#f7f7f8] -mx-4 -mt-8 min-h-screen px-4 py-8 md:-mx-6 md:px-6 xl:-mx-10 xl:px-10"
     x-data="messagesApp()">
    <div class="mx-auto max-w-[1440px]">

        {{-- Header --}}
        <div class="mb-8 flex items-start gap-4">
            <span class="mt-3 h-3 w-10 shrink-0 rounded-full bg-[#f6c400]"></span>
            <div>
                <h1 class="text-[32px] font-bold tracking-tight text-gray-900">Messages</h1>
                <p class="mt-1.5 text-[16px] font-medium text-gray-500">Contact form submissions from visitors.</p>
            </div>
        </div>

        {{-- Stats --}}
        <div class="mb-8 grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl bg-gradient-to-br from-[#111827] to-[#1f2937] p-5 text-white shadow">
                <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-white/70">Total</div>
                <div class="mt-3 text-[30px] font-bold">{{ number_format($totalMessages) }}</div>
            </div>
            <div class="rounded-xl bg-gradient-to-br from-[#7c3aed] to-[#a78bfa] p-5 text-white shadow">
                <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-white/70">Unread</div>
                <div class="mt-3 text-[30px] font-bold">{{ number_format($unreadMessages) }}</div>
            </div>
            <div class="rounded-xl bg-gradient-to-br from-[#065f46] to-[#10b981] p-5 text-white shadow">
                <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-white/70">Replied</div>
                <div class="mt-3 text-[30px] font-bold">{{ number_format($repliedMessages) }}</div>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('dashboard.messages') }}" class="mb-6 flex flex-wrap gap-3">
            <input type="text"
                   name="search"
                   value="{{ $search }}"
                   placeholder="Search name, email, subject…"
                   class="w-full max-w-xs rounded-xl border border-[#dbe3ec] bg-white px-4 py-2.5 text-[15px] text-gray-700 shadow-sm outline-none focus:border-[#0b63ce] focus:ring-2 focus:ring-[#0b63ce]/20" />

            <select name="status"
                    class="rounded-xl border border-[#dbe3ec] bg-white px-4 py-2.5 text-[15px] text-gray-700 shadow-sm outline-none focus:border-[#0b63ce]">
                <option value="" {{ $status === '' ? 'selected' : '' }}>All Status</option>
                <option value="unread" {{ $status === 'unread' ? 'selected' : '' }}>Unread</option>
                <option value="read" {{ $status === 'read' ? 'selected' : '' }}>Read</option>
                <option value="replied" {{ $status === 'replied' ? 'selected' : '' }}>Replied</option>
            </select>

            <button type="submit"
                    class="rounded-xl bg-[#0b63ce] px-5 py-2.5 text-[14px] font-semibold text-white shadow hover:bg-[#0952b3] transition">
                Filter
            </button>
            @if ($search || $status)
                <a href="{{ route('dashboard.messages') }}"
                   class="rounded-xl border border-[#dbe3ec] bg-white px-5 py-2.5 text-[14px] font-semibold text-gray-600 shadow hover:bg-gray-50 transition">
                    Clear
                </a>
            @endif
        </form>

        {{-- Messages List --}}
        <div class="space-y-4">
            @forelse ($messages as $msg)
                <div class="rounded-2xl border bg-white shadow-sm transition hover:shadow-md
                            {{ $msg->status === 'unread' ? 'border-[#0b63ce]/30 bg-[#f0f7ff]' : 'border-[#dbe3ec]' }}"
                     x-data="{ open: false }">

                    {{-- Collapsed header --}}
                    <button @click="open = !open; markRead({{ $msg->id }}, '{{ $msg->status }}')"
                            class="flex w-full items-center justify-between gap-4 px-6 py-4 text-left">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#e8f0fe] text-[15px] font-bold text-[#0b63ce]">
                                {{ strtoupper(substr($msg->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-semibold text-gray-900">{{ $msg->name }}</span>
                                    @if ($msg->status === 'unread')
                                        <span class="rounded-full bg-[#0b63ce] px-2 py-0.5 text-[10px] font-black uppercase text-white">New</span>
                                    @endif
                                </div>
                                <div class="truncate text-[13px] text-gray-500">
                                    {{ $msg->email }}
                                    @if ($msg->subject) — <span class="text-gray-700">{{ $msg->subject }}</span>@endif
                                </div>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            <span class="hidden text-[12px] text-gray-400 xl:block">
                                {{ $msg->created_at->format('M j, Y g:i A') }}
                            </span>
                            <span class="rounded-full px-2.5 py-1 text-[11px] font-bold uppercase
                                {{ $msg->status === 'unread' ? 'bg-[#eff6ff] text-[#0b63ce]' : ($msg->status === 'replied' ? 'bg-[#ecfdf5] text-[#065f46]' : 'bg-gray-100 text-gray-500') }}">
                                {{ ucfirst($msg->status) }}
                            </span>
                            <i data-lucide="chevron-down" class="h-4 w-4 text-gray-400 transition-transform duration-200"
                               :class="open ? 'rotate-180' : ''"></i>
                        </div>
                    </button>

                    {{-- Expanded body --}}
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-cloak
                         class="border-t border-[#dbe3ec] px-6 py-5">
                        <div class="mb-3 grid gap-2 text-[13px] text-gray-500 sm:grid-cols-3">
                            <span><span class="font-semibold text-gray-700">Email:</span> {{ $msg->email }}</span>
                            <span><span class="font-semibold text-gray-700">Phone:</span> {{ $msg->phone ?? '—' }}</span>
                            <span><span class="font-semibold text-gray-700">Received:</span> {{ $msg->created_at->format('M j, Y g:i A') }}</span>
                        </div>
                        <p class="whitespace-pre-line text-[15px] text-gray-800 leading-relaxed">{{ $msg->message }}</p>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="mailto:{{ $msg->email }}?subject=Re: {{ urlencode($msg->subject ?? 'Your enquiry') }}"
                               @click="updateStatus({{ $msg->id }}, 'replied')"
                               class="rounded-lg bg-[#0b63ce] px-4 py-2 text-[13px] font-semibold text-white hover:bg-[#0952b3] transition">
                                Reply via Email
                            </a>
                            <button @click="updateStatus({{ $msg->id }}, 'replied')"
                                    class="rounded-lg border border-[#dbe3ec] bg-white px-4 py-2 text-[13px] font-semibold text-gray-700 hover:bg-gray-50 transition">
                                Mark as Replied
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-[#dbe3ec] bg-white px-6 py-16 text-center shadow-sm">
                    <i data-lucide="message-square" class="mx-auto mb-3 h-10 w-10 text-gray-300"></i>
                    <p class="text-[15px] text-gray-400">No messages found.</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($messages->hasPages())
            <div class="mt-6">
                {{ $messages->links() }}
            </div>
        @endif

    </div>
</div>

<script>
    function messagesApp() {
        return {
            markRead(id, currentStatus) {
                if (currentStatus !== 'unread') return;
                fetch(`/dashboard/messages/${id}/read`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                        'Content-Type': 'application/json',
                    },
                });
            },
            updateStatus(id, status) {
                fetch(`/dashboard/messages/${id}/status`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ status }),
                });
            },
        };
    }

    document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
</script>
@endsection
