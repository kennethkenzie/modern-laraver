@extends('admin.layout')

@section('title', 'My Profile')

@section('content')
    <div class="space-y-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.25em] text-[#114f8f]">Account</p>
                <h1 class="mt-2 text-[34px] font-black uppercase tracking-tight text-[#111827]">My Profile</h1>
                <p class="mt-3 max-w-2xl text-sm font-medium leading-6 text-gray-500">
                    Manage your dashboard identity, contact details, and avatar used across the admin interface.
                </p>
            </div>

            @if (session('status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-[12px] font-black uppercase tracking-[0.16em] text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.2fr_0.85fr]">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                @if ($errors->any())
                    <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-600">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('dashboard.account.profile.update') }}" class="space-y-5">
                    @csrf
                    @method('PATCH')

                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="block">
                            <span class="mb-2 block text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Full Name</span>
                            <input type="text" name="full_name" value="{{ old('full_name', $account->full_name) }}" class="h-12 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 text-sm font-semibold text-[#111827] outline-none transition focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50">
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Email Address</span>
                            <input type="email" name="email" value="{{ old('email', $account->email) }}" class="h-12 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 text-sm font-semibold text-[#111827] outline-none transition focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50">
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Phone</span>
                            <input type="text" name="phone" value="{{ old('phone', $account->phone) }}" class="h-12 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 text-sm font-semibold text-[#111827] outline-none transition focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50">
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Role</span>
                            <input type="text" value="{{ strtoupper($account->role) }}" disabled class="h-12 w-full rounded-xl border border-gray-200 bg-gray-100 px-4 text-sm font-black tracking-[0.12em] text-gray-500 outline-none">
                        </label>
                    </div>

                    <label class="block">
                        <span class="mb-2 block text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Avatar URL</span>
                        <input type="text" name="avatar_url" value="{{ old('avatar_url', $account->avatar_url) }}" placeholder="https://..." class="h-12 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 text-sm font-semibold text-[#111827] outline-none transition focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50">
                    </label>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex h-12 items-center justify-center rounded-xl bg-[#114f8f] px-5 text-[13px] font-black uppercase tracking-[0.15em] text-white transition hover:bg-[#0d3f74]">
                            Save Profile
                        </button>
                    </div>
                </form>
            </section>

            <aside class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black uppercase tracking-tight text-[#111827]">Profile Card</h2>
                <p class="mt-1 text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Header dropdown preview</p>

                <div class="mt-6 rounded-2xl border border-gray-200 bg-[#f8fbff] p-6">
                    @if ($account->avatar_url)
                        <img src="{{ $account->avatar_url }}" alt="{{ $account->full_name }}" class="h-20 w-20 rounded-2xl object-cover shadow-sm">
                    @else
                        <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-[#114f8f] text-2xl font-black uppercase text-white shadow-sm">
                            {{ strtoupper(substr($account->full_name ?: 'A', 0, 1)) }}
                        </div>
                    @endif

                    <div class="mt-5 text-xl font-black uppercase tracking-tight text-[#111827]">{{ $account->full_name }}</div>
                    <div class="mt-2 text-sm font-semibold text-gray-500">{{ $account->email }}</div>
                    <div class="mt-2 inline-flex rounded-full bg-[#114f8f]/10 px-3 py-1 text-[11px] font-black uppercase tracking-[0.14em] text-[#114f8f]">
                        {{ $account->role }}
                    </div>
                </div>

                <div class="mt-5 space-y-3">
                    <a href="{{ route('dashboard.account.settings') }}" class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-bold text-[#111827] transition hover:bg-gray-100">
                        <span>Account Settings</span>
                        <i data-lucide="chevron-right" class="h-4 w-4 text-gray-400"></i>
                    </a>
                    <form method="POST" action="{{ route('web.logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center justify-between rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-bold text-red-600 transition hover:bg-red-100">
                            <span>Sign Out</span>
                            <i data-lucide="log-out" class="h-4 w-4"></i>
                        </button>
                    </form>
                </div>
            </aside>
        </div>
    </div>
@endsection
