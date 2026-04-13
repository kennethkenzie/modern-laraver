<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Modern Electronics - Admin Access</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Inter, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(249,115,22,.20), transparent 32%),
                radial-gradient(circle at bottom right, rgba(17,79,143,.30), transparent 38%),
                #09111f;
            color: #e5eef8;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .shell {
            width: 100%;
            max-width: 1120px;
            display: grid;
            grid-template-columns: 1.05fr .95fr;
            overflow: hidden;
            border: 1px solid rgba(148,163,184,.18);
            border-radius: 28px;
            background: rgba(7, 15, 29, .92);
            box-shadow: 0 28px 80px rgba(0,0,0,.42);
            backdrop-filter: blur(18px);
        }
        .panel {
            padding: 52px 48px;
            position: relative;
        }
        .hero {
            background:
                linear-gradient(180deg, rgba(17,79,143,.16), rgba(7,15,29,.06)),
                linear-gradient(135deg, rgba(255,255,255,.02), rgba(255,255,255,.00));
            border-right: 1px solid rgba(148,163,184,.14);
        }
        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(246,196,0,.12);
            color: #fcd34d;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .24em;
            text-transform: uppercase;
        }
        .hero h1 {
            margin-top: 26px;
            font-size: 52px;
            line-height: .96;
            font-weight: 900;
            letter-spacing: -.05em;
            text-transform: uppercase;
            color: #f8fafc;
        }
        .hero p {
            margin-top: 22px;
            max-width: 460px;
            color: #94a3b8;
            font-size: 15px;
            line-height: 1.8;
        }
        .stack {
            margin-top: 34px;
            display: grid;
            gap: 14px;
        }
        .stack-card {
            border: 1px solid rgba(148,163,184,.12);
            background: rgba(15,23,42,.76);
            border-radius: 20px;
            padding: 18px 18px 16px;
        }
        .stack-card .label {
            font-size: 11px;
            color: #60a5fa;
            font-weight: 800;
            letter-spacing: .18em;
            text-transform: uppercase;
        }
        .stack-card .value {
            margin-top: 10px;
            color: #f8fafc;
            font-size: 18px;
            font-weight: 800;
            line-height: 1.4;
        }
        .stack-card .note {
            margin-top: 8px;
            color: #94a3b8;
            font-size: 13px;
            line-height: 1.6;
        }
        .auth {
            background: linear-gradient(180deg, rgba(15,23,42,.92), rgba(2,6,23,.96));
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .brand-mark {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, #f6c400, #f97316);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #111827;
            font-weight: 900;
        }
        .brand-copy strong {
            display: block;
            color: #f8fafc;
            font-size: 17px;
            font-weight: 900;
        }
        .brand-copy span {
            display: block;
            margin-top: 4px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .2em;
        }
        .tabs {
            margin-top: 28px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            padding: 8px;
            border-radius: 18px;
            background: rgba(15,23,42,.92);
            border: 1px solid rgba(148,163,184,.14);
        }
        .tab {
            height: 50px;
            border: 0;
            border-radius: 14px;
            background: transparent;
            color: #94a3b8;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .16em;
            cursor: pointer;
        }
        .tab.active {
            background: #114f8f;
            color: #fff;
            box-shadow: 0 14px 34px rgba(17,79,143,.28);
        }
        .title {
            margin-top: 28px;
            color: #f8fafc;
            font-size: 30px;
            line-height: 1.02;
            font-weight: 900;
            letter-spacing: -.04em;
            text-transform: uppercase;
        }
        .subtitle {
            margin-top: 10px;
            color: #94a3b8;
            font-size: 14px;
            line-height: 1.7;
        }
        .alert {
            margin-top: 18px;
            padding: 14px 16px;
            border-radius: 14px;
            font-size: 13px;
            line-height: 1.6;
        }
        .alert.error {
            border: 1px solid rgba(239,68,68,.25);
            background: rgba(127,29,29,.28);
            color: #fecaca;
        }
        .form {
            margin-top: 26px;
            display: none;
        }
        .form.active {
            display: block;
        }
        .grid {
            display: grid;
            gap: 16px;
        }
        .grid.two {
            grid-template-columns: 1fr 1fr;
        }
        label {
            display: block;
        }
        label span {
            display: block;
            margin-bottom: 8px;
            color: #94a3b8;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .16em;
        }
        input, select {
            width: 100%;
            height: 48px;
            border-radius: 14px;
            border: 1px solid rgba(51,65,85,.95);
            background: #0f172a;
            padding: 0 15px;
            color: #f8fafc;
            font-size: 14px;
            font-weight: 600;
            outline: none;
            transition: border-color .18s, box-shadow .18s;
        }
        input:focus, select:focus {
            border-color: #38bdf8;
            box-shadow: 0 0 0 4px rgba(56,189,248,.12);
        }
        .password-wrap {
            position: relative;
        }
        .password-wrap button {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .14em;
        }
        .meta {
            margin-top: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            color: #94a3b8;
            font-size: 13px;
        }
        .meta a {
            color: #f6c400;
            text-decoration: none;
            font-weight: 800;
        }
        .submit {
            margin-top: 20px;
            width: 100%;
            height: 52px;
            border: 0;
            border-radius: 16px;
            background: linear-gradient(135deg, #f6c400, #f97316);
            color: #111827;
            font-size: 13px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .18em;
            cursor: pointer;
            transition: transform .12s, opacity .18s;
        }
        .submit:hover { opacity: .95; }
        .submit:active { transform: scale(.99); }
        .api {
            margin-top: 26px;
            padding-top: 20px;
            border-top: 1px solid rgba(148,163,184,.12);
            color: #64748b;
            font-size: 12px;
        }
        .api strong {
            color: #38bdf8;
            font-weight: 800;
        }
        @media (max-width: 980px) {
            .shell { grid-template-columns: 1fr; }
            .hero { display: none; }
            .panel { padding: 34px 24px; }
            .grid.two { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body data-initial-mode="{{ $errors->hasBag('register') ? 'register' : 'login' }}">
    <div class="shell">
        <section class="panel hero">
            <div class="eyebrow">Admin Access Portal</div>
            <h1>Control the storefront from one secure dashboard.</h1>
            <p>
                Sign in to manage products, shipping, storefront content, and operations.
                New team members can also be created directly from this admin entry page.
            </p>

            <div class="stack">
                <article class="stack-card">
                    <div class="label">Dashboard URL</div>
                    <div class="value">{{ url('/dashboard') }}</div>
                    <div class="note">Authenticated users are redirected here immediately after sign-in or account creation.</div>
                </article>
                <article class="stack-card">
                    <div class="label">Frontend Website</div>
                    <div class="value">https://e-modern.ug/</div>
                    <div class="note">The admin now manages storefront header, slider, product, and shipping configuration used by the live site.</div>
                </article>
                <article class="stack-card">
                    <div class="label">API Base</div>
                    <div class="value">{{ url('/api') }}</div>
                    <div class="note">Next.js storefront and admin-facing uploads are wired through the Laravel backend.</div>
                </article>
            </div>
        </section>

        <section class="panel auth">
            <div class="brand">
                <div class="brand-mark">ME</div>
                <div class="brand-copy">
                    <strong>Modern Electronics</strong>
                    <span>Admin Dashboard</span>
                </div>
            </div>

            <div class="tabs">
                <button type="button" class="tab" data-tab="login">Sign In</button>
                <button type="button" class="tab" data-tab="register">Register</button>
            </div>

            <h1 class="title" id="authTitle">Welcome Back</h1>
            <p class="subtitle" id="authSubtitle">Sign in to access the admin dashboard.</p>

            @if ($errors->any() && ! $errors->hasBag('register'))
                <div class="alert error">{{ $errors->first() }}</div>
            @endif

            @if ($errors->hasBag('register'))
                <div class="alert error">{{ $errors->register->first() }}</div>
            @endif

            @if (session('session_expired'))
                <div class="alert error">{{ session('session_expired') }}</div>
            @endif

            <form method="POST" action="{{ route('web.login') }}" class="form" data-form="login">
                @csrf
                <div class="grid">
                    <label>
                        <span>Email Address</span>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="admin@e-modern.ug" autocomplete="username" required />
                    </label>

                    <label>
                        <span>Password</span>
                        <div class="password-wrap">
                            <input type="password" name="password" id="login_password" placeholder="Enter your password" autocomplete="current-password" required />
                            <button type="button" onclick="togglePassword('login_password', this)">Show</button>
                        </div>
                    </label>
                </div>

                <div class="meta">
                    <span>Admin and staff accounts only.</span>
                    <a href="#" onclick="event.preventDefault(); setMode('register')">Create account</a>
                </div>

                <button type="submit" class="submit">Sign In</button>
            </form>

            <form method="POST" action="{{ route('web.register') }}" class="form" data-form="register">
                @csrf
                <div class="grid">
                    <label>
                        <span>Full Name</span>
                        <input type="text" name="full_name" value="{{ old('full_name') }}" placeholder="Jane Doe" required />
                    </label>

                    <div class="grid two">
                        <label>
                            <span>Email Address</span>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="jane@e-modern.ug" required />
                        </label>
                        <label>
                            <span>Phone</span>
                            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+256700000000" />
                        </label>
                    </div>

                    <label>
                        <span>Account Role</span>
                        <select name="role" required>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Staff</option>
                        </select>
                    </label>

                    <div class="grid two">
                        <label>
                            <span>Password</span>
                            <div class="password-wrap">
                                <input type="password" name="password" id="register_password" placeholder="Minimum 6 characters" autocomplete="new-password" required />
                                <button type="button" onclick="togglePassword('register_password', this)">Show</button>
                            </div>
                        </label>
                        <label>
                            <span>Confirm Password</span>
                            <div class="password-wrap">
                                <input type="password" name="password_confirmation" id="register_password_confirmation" placeholder="Repeat password" autocomplete="new-password" required />
                                <button type="button" onclick="togglePassword('register_password_confirmation', this)">Show</button>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="meta">
                    <span>New accounts are signed in immediately after registration.</span>
                    <a href="#" onclick="event.preventDefault(); setMode('login')">Already have an account?</a>
                </div>

                <button type="submit" class="submit">Create Account</button>
            </form>

            <div class="api">API base URL: <strong>{{ url('/api') }}</strong></div>
        </section>
    </div>

    <script>
        const titles = {
            login: {
                title: 'Welcome Back',
                subtitle: 'Sign in to access the admin dashboard.',
            },
            register: {
                title: 'Create Admin Access',
                subtitle: 'Register a new admin or staff account for the dashboard.',
            },
        };

        function setMode(mode) {
            document.querySelectorAll('[data-tab]').forEach((button) => {
                button.classList.toggle('active', button.dataset.tab === mode);
            });

            document.querySelectorAll('[data-form]').forEach((form) => {
                form.classList.toggle('active', form.dataset.form === mode);
            });

            document.getElementById('authTitle').textContent = titles[mode].title;
            document.getElementById('authSubtitle').textContent = titles[mode].subtitle;
        }

        function togglePassword(id, button) {
            const input = document.getElementById(id);
            if (!input) return;
            input.type = input.type === 'password' ? 'text' : 'password';
            button.textContent = input.type === 'password' ? 'Show' : 'Hide';
        }

        document.querySelectorAll('[data-tab]').forEach((button) => {
            button.addEventListener('click', () => setMode(button.dataset.tab));
        });

        setMode(document.body.dataset.initialMode || 'login');
    </script>
</body>
</html>
