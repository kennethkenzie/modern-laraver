<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign In — Modern Electronics Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Amazon Ember"','Arial','Helvetica','sans-serif'],
                        display: ['"Amazon Ember Display"','"Amazon Ember"','Arial','Helvetica','sans-serif'],
                    },
                },
            },
        };
    </script>
    <style>
        @font-face {
            font-family: "Amazon Ember";
            src: url("{{ asset('fonts/AmazonEmber_Rg.ttf') }}") format("truetype");
            font-weight: 400; font-style: normal; font-display: swap;
        }
        @font-face {
            font-family: "Amazon Ember";
            src: url("{{ asset('fonts/Amazon-Ember-Medium.ttf') }}") format("truetype");
            font-weight: 500; font-style: normal; font-display: swap;
        }
        @font-face {
            font-family: "Amazon Ember";
            src: url("{{ asset('fonts/AmazonEmber_Bd.ttf') }}") format("truetype");
            font-weight: 700; font-style: normal; font-display: swap;
        }
        @font-face {
            font-family: "Amazon Ember Display";
            src: url("{{ asset('fonts/AmazonEmberDisplay_Rg.ttf') }}") format("truetype");
            font-weight: 400; font-style: normal; font-display: swap;
        }
        @font-face {
            font-family: "Amazon Ember Display";
            src: url("{{ asset('fonts/AmazonEmberDisplay_Bd.ttf') }}") format("truetype");
            font-weight: 700; font-style: normal; font-display: swap;
        }

        [x-cloak] { display: none !important; }

        /* Keep autofill readable on white background */
        input:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 100px #fff inset;
            -webkit-text-fill-color: #0f1111;
        }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>

{{-- Page bg = frontend's #eaeded on mobile; on lg the split handles its own colours --}}
<body class="h-full bg-[#eaeded] antialiased"
      style="font-family: 'Amazon Ember', Arial, Helvetica, sans-serif;"
      x-data="authPage()"
      x-init="init()"
      data-initial-mode="{{ $errors->hasBag('register') ? 'register' : 'login' }}">

<div class="min-h-screen flex items-stretch lg:items-center justify-center lg:p-8">

    {{-- ── Shell: full-screen on mobile, capped card on desktop ── --}}
    <div class="w-full lg:max-w-[1060px] lg:min-h-0 min-h-screen
                flex flex-col lg:grid lg:grid-cols-[1.15fr_0.85fr]
                lg:overflow-hidden lg:rounded-[24px] lg:shadow-[0_24px_80px_rgba(15,17,17,0.18)]">

        {{-- ════════════════════════════════════════════════════════
             LEFT — full-bleed image (desktop only)
             Place your photo at public/images/login-bg.jpg
             and swap the src to {{ asset('images/login-bg.jpg') }}
        ════════════════════════════════════════════════════════ --}}
        <div class="hidden lg:block relative overflow-hidden">
            <img src="https://images.unsplash.com/photo-1550009158-9ebf69173e03?w=900&auto=format&fit=crop&q=80"
                 alt=""
                 class="absolute inset-0 h-full w-full object-cover object-center" />

            {{-- gradient: heavier at bottom for legibility --}}
            <div class="absolute inset-0"
                 style="background: linear-gradient(to top,
                            rgba(9,17,31,.90) 0%,
                            rgba(9,17,31,.28) 52%,
                            rgba(9,17,31,.18) 100%);"></div>

            <div class="relative h-full flex flex-col justify-between p-10">
                {{-- Brand mark --}}
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl flex items-center justify-center
                                font-black text-[#09111f] text-sm shadow-lg"
                         style="background: linear-gradient(135deg, #f6c400, #f97316);">ME</div>
                    <div>
                        <p class="font-black text-white leading-none drop-shadow">Modern Electronics</p>
                        <p class="text-[11px] font-bold uppercase tracking-[.2em] text-white/50 mt-1">Admin Portal</p>
                    </div>
                </div>

                {{-- Tagline at bottom --}}
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full px-4 py-2 mb-5
                                 text-[11px] font-black uppercase tracking-[.22em] text-white/70
                                 border border-white/15 backdrop-blur-sm bg-white/5">
                        <i data-lucide="shield-check" class="h-3.5 w-3.5 text-[#f6c400]"></i>
                        Secure Access Portal
                    </span>
                    <h1 class="text-[42px] font-black uppercase leading-[.95] tracking-tight
                               text-white drop-shadow-lg"
                        style="font-family: 'Amazon Ember Display', 'Amazon Ember', Arial, sans-serif;">
                        Control<br>the<br>storefront.
                    </h1>
                    <p class="mt-4 text-[14px] leading-[1.75] text-white/55 max-w-xs">
                        Manage products, orders, shipping, and storefront content from one dashboard.
                    </p>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════
             RIGHT — auth panel, styled after the frontend login
        ════════════════════════════════════════════════════════ --}}
        <div class="flex flex-col flex-1 bg-[#eaeded] px-4 py-10 lg:px-0 lg:py-0 lg:overflow-y-auto">
            <div class="w-full max-w-[400px] mx-auto lg:my-auto lg:py-10">

                {{-- ── Brand header (mirrors frontend) ── --}}
                <div class="mb-6 text-center">
                    <div class="text-[26px] font-black tracking-tight text-[#0f1111]"
                         style="font-family: 'Amazon Ember Display', 'Amazon Ember', Arial, sans-serif;">
                        Modern Electronics
                    </div>
                    <p class="mt-2 text-[13px] text-[#565959]"
                       x-text="mode === 'login'
                           ? 'Secure sign in for admin and staff accounts.'
                           : 'Create a new admin or staff account.'"></p>
                </div>

                {{-- ── White card (mirrors frontend card) ── --}}
                <div class="rounded-[18px] border border-[#d5d9d9] bg-white px-6 py-6
                            shadow-[0_8px_24px_rgba(15,17,17,0.12)]">

                    {{-- Tab switcher --}}
                    <div class="grid grid-cols-2 gap-2 mb-6">
                        <button type="button" @click="setMode('login')"
                                :class="mode === 'login'
                                    ? 'border-[#fcd200] bg-[#ffd814] text-[#0f1111] shadow-[inset_0_-1px_0_rgba(0,0,0,0.15)]'
                                    : 'border-[#d5d9d9] bg-[#f7fafa] text-[#565959] shadow-[inset_0_-1px_0_rgba(0,0,0,0.08)] hover:bg-[#eef3f3]'"
                                class="h-10 rounded-full border text-[13px] font-medium transition-all">
                            Sign In
                        </button>
                        <button type="button" @click="setMode('register')"
                                :class="mode === 'register'
                                    ? 'border-[#fcd200] bg-[#ffd814] text-[#0f1111] shadow-[inset_0_-1px_0_rgba(0,0,0,0.15)]'
                                    : 'border-[#d5d9d9] bg-[#f7fafa] text-[#565959] shadow-[inset_0_-1px_0_rgba(0,0,0,0.08)] hover:bg-[#eef3f3]'"
                                class="h-10 rounded-full border text-[13px] font-medium transition-all">
                            Register
                        </button>
                    </div>

                    {{-- Heading --}}
                    <h1 class="text-[28px] font-normal leading-none text-[#0f1111]"
                        style="font-family: 'Amazon Ember Display', 'Amazon Ember', Arial, sans-serif;"
                        x-text="mode === 'login' ? 'Sign in' : 'Create account'"></h1>
                    <p class="mt-3 text-[14px] leading-6 text-[#565959]"
                       x-text="mode === 'login'
                           ? 'Enter your email and password to access the dashboard.'
                           : 'Set up your admin account. New accounts are signed in immediately.'"></p>

                    {{-- Error / session alerts --}}
                    @if ($errors->any() && ! $errors->hasBag('register'))
                        <p class="mt-4 text-[13px] text-[#b12704]">{{ $errors->first() }}</p>
                    @endif
                    @if ($errors->hasBag('register'))
                        <p class="mt-4 text-[13px] text-[#b12704]">{{ $errors->register->first() }}</p>
                    @endif
                    @if (session('session_expired'))
                        <p class="mt-4 text-[13px] text-[#b12704]">{{ session('session_expired') }}</p>
                    @endif

                    {{-- ══ LOGIN FORM ══════════════════════════════════ --}}
                    <form x-show="mode === 'login'" x-cloak
                          method="POST" action="{{ route('web.login') }}"
                          class="mt-5 space-y-4">
                        @csrf

                        {{-- Email --}}
                        <label class="block">
                            <span class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Email address</span>
                            <div class="flex items-center gap-3 rounded-xl border border-[#a6a6a6] px-3 py-3
                                        shadow-[inset_0_1px_2px_rgba(15,17,17,0.08)]
                                        focus-within:border-[#007185] focus-within:shadow-[inset_0_1px_2px_rgba(15,17,17,0.08),0_0_0_3px_rgba(0,113,133,0.12)]
                                        transition-shadow">
                                <i data-lucide="mail" class="h-[18px] w-[18px] shrink-0 text-[#565959]"></i>
                                <input type="email" name="email" value="{{ old('email') }}"
                                       placeholder="admin@e-modern.ug" autocomplete="username" required
                                       class="w-full bg-transparent text-[15px] text-[#0f1111]
                                              placeholder:text-[#8a8f98] outline-none" />
                            </div>
                        </label>

                        {{-- Password --}}
                        <label class="block">
                            <span class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Password</span>
                            <div class="flex items-center gap-3 rounded-xl border border-[#a6a6a6] px-3 py-3
                                        shadow-[inset_0_1px_2px_rgba(15,17,17,0.08)]
                                        focus-within:border-[#007185] focus-within:shadow-[inset_0_1px_2px_rgba(15,17,17,0.08),0_0_0_3px_rgba(0,113,133,0.12)]
                                        transition-shadow">
                                <i data-lucide="lock" class="h-[18px] w-[18px] shrink-0 text-[#565959]"></i>
                                <input :type="showLoginPw ? 'text' : 'password'" name="password"
                                       placeholder="Your password" autocomplete="current-password" required
                                       class="w-full bg-transparent text-[15px] text-[#0f1111]
                                              placeholder:text-[#8a8f98] outline-none" />
                                <button type="button" @click="showLoginPw = !showLoginPw"
                                        class="text-[12px] font-bold text-[#007185] hover:underline shrink-0"
                                        x-text="showLoginPw ? 'Hide' : 'Show'"></button>
                            </div>
                        </label>

                        <p class="text-[12px] text-[#565959]">Admin and staff accounts only.</p>

                        <button type="submit"
                                class="flex w-full items-center justify-center gap-2 rounded-full
                                       border border-[#fcd200] bg-[#ffd814] px-4 py-3
                                       text-[14px] font-medium text-[#0f1111]
                                       shadow-[inset_0_-1px_0_rgba(0,0,0,0.15)]
                                       hover:bg-[#f7ca00] transition-colors active:scale-[.99]">
                            Sign in
                        </button>
                    </form>

                    {{-- ══ REGISTER FORM ═══════════════════════════════ --}}
                    <form x-show="mode === 'register'" x-cloak
                          method="POST" action="{{ route('web.register') }}"
                          class="mt-5 space-y-4">
                        @csrf

                        {{-- Full name --}}
                        <label class="block">
                            <span class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Full name</span>
                            <div class="flex items-center gap-3 rounded-xl border border-[#a6a6a6] px-3 py-3
                                        shadow-[inset_0_1px_2px_rgba(15,17,17,0.08)]
                                        focus-within:border-[#007185] focus-within:shadow-[inset_0_1px_2px_rgba(15,17,17,0.08),0_0_0_3px_rgba(0,113,133,0.12)]
                                        transition-shadow">
                                <i data-lucide="user" class="h-[18px] w-[18px] shrink-0 text-[#565959]"></i>
                                <input type="text" name="full_name" value="{{ old('full_name') }}"
                                       placeholder="Jane Doe" required
                                       class="w-full bg-transparent text-[15px] text-[#0f1111]
                                              placeholder:text-[#8a8f98] outline-none" />
                            </div>
                        </label>

                        {{-- Email --}}
                        <label class="block">
                            <span class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Email address</span>
                            <div class="flex items-center gap-3 rounded-xl border border-[#a6a6a6] px-3 py-3
                                        shadow-[inset_0_1px_2px_rgba(15,17,17,0.08)]
                                        focus-within:border-[#007185] focus-within:shadow-[inset_0_1px_2px_rgba(15,17,17,0.08),0_0_0_3px_rgba(0,113,133,0.12)]
                                        transition-shadow">
                                <i data-lucide="mail" class="h-[18px] w-[18px] shrink-0 text-[#565959]"></i>
                                <input type="email" name="email" value="{{ old('email') }}"
                                       placeholder="jane@e-modern.ug" required
                                       class="w-full bg-transparent text-[15px] text-[#0f1111]
                                              placeholder:text-[#8a8f98] outline-none" />
                            </div>
                        </label>

                        {{-- Phone --}}
                        <label class="block">
                            <span class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Phone <span class="font-normal text-[#565959]">(optional)</span></span>
                            <div class="flex items-center gap-3 rounded-xl border border-[#a6a6a6] px-3 py-3
                                        shadow-[inset_0_1px_2px_rgba(15,17,17,0.08)]
                                        focus-within:border-[#007185] focus-within:shadow-[inset_0_1px_2px_rgba(15,17,17,0.08),0_0_0_3px_rgba(0,113,133,0.12)]
                                        transition-shadow">
                                <i data-lucide="phone" class="h-[18px] w-[18px] shrink-0 text-[#565959]"></i>
                                <input type="text" name="phone" value="{{ old('phone') }}"
                                       placeholder="+256 700 000000"
                                       class="w-full bg-transparent text-[15px] text-[#0f1111]
                                              placeholder:text-[#8a8f98] outline-none" />
                            </div>
                        </label>

                        {{-- Role --}}
                        <label class="block">
                            <span class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Account role</span>
                            <div class="flex items-center gap-3 rounded-xl border border-[#a6a6a6] px-3 py-3
                                        shadow-[inset_0_1px_2px_rgba(15,17,17,0.08)]
                                        focus-within:border-[#007185] transition-shadow">
                                <i data-lucide="badge" class="h-[18px] w-[18px] shrink-0 text-[#565959]"></i>
                                <select name="role" required
                                        class="w-full bg-transparent text-[15px] text-[#0f1111] outline-none appearance-none cursor-pointer">
                                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Staff</option>
                                </select>
                                <i data-lucide="chevron-down" class="h-4 w-4 shrink-0 text-[#565959] pointer-events-none"></i>
                            </div>
                        </label>

                        {{-- Password --}}
                        <label class="block">
                            <span class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Password</span>
                            <div class="flex items-center gap-3 rounded-xl border border-[#a6a6a6] px-3 py-3
                                        shadow-[inset_0_1px_2px_rgba(15,17,17,0.08)]
                                        focus-within:border-[#007185] focus-within:shadow-[inset_0_1px_2px_rgba(15,17,17,0.08),0_0_0_3px_rgba(0,113,133,0.12)]
                                        transition-shadow">
                                <i data-lucide="lock" class="h-[18px] w-[18px] shrink-0 text-[#565959]"></i>
                                <input :type="showRegPw ? 'text' : 'password'" name="password"
                                       placeholder="Minimum 6 characters" autocomplete="new-password" required
                                       class="w-full bg-transparent text-[15px] text-[#0f1111]
                                              placeholder:text-[#8a8f98] outline-none" />
                                <button type="button" @click="showRegPw = !showRegPw"
                                        class="text-[12px] font-bold text-[#007185] hover:underline shrink-0"
                                        x-text="showRegPw ? 'Hide' : 'Show'"></button>
                            </div>
                        </label>

                        {{-- Confirm password --}}
                        <label class="block">
                            <span class="mb-1.5 block text-[13px] font-bold text-[#0f1111]">Confirm password</span>
                            <div class="flex items-center gap-3 rounded-xl border border-[#a6a6a6] px-3 py-3
                                        shadow-[inset_0_1px_2px_rgba(15,17,17,0.08)]
                                        focus-within:border-[#007185] focus-within:shadow-[inset_0_1px_2px_rgba(15,17,17,0.08),0_0_0_3px_rgba(0,113,133,0.12)]
                                        transition-shadow">
                                <i data-lucide="lock" class="h-[18px] w-[18px] shrink-0 text-[#565959]"></i>
                                <input :type="showRegPwC ? 'text' : 'password'" name="password_confirmation"
                                       placeholder="Repeat password" autocomplete="new-password" required
                                       class="w-full bg-transparent text-[15px] text-[#0f1111]
                                              placeholder:text-[#8a8f98] outline-none" />
                                <button type="button" @click="showRegPwC = !showRegPwC"
                                        class="text-[12px] font-bold text-[#007185] hover:underline shrink-0"
                                        x-text="showRegPwC ? 'Hide' : 'Show'"></button>
                            </div>
                        </label>

                        <button type="submit"
                                class="flex w-full items-center justify-center gap-2 rounded-full
                                       border border-[#fcd200] bg-[#ffd814] px-4 py-3
                                       text-[14px] font-medium text-[#0f1111]
                                       shadow-[inset_0_-1px_0_rgba(0,0,0,0.15)]
                                       hover:bg-[#f7ca00] transition-colors active:scale-[.99]">
                            Create account
                        </button>
                    </form>

                    {{-- Bottom divider + switch link --}}
                    <div class="mt-6 border-t border-[#eaeded] pt-5">
                        <p class="text-[13px] text-[#565959]"
                           x-text="mode === 'login' ? 'Need admin access?' : 'Already have an account?'"></p>
                        <button type="button" @click="setMode(mode === 'login' ? 'register' : 'login')"
                                class="mt-3 inline-flex w-full items-center justify-center rounded-full
                                       border border-[#d5d9d9] bg-[#f7fafa] px-4 py-3
                                       text-[14px] font-medium text-[#0f1111]
                                       shadow-[inset_0_-1px_0_rgba(0,0,0,0.08)]
                                       hover:bg-[#eef3f3] transition-colors">
                            <span x-text="mode === 'login' ? 'Create an admin account' : 'Sign in instead'"></span>
                        </button>
                    </div>
                </div>{{-- /card --}}
            </div>{{-- /centered wrapper --}}
        </div>{{-- /right panel --}}

    </div>{{-- /shell --}}
</div>

<script>
function authPage() {
    return {
        mode: 'login',
        showLoginPw:  false,
        showRegPw:    false,
        showRegPwC:   false,

        init() {
            this.mode = document.body.dataset.initialMode || 'login';
            this.$nextTick(() => lucide.createIcons());
        },

        setMode(m) {
            this.mode = m;
            this.$nextTick(() => lucide.createIcons());
        },
    };
}
</script>
</body>
</html>
