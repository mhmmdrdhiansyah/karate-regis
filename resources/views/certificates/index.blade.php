<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Sertifikat — {{ config('app.name', 'Combat Pro') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/media/logos/logo3.png') }}" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Hanken+Grotesk:wght@400;500;700&display=swap"
          rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
          rel="stylesheet"/>

    <style>
        /* Page-local reset (preflight disabled project-wide) — same scoping
           approach as welcome.blade.php. */
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

    {{-- Hero gelap: form NIK adalah bintangnya --}}
    <section class="relative overflow-hidden bg-on-background">
        <div class="absolute -right-16 bottom-0 text-accent opacity-20 font-display-lg select-none pointer-events-none -rotate-6 whitespace-nowrap leading-none">
            CERTIFICATE
        </div>

        <div class="container mx-auto px-lg py-xl relative z-10">
            <div class="grid lg:grid-cols-2 gap-xl items-center">
                <div>
                    <div class="bg-accent text-on-accent inline-block px-sm py-xs font-label-bold mb-md uppercase tracking-wider">
                        SERTIFIKAT DIGITAL RESMI
                    </div>
                    <h1 class="font-display-lg-mobile text-display-lg-mobile md:text-display-lg text-white uppercase leading-none mb-sm">
                        AMBIL <span class="text-primary">SERTIFIKATMU</span>
                    </h1>
                    <p class="font-body-lg text-surface-variant max-w-md mb-lg">
                        Turun arena, buktikan hasilnya. Masukkan NIK yang terdaftar pada pendaftaran untuk melihat dan mengunduh sertifikat keikutsertaan maupun kemenanganmu.
                    </p>
                    <div class="hidden lg:flex items-center gap-sm text-surface-variant font-label-sm uppercase tracking-widest">
                        <span class="material-symbols-outlined text-accent text-xl">workspace_premium</span>
                        Diterbitkan panitia · Siap cetak A4 · Gratis
                    </div>
                </div>

                <form method="POST" action="{{ route('certificates.public.lookup') }}"
                      class="bg-white border-2 border-on-background p-lg hard-shadow flex flex-col gap-md">
                    @csrf
                    <label for="nik" class="font-label-bold text-on-background uppercase">NIK Peserta</label>
                    <input id="nik" name="nik" type="text" inputmode="numeric" maxlength="16" pattern="[0-9]{16}" required
                           value="{{ old('nik') }}"
                           placeholder="0000000000000000"
                           class="w-full border-2 border-on-background px-md py-sm font-body-lg font-bold tracking-[0.3em] focus:outline-none focus:border-primary" />
                    @error('nik')
                        <p class="text-primary font-bold font-body-md">{{ $message }}</p>
                    @enderror
                    <p class="font-label-sm text-secondary">16 digit angka, tanpa spasi — sama seperti saat mendaftar.</p>
                    <button type="submit"
                            class="bg-primary text-on-primary font-headline-md uppercase px-lg py-sm border-2 border-on-background hard-shadow hover:bg-on-background transition-all inline-flex items-center justify-center gap-xs">
                        <span class="material-symbols-outlined text-lg">search</span>
                        Cari Sertifikat
                    </button>
                </form>
            </div>
        </div>
    </section>

    {{-- Cara kerja: urutan nyata, nomor = langkah --}}
    <section class="py-xl bg-surface-container-low border-y-2 border-on-background">
        <div class="container mx-auto px-lg">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-md">
                <div class="border-2 border-on-background bg-white p-lg flex gap-md items-start">
                    <span class="font-headline-lg text-headline-lg bg-accent text-on-accent border-2 border-on-background px-sm py-xs select-none">1</span>
                    <div>
                        <h3 class="font-headline-md uppercase mb-xs">Ikut Turnamen</h3>
                        <p class="font-body-md text-secondary">Daftar dan tanding di event Combat Pro mana pun lewat platform ini.</p>
                    </div>
                </div>
                <div class="border-2 border-on-background bg-white p-lg flex gap-md items-start">
                    <span class="font-headline-lg text-headline-lg bg-accent text-on-accent border-2 border-on-background px-sm py-xs select-none">2</span>
                    <div>
                        <h3 class="font-headline-md uppercase mb-xs">Terverifikasi Panitia</h3>
                        <p class="font-body-md text-secondary">Berkas dan pembayaranmu divalidasi panitia sebelum event berlangsung.</p>
                    </div>
                </div>
                <div class="border-2 border-on-background bg-white p-lg flex gap-md items-start">
                    <span class="font-headline-lg text-headline-lg bg-accent text-on-accent border-2 border-on-background px-sm py-xs select-none">3</span>
                    <div>
                        <h3 class="font-headline-md uppercase mb-xs">Unduh Sertifikat</h3>
                        <p class="font-body-md text-secondary">Masukkan NIK di atas — sertifikat langsung tersedia dalam format PDF.</p>
                    </div>
                </div>
            </div>
            <p class="font-body-md text-secondary mt-lg text-center">
                Peserta tanpa NIK? <span class="font-bold text-on-background">Hubungi panitia kontingenmu</span> untuk pencetakan sertifikat.
            </p>
        </div>
    </section>

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
