<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Peserta — {{ config('app.name', 'Combat Pro') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/media/logos/logo3.png') }}" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Hanken+Grotesk:wght@400;500;700&display=swap"
          rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
          rel="stylesheet"/>

    <style>
        *, *::before, *::after { box-sizing: border-box; border-width: 0; border-style: solid; border-color: currentColor; }
        html { line-height: 1.5; -webkit-text-size-adjust: 100%; }
        body { margin: 0; font-family: 'Hanken Grotesk', sans-serif; background-color: #fbf9f8; color: #1b1c1c; }
        h1, h2, h3, h4, h5, h6 { font-size: inherit; font-weight: inherit; margin: 0; }
        p { margin: 0; }
        img, svg { display: block; max-width: 100%; }
        button { font: inherit; color: inherit; background-color: transparent; cursor: pointer; }
        a { color: inherit; text-decoration: none; }

        .container { width: 100%; max-width: 1280px; margin-left: auto; margin-right: auto; }

        .hard-shadow { box-shadow: 4px 4px 0px 0px #1b1c1c; transition: box-shadow .12s ease, transform .12s ease; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    </style>
</head>
<body class="bg-surface text-on-background font-body-md selection:bg-primary selection:text-white">

    <nav class="flex justify-between items-center px-md py-sm w-full sticky top-0 z-50 bg-surface border-b-2 border-on-background">
        <div class="font-display-lg-mobile text-display-lg-mobile text-primary italic uppercase tracking-tighter">
            COMBAT <span class="text-on-background">PRO</span>
        </div>
        <div class="hidden md:flex gap-lg items-center">
            <a class="font-label-bold text-label-bold text-on-background hover:border-b-4 hover:border-accent transition-all duration-100 ease-in-out active:translate-y-0.5 px-xs" href="{{ route('landing') }}#events">TOURNAMENTS</a>
            <a class="font-label-bold text-label-bold text-primary border-b-4 border-accent transition-all duration-100 ease-in-out active:translate-y-0.5 px-xs" href="{{ route('certificates.public.index') }}">CEK SERTIFIKAT</a>
            <a class="font-label-bold text-label-bold text-on-background hover:border-b-4 hover:border-accent transition-all duration-100 ease-in-out active:translate-y-0.5 px-xs" href="{{ route('landing') }}#about">ABOUT</a>
            <a class="font-label-bold text-label-bold text-on-background hover:border-b-4 hover:border-accent transition-all duration-100 ease-in-out active:translate-y-0.5 px-xs" href="{{ route('landing') }}#contact">CONTACT</a>
        </div>
        @guest
        <a href="{{ route('login') }}" class="bg-primary text-on-primary font-headline-md px-md py-xs border-2 border-on-background hard-shadow hover:bg-on-background transition-all">
            LOGIN
        </a>
        @else
        <a href="{{ route('dashboard') }}" class="bg-primary text-on-primary font-headline-md px-md py-xs border-2 border-on-background hard-shadow hover:bg-on-background transition-all">
            DASHBOARD
        </a>
        @endguest
    </nav>

    <section class="bg-on-background relative overflow-hidden">
        <div class="absolute -right-16 bottom-0 text-accent opacity-20 font-display-lg select-none pointer-events-none -rotate-6 whitespace-nowrap leading-none">
            CERTIFICATE
        </div>

        <div class="container mx-auto px-lg py-lg relative z-10">
            <p class="font-label-bold text-accent uppercase tracking-widest mb-xs">Beberapa Peserta Ditemukan</p>
            <h1 class="font-display-lg-mobile text-display-lg-mobile md:text-headline-lg text-white uppercase leading-none mb-xs">
                {{ strtoupper($nama) }}
            </h1>
            <p class="font-body-md text-surface-variant max-w-2xl">
                Ditemukan {{ $candidates->count() }} peserta dengan nama dan tanggal lahir yang sama.
                Pilih identitasmu di bawah untuk melihat sertifikat.
            </p>
        </div>
    </section>

    <main class="py-xl container mx-auto px-lg">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
            @foreach ($candidates as $c)
                <a href="{{ $c['url'] }}" class="bg-white border-2 border-on-background hard-shadow p-lg flex items-center justify-between gap-md hover:-translate-y-1 transition-all">
                    <div>
                        <p class="font-headline-md uppercase">{{ $c['participant']->name }}</p>
                        <p class="font-body-md text-secondary">
                            {{ $c['participant']->contingent?->name ?? 'Tanpa kontingen' }} ·
                            NIK {{ $c['participant']->nik
                                ? \Illuminate\Support\Str::mask($c['participant']->nik, '*', 4, 8)
                                : '—' }}
                        </p>
                    </div>
                    <span class="material-symbols-outlined text-primary">arrow_forward</span>
                </a>
            @endforeach
        </div>

        <div class="mt-lg">
            <a href="{{ route('certificates.public.index') }}" class="inline-flex items-center gap-xs font-label-bold text-on-background hover:text-primary uppercase">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Kembali ke pencarian
            </a>
        </div>
    </main>

    <footer class="bg-on-background text-surface-variant flex flex-col md:flex-row justify-between items-center px-lg py-xl w-full gap-md border-t-4 border-accent">
        <div class="flex flex-col items-center md:items-start gap-sm">
            <div class="font-headline-md text-headline-md text-surface uppercase tracking-tight">
                COMBAT <span class="text-accent">PRO</span>
            </div>
            <p class="font-label-sm text-label-sm uppercase tracking-widest text-secondary">
                © 2026 COMBAT PRO. PRECISION & POWER.
            </p>
        </div>
        <div class="flex flex-wrap justify-center gap-md">
            <a class="font-label-sm text-label-sm uppercase tracking-widest text-secondary hover:text-accent opacity-80 hover:opacity-100 transition-all" href="{{ route('landing') }}#events">Tournaments</a>
            <a class="font-label-sm text-label-sm uppercase tracking-widest text-secondary hover:text-accent opacity-80 hover:opacity-100 transition-all" href="{{ route('certificates.public.index') }}">Cek Sertifikat</a>
            <a class="font-label-sm text-label-sm uppercase tracking-widest text-secondary hover:text-accent opacity-80 hover:opacity-100 transition-all" href="{{ route('landing') }}#contact">Contact</a>
        </div>
    </footer>
</body>
</html>
