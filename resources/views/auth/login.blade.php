<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — Login</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Hanken+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --ink: #1b1c1c;
            --paper: #fbf9f8;
            --primary: #b9001c;
            --primary-soft: #ffdad7;
            --muted: #5d3f3d;
            --hair: #e7bdb9;
            --surface: #efeded;
            --surface-low: #f5f3f3;
        }

        html, body { height: 100%; }

        body {
            font-family: 'Hanken Grotesk', sans-serif;
            background: var(--surface-low);
            color: var(--ink);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }

        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined';
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            line-height: 1;
            user-select: none;
        }

        .fill-icon { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }

        /* Top bar */
        .topbar {
            background: var(--paper);
            border-bottom: 2px solid var(--ink);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar-inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand {
            font-family: 'Anton', sans-serif;
            font-size: 28px;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: var(--primary);
            line-height: 1;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand::before {
            content: '';
            width: 8px;
            height: 28px;
            background: var(--ink);
            display: inline-block;
        }

        .topbar-back {
            font-family: 'Hanken Grotesk', sans-serif;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--ink);
            text-decoration: none;
            padding: 8px 16px;
            border: 2px solid var(--ink);
            background: var(--paper);
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .topbar-back:hover {
            background: var(--ink);
            color: var(--paper);
        }

        .topbar-back:active { transform: translateY(2px); }

        /* Main login */
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 24px;
        }

        .login-shell {
            width: 100%;
            max-width: 1180px;
            display: grid;
            grid-template-columns: 1fr;
            background: var(--paper);
            border: 2px solid var(--ink);
            box-shadow: 10px 10px 0 var(--ink);
            overflow: hidden;
            min-height: 640px;
        }

        @media (min-width: 900px) {
            .login-shell { grid-template-columns: 1.05fr 1fr; }
        }

        /* Left visual panel */
        .visual {
            position: relative;
            background: var(--ink);
            color: var(--paper);
            padding: 64px 56px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            min-height: 320px;
        }

        @media (max-width: 899px) {
            .visual { display: none; }
        }

        .visual::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 15% 20%, rgba(185, 0, 28, 0.35), transparent 50%),
                radial-gradient(circle at 85% 80%, rgba(185, 0, 28, 0.25), transparent 55%);
            pointer-events: none;
        }

        .visual::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }

        .visual-watermark {
            position: absolute;
            font-family: 'Anton', sans-serif;
            font-size: 280px;
            line-height: 0.85;
            color: rgba(255, 255, 255, 0.04);
            top: -40px;
            right: -60px;
            letter-spacing: -0.02em;
            pointer-events: none;
            user-select: none;
        }

        .visual-watermark-2 {
            position: absolute;
            font-family: 'Anton', sans-serif;
            font-size: 280px;
            line-height: 0.85;
            color: rgba(185, 0, 28, 0.18);
            bottom: -80px;
            left: -40px;
            letter-spacing: -0.02em;
            pointer-events: none;
            user-select: none;
        }

        .visual-content { position: relative; z-index: 2; display: flex; flex-direction: column; gap: 32px; }

        .visual-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--primary);
            color: var(--paper);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            padding: 6px 12px;
            align-self: flex-start;
        }

        .visual-headline {
            font-family: 'Anton', sans-serif;
            font-size: clamp(48px, 5.5vw, 72px);
            line-height: 0.95;
            letter-spacing: 0.01em;
            text-transform: uppercase;
            font-weight: 400;
        }

        .visual-headline .red { color: #ffb3ae; }
        .visual-headline .underline {
            display: inline-block;
            border-bottom: 8px solid var(--primary);
            padding-bottom: 2px;
        }

        .visual-body {
            font-size: 16px;
            line-height: 1.55;
            color: #d6cfc9;
            max-width: 380px;
            font-weight: 400;
        }

        .visual-stats {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0;
            border-top: 1px solid rgba(255,255,255,0.15);
            padding-top: 28px;
        }

        .stat {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .stat + .stat { border-left: 1px solid rgba(255,255,255,0.15); padding-left: 20px; }
        .stat:not(:last-child) { padding-right: 20px; }

        .stat-num {
            font-family: 'Anton', sans-serif;
            font-size: 36px;
            line-height: 1;
            color: var(--paper);
        }

        .stat-lbl {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #9ca3af;
        }

        /* Right form panel */
        .form-panel {
            padding: 56px 64px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            background: var(--paper);
        }

        @media (max-width: 600px) {
            .form-panel { padding: 40px 28px; }
        }

        .form-wrapper { max-width: 380px; width: 100%; margin: 0 auto; }

        .form-icon {
            width: 56px;
            height: 56px;
            background: var(--ink);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            box-shadow: 5px 5px 0 var(--primary);
        }

        .form-icon .material-symbols-outlined { font-size: 32px; }

        .form-title {
            font-family: 'Anton', sans-serif;
            font-size: 44px;
            line-height: 1;
            letter-spacing: 0.01em;
            text-transform: uppercase;
            color: var(--ink);
            margin-bottom: 8px;
        }

        .form-subtitle {
            font-size: 14px;
            color: var(--muted);
            margin-bottom: 36px;
            font-weight: 500;
        }

        .form-subtitle .accent { color: var(--primary); font-weight: 700; }

        /* Alerts */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 14px;
            border: 2px solid var(--ink);
            margin-bottom: 24px;
            font-size: 13px;
            line-height: 1.4;
        }

        .alert-error {
            background: var(--primary);
            color: var(--paper);
        }

        .alert-error .material-symbols-outlined { color: var(--paper); font-size: 22px; flex-shrink: 0; }

        /* Field */
        .field { margin-bottom: 18px; }

        .field-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .field-label label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--ink);
        }

        .field-label a {
            font-size: 11px;
            font-weight: 600;
            color: var(--primary);
            text-decoration: none;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .field-label a:hover { text-decoration: underline; text-decoration-thickness: 2px; underline-offset: 3px; }

        .input-wrap { position: relative; }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: 22px;
            pointer-events: none;
            transition: color 0.15s ease;
            z-index: 2;
        }

        .input {
            width: 100%;
            background: var(--paper);
            border: 2px solid var(--ink);
            padding: 14px 16px 14px 46px;
            font-family: 'Hanken Grotesk', sans-serif;
            font-size: 15px;
            font-weight: 500;
            color: var(--ink);
            transition: all 0.15s ease;
            outline: none;
            border-radius: 0;
        }

        .input::placeholder { color: #a8a8a8; font-weight: 400; }

        .input:focus {
            border-color: var(--primary);
            box-shadow: 4px 4px 0 var(--primary);
            transform: translate(-2px, -2px);
        }

        .input:focus + .input-icon,
        .input-wrap:focus-within .input-icon { color: var(--primary); }

        .field-error {
            font-size: 12px;
            color: var(--primary);
            margin-top: 6px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Remember */
        .remember {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 28px;
        }

        .check-wrap {
            position: relative;
            display: inline-flex;
        }

        .check-wrap input {
            appearance: none;
            -webkit-appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid var(--ink);
            background: var(--paper);
            cursor: pointer;
            position: relative;
            transition: all 0.15s ease;
            border-radius: 0;
        }

        .check-wrap input:checked {
            background: var(--primary);
            border-color: var(--ink);
        }

        .check-wrap input:checked::after {
            content: '';
            position: absolute;
            left: 4px;
            top: 0px;
            width: 7px;
            height: 12px;
            border: solid var(--paper);
            border-width: 0 2.5px 2.5px 0;
            transform: rotate(45deg);
        }

        .check-label {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--ink);
            cursor: pointer;
            user-select: none;
        }

        /* Submit */
        .submit {
            width: 100%;
            background: var(--primary);
            color: var(--paper);
            border: 2px solid var(--ink);
            padding: 18px 24px;
            font-family: 'Anton', sans-serif;
            font-size: 22px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.15s ease;
            box-shadow: 6px 6px 0 var(--ink);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
        }

        .submit:hover {
            background: var(--ink);
            transform: translate(-2px, -2px);
            box-shadow: 8px 8px 0 var(--primary);
        }

        .submit:active {
            transform: translate(2px, 2px);
            box-shadow: 2px 2px 0 var(--ink);
        }

        .submit:active { background: var(--primary); }

        .submit .material-symbols-outlined { font-size: 22px; }

        /* Footer link */
        .signup {
            margin-top: 32px;
            padding-top: 28px;
            border-top: 2px dashed var(--hair);
            text-align: center;
            font-size: 14px;
            color: var(--muted);
        }

        .signup a {
            color: var(--ink);
            font-weight: 700;
            text-decoration: underline;
            text-decoration-color: var(--primary);
            text-decoration-thickness: 3px;
            text-underline-offset: 4px;
            letter-spacing: 0.04em;
            margin-left: 4px;
        }

        .signup a:hover { color: var(--primary); }

        /* Security badges */
        .badges {
            margin-top: 28px;
            display: flex;
            justify-content: center;
            gap: 24px;
            opacity: 0.7;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .badge .material-symbols-outlined { font-size: 14px; color: var(--primary); }

        /* Footer */
        .footer {
            background: var(--paper);
            border-top: 2px solid var(--ink);
            padding: 20px 24px;
        }

        .footer-inner {
            max-width: 1280px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .footer-brand {
            font-family: 'Anton', sans-serif;
            font-size: 20px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--ink);
        }

        .footer-copy {
            font-size: 11px;
            font-weight: 500;
            color: var(--muted);
            letter-spacing: 0.08em;
        }

        .footer-nav { display: flex; gap: 20px; }

        .footer-nav a {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--muted);
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .footer-nav a:hover { color: var(--primary); }

        /* Animations */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideRight {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .anim-1 { animation: slideUp 0.6s ease 0.1s both; }
        .anim-2 { animation: slideUp 0.6s ease 0.2s both; }
        .anim-3 { animation: slideUp 0.6s ease 0.3s both; }
        .anim-4 { animation: slideUp 0.6s ease 0.4s both; }
        .anim-5 { animation: slideUp 0.6s ease 0.5s both; }
        .anim-6 { animation: slideUp 0.6s ease 0.6s both; }

        .visual-content > * { animation: slideRight 0.7s ease both; }
        .visual-content > *:nth-child(1) { animation-delay: 0.15s; }
        .visual-content > *:nth-child(2) { animation-delay: 0.25s; }
        .visual-content > *:nth-child(3) { animation-delay: 0.35s; }
    </style>
</head>

<body>
    <!-- Top bar -->
    <header class="topbar">
        <div class="topbar-inner">
            <a href="{{ route('landing') }}" class="brand">
                COMBAT PRO
            </a>
            <a href="{{ route('landing') }}" class="topbar-back">
                <span class="material-symbols-outlined" style="font-size:16px;">arrow_back</span>
                Kembali ke Beranda
            </a>
        </div>
    </header>

    <!-- Main -->
    <main>
        <div class="login-shell">
            <!-- Left visual -->
            <aside class="visual">
                <div class="visual-watermark">COMBAT</div>
                <div class="visual-watermark-2">格闘</div>

                <div class="visual-content">
                    <span class="visual-tag">
                        <span class="material-symbols-outlined fill-icon" style="font-size:14px;">bolt</span>
                        Musim Kejuaraan 2026
                    </span>

                    <h2 class="visual-headline">
                        DISIPLIN<br>
                        ADALAH<br>
                        <span class="red">KEKUATAN</span><br>
                        <span class="underline">UTAMA.</span>
                    </h2>

                    <p class="visual-body">
                        Masuk ke arena Combat Pro. Kelola pendaftaran kontingen, lacak pembayaran, dan tampilkan atlet terbaikmu di setiap kejuaraan olahraga.
                    </p>
                </div>

                <div class="visual-stats">
                    <div class="stat">
                        <span class="stat-num">5</span>
                        <span class="stat-lbl">Event Aktif</span>
                    </div>
                    <div class="stat">
                        <span class="stat-num">6</span>
                        <span class="stat-lbl">Kontingen</span>
                    </div>
                    <div class="stat">
                        <span class="stat-num">100+</span>
                        <span class="stat-lbl">Atlet</span>
                    </div>
                </div>
            </aside>

            <!-- Right form -->
            <section class="form-panel">
                <div class="form-wrapper">
                    <div class="form-icon anim-1">
                        <span class="material-symbols-outlined fill-icon">sports_martial_arts</span>
                    </div>

                    <h1 class="form-title anim-2">Masuk Arena</h1>
                    <p class="form-subtitle anim-2">
                        Siap bertanding, <span class="accent">Atlet.</span>
                    </p>

                    @if (session('status'))
                        <div class="alert alert-error anim-3">
                            <span class="material-symbols-outlined fill-icon">info</span>
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="anim-3">
                        @csrf

                        @if ($errors->any())
                            <div class="alert alert-error">
                                <span class="material-symbols-outlined fill-icon">error</span>
                                <div>
                                    <strong>Kredensial tidak valid.</strong><br>
                                    @foreach ($errors->all() as $error)
                                        {{ $error }}<br>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Username -->
                        <div class="field">
                            <div class="field-label">
                                <label for="username">Username atau ID Anggota</label>
                            </div>
                            <div class="input-wrap">
                                <input
                                    type="text"
                                    id="username"
                                    name="username"
                                    class="input"
                                    value="{{ old('username') }}"
                                    placeholder="cth: superadmin"
                                    autocomplete="username"
                                    autofocus
                                    required>
                                <span class="material-symbols-outlined input-icon">person</span>
                            </div>
                            @error('username')
                                <div class="field-error">
                                    <span class="material-symbols-outlined" style="font-size:14px;">warning</span>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="field">
                            <div class="field-label">
                                <label for="password">Kata Sandi</label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}">Lupa Sandi?</a>
                                @endif
                            </div>
                            <div class="input-wrap">
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="input"
                                    placeholder="••••••••"
                                    autocomplete="current-password"
                                    required>
                                <span class="material-symbols-outlined input-icon">lock</span>
                            </div>
                            @error('password')
                                <div class="field-error">
                                    <span class="material-symbols-outlined" style="font-size:14px;">warning</span>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Remember -->
                        <div class="remember">
                            <label class="check-wrap">
                                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            </label>
                            <label class="check-label" for="remember">Ingat Saya</label>
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="submit">
                            <span class="material-symbols-outlined">login</span>
                            Masuk Arena
                        </button>
                    </form>

                    @if (Route::has('register'))
                        <div class="signup">
                            Belum punya akun kontingen?
                            <a href="{{ route('register') }}">Daftar Sekarang</a>
                        </div>
                    @endif

                    <div class="badges">
                        <div class="badge">
                            <span class="material-symbols-outlined fill-icon">security</span>
                            Koneksi Aman
                        </div>
                        <div class="badge">
                            <span class="material-symbols-outlined fill-icon">verified_user</span>
                            Terverifikasi
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-inner">
            <div class="footer-brand">COMBAT PRO</div>
            <div class="footer-copy">© {{ date('Y') }} COMBAT PRO. ALL STRIKES RESERVED.</div>
            <nav class="footer-nav">
                <a href="#">Terms</a>
                <a href="#">Privacy</a>
                <a href="#">Support</a>
            </nav>
        </div>
    </footer>
</body>

</html>
