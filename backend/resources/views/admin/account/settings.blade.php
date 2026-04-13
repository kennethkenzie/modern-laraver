@extends('admin.layout')

@section('title', 'Account Settings')

@section('content')
    <div class="space-y-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.25em] text-[#114f8f]">Account</p>
                <h1 class="mt-2 text-[34px] font-black uppercase tracking-tight text-[#111827]">Account Settings</h1>
                <p class="mt-3 max-w-2xl text-sm font-medium leading-6 text-gray-500">
                    Change your dashboard password and review how your account is currently configured.
                </p>
            </div>

            @if (session('status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-[12px] font-black uppercase tracking-[0.16em] text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                @if ($errors->any())
                    <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-600">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('dashboard.account.settings.password') }}" class="space-y-5">
                    @csrf
                    @method('PATCH')

                    <label class="block">
                        <span class="mb-2 block text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Current Password</span>
                        <input type="password" name="current_password" class="h-12 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 text-sm font-semibold text-[#111827] outline-none transition focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50">
                    </label>

                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="block">
                            <span class="mb-2 block text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">New Password</span>
                            <input type="password" name="password" class="h-12 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 text-sm font-semibold text-[#111827] outline-none transition focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50">
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Confirm Password</span>
                            <input type="password" name="password_confirmation" class="h-12 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 text-sm font-semibold text-[#111827] outline-none transition focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50">
                        </label>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex h-12 items-center justify-center rounded-xl bg-[#114f8f] px-5 text-[13px] font-black uppercase tracking-[0.15em] text-white transition hover:bg-[#0d3f74]">
                            Update Password
                        </button>
                    </div>
                </form>
            </section>

            <aside class="space-y-6">
                <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-black uppercase tracking-tight text-[#111827]">Account Summary</h2>
                    <div class="mt-5 space-y-4">
                        <div class="rounded-xl border border-gray-200 bg-[#f8fbff] px-4 py-3">
                            <div class="text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Full Name</div>
                            <div class="mt-2 text-sm font-bold text-[#111827]">{{ $account->full_name }}</div>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-[#f8fbff] px-4 py-3">
                            <div class="text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Email</div>
                            <div class="mt-2 text-sm font-bold text-[#111827]">{{ $account->email }}</div>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-[#f8fbff] px-4 py-3">
                            <div class="text-[11px] font-black uppercase tracking-[0.18em] text-gray-400">Role</div>
                            <div class="mt-2 text-sm font-bold uppercase text-[#111827]">{{ $account->role }}</div>
                        </div>
                    </div>
                </article>

                <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-black uppercase tracking-tight text-[#111827]">Quick Actions</h2>
                    <div class="mt-5 space-y-3">
                        <a href="{{ route('dashboard.account.profile') }}" class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-bold text-[#111827] transition hover:bg-gray-100">
                            <span>Back to Profile</span>
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
                </article>
            </aside>
        </div>
    </div>
@endsection
