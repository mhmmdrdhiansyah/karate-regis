<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat — {{ $participant->name }}</title>
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

    {{-- Nav (identik landing) --}}
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

    {{-- Band identitas peserta --}}
    <section class="bg-on-background relative overflow-hidden">
        <div class="absolute -right-16 bottom-0 text-accent opacity-20 font-display-lg select-none pointer-events-none -rotate-6 whitespace-nowrap leading-none">
            CERTIFICATE
        </div>
        <div class="container mx-auto px-lg py-lg relative z-10">
            <a href="{{ route('certificates.public.index') }}"
               class="inline-flex items-center gap-xs font-label-bold text-surface-variant hover:text-accent uppercase mb-md">
                <span class="material-symbols-outlined text-base">arrow_back</span> Cari NIK Lain
            </a>
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-md">
                <div>
                    <p class="font-label-bold text-accent uppercase tracking-widest mb-xs">Sertifikat Ditemukan</p>
                    <h1 class="font-display-lg-mobile text-display-lg-mobile md:text-headline-lg text-white uppercase leading-none mb-xs">
                        {{ strtoupper($participant->name) }}
                    </h1>
                    <p class="font-body-md text-surface-variant">
                        @if ($participant->contingent)
                            Kontingen {{ $participant->contingent->name }} ·
                        @endif
                        {{ $certificates->count() }} sertifikat tersedia
                    </p>
                </div>
                <div class="bg-on-background border-2 border-surface-variant px-md py-sm self-start md:self-auto">
                    <p class="font-label-sm text-surface-variant uppercase tracking-widest mb-xs">NIK</p>
                    <p class="font-body-lg font-bold text-white tracking-[0.25em]">{{ $participant->nik }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Kartu sertifikat --}}
    <main class="py-xl container mx-auto px-lg">
        @if ($certificates->isEmpty())
            <div class="border-2 border-on-background bg-white p-xl text-center hard-shadow max-w-2xl mx-auto">
                <span class="material-symbols-outlined text-primary text-6xl mb-md">workspace_premium</span>
                <h2 class="font-headline-lg uppercase mb-sm">Belum Ada Sertifikat</h2>
                <p class="font-body-lg text-secondary">
                    Sertifikat terbit setelah berkas dan pembayaran pendaftaranmu terverifikasi panitia.
                    Merasa ini keliru? Hubungi panitia lewat kontak di bawah.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
                @foreach ($certificates as $cert)
                    @php
                        $rail = match ($cert['scope']->value) {
                            'champion_gold' => '#d4af37',
                            'champion_silver' => '#a8a9ad',
                            'champion_bronze' => '#96502c',
                            'champion_other' => '#FFD700',
                            default => '#b9001c',
                        };
                    @endphp
                    <div class="bg-white border-2 border-on-background hard-shadow flex flex-col overflow-hidden group transition-all duration-300 hover:-translate-y-1">
                        {{-- Rail medali: warna = hasil di arena --}}
                        <div class="h-2 w-full" style="background-color: {{ $rail }};"></div>
                        <div class="p-lg flex flex-col gap-sm flex-grow">
                            <div class="flex items-start justify-between gap-sm">
                                <div>
                                    <p class="font-label-bold text-primary uppercase mb-xs">{{ $cert['event']->name }}</p>
                                    <p class="font-body-lg">{{ $cert['class'] }} — {{ $cert['sub_category'] }}</p>
                                </div>
                                <span class="inline-flex items-center gap-xs border-2 border-on-background px-sm py-xs font-label-bold uppercase whitespace-nowrap">
                                    <span class="material-symbols-outlined text-lg" style="color: {{ $rail }};">military_tech</span>
                                    {{ $cert['status'] }}
                                </span>
                            </div>
                            <div class="mt-auto pt-sm border-t-2 border-surface-variant">
                                <a href="{{ route('certificates.public.pdf', $cert['registration']) }}"
                                   class="bg-primary text-on-primary font-headline-md uppercase px-lg py-sm border-2 border-on-background hard-shadow hover:bg-on-background transition-all inline-flex items-center gap-xs">
                                    <span class="material-symbols-outlined text-lg">download</span>
                                    Unduh PDF
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </main>

    {{-- Footer (identik landing) --}}
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
