<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->name }} — {{ config('app.name', 'Combat Pro') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Google Fonts: Anton (display) + Hanken Grotesk (body) + Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Hanken+Grotesk:wght@400;500;700&display=swap"
          rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
          rel="stylesheet"/>

    <style>
        /* Page-local base reset (preflight is off project-wide). Scoped to this
           document only — other Blade templates are unaffected. */
        *, *::before, *::after {
            box-sizing: border-box;
            border-width: 0;
            border-style: solid;
            border-color: currentColor;
        }
        html { line-height: 1.5; -webkit-text-size-adjust: 100%; }
        body {
            margin: 0;
            font-family: 'Hanken Grotesk', sans-serif;
            background-color: #fbf9f8;
            color: #1b1c1c;
        }
        h1, h2, h3, h4, h5, h6 { font-size: inherit; font-weight: inherit; margin: 0; }
        p, figure { margin: 0; }
        img, svg, video { display: block; max-width: 100%; }
        button { font: inherit; color: inherit; background-color: transparent; cursor: pointer; }
        a { color: inherit; text-decoration: none; }

        .container { width: 100%; max-width: 1280px; margin-left: auto; margin-right: auto; }

        .hard-shadow { box-shadow: 4px 4px 0px 0px #1b1c1c; transition: box-shadow .12s ease, transform .12s ease; }
        .hard-shadow-red { box-shadow: 4px 4px 0px 0px #b9001c; }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-surface text-on-background font-body-md selection:bg-primary selection:text-white">

    @php
        // Resolve the primary CTA target/label once, auth-aware.
        if (auth()->check()) {
            $ctaHref = route('dashboard');
            $ctaLabel = 'DASHBOARD';
        } elseif (Route::has('register')) {
            $ctaHref = route('register');
            $ctaLabel = 'DAFTAR SEKARANG';
        } else {
            $ctaHref = route('login');
            $ctaLabel = 'LOGIN / DAFTAR';
        }
    @endphp

    <!-- Top Navigation Bar (same as landing) -->
    <nav class="flex justify-between items-center px-md py-sm w-full sticky top-0 z-50 bg-surface border-b-2 border-on-background">
        <a href="{{ route('landing') }}" class="font-display-lg-mobile text-display-lg-mobile text-primary italic uppercase tracking-tighter">
            COMBAT <span class="text-on-background">PRO</span>
        </a>
        <div class="hidden md:flex gap-lg items-center">
            <a class="font-label-bold text-label-bold text-on-background hover:border-b-4 hover:border-accent transition-all duration-100 ease-in-out px-xs" href="{{ route('landing') }}#events">TOURNAMENTS</a>
            <a class="font-label-bold text-label-bold text-on-background hover:border-b-4 hover:border-accent transition-all duration-100 ease-in-out px-xs" href="{{ route('landing') }}#about">ABOUT</a>
            <a class="font-label-bold text-label-bold text-on-background hover:border-b-4 hover:border-accent transition-all duration-100 ease-in-out px-xs" href="{{ route('landing') }}#contact">CONTACT</a>
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

    <!-- Hero Section -->
    <section class="relative min-h-[80vh] flex flex-col md:flex-row bg-on-background overflow-hidden">
        <!-- Poster -->
        <div class="w-full md:w-1/2 relative h-[50vh] md:h-auto group">
            <img alt="{{ $event->name }}" class="w-full h-full object-cover grayscale-[20%] group-hover:grayscale-0 transition-all duration-700"
                 src="{{ $event->image_url }}" />
            <div class="absolute inset-0 bg-gradient-to-t from-on-background via-transparent to-transparent md:bg-gradient-to-r md:via-on-background/40"></div>
        </div>
        <!-- Content -->
        <div class="w-full md:w-1/2 p-md md:p-xl flex flex-col justify-center relative z-10">
            <div class="inline-block self-start bg-primary px-sm py-1 mb-md">
                <span class="font-label-bold text-label-sm text-on-primary tracking-widest uppercase">{{ $event->statusLabel() }}</span>
            </div>
            <h1 class="font-display-lg text-display-lg-mobile md:text-display-lg text-white uppercase leading-none mb-sm">
                {{ $event->name }}
            </h1>
            <p class="font-headline-md text-primary mb-lg uppercase">
                {{ $event->formatted_date }} <span class="text-surface-variant">· Lokasi akan diumumkan</span>
            </p>
            <div class="flex flex-col sm:flex-row gap-md mt-lg">
                <a href="{{ $ctaHref }}"
                   class="bg-primary text-on-primary font-display-lg text-headline-md px-lg py-sm h-16 hard-shadow-red hover:bg-white hover:text-on-background transition-all transform active:translate-y-1 flex items-center justify-center text-center">
                    {{ $ctaLabel }}
                </a>
            </div>
        </div>
    </section>

    <!-- Main Info Grid -->
    <section class="max-w-container-max mx-auto px-md py-xl grid grid-cols-1 md:grid-cols-12 gap-lg">

        <!-- Sidebar: Key Details -->
        <aside class="md:col-span-4 flex flex-col gap-lg">
            <!-- Biaya Pendaftaran -->
            <div class="border-2 border-on-background p-md bg-white hard-shadow">
                <div class="bg-on-background text-white px-md py-2 -mx-md -mt-md mb-md font-display-lg text-headline-md">
                    BIAYA PENDAFTARAN
                </div>
                <div class="space-y-md">
                    <div class="flex justify-between items-end border-b border-secondary pb-xs gap-sm">
                        <span class="font-label-bold">BIAYA EVENT</span>
                        <span class="font-display-lg text-headline-md text-primary whitespace-nowrap">Rp {{ number_format((float) $event->event_fee, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-end border-b border-secondary pb-xs gap-sm">
                        <span class="font-label-bold">BIAYA PELATIH</span>
                        <span class="font-display-lg text-headline-md text-primary whitespace-nowrap">Rp {{ number_format((float) $event->coach_fee, 0, ',', '.') }}</span>
                    </div>
                </div>
                @if ($event->registration_deadline)
                    <p class="text-label-sm text-secondary mt-md italic">
                        *Pendaftaran ditutup pada {{ $event->registration_deadline->translatedFormat('d M Y') }}
                    </p>
                @endif
            </div>

            <!-- Jadwal (placeholder) -->
            <div class="border-2 border-on-background p-md bg-white hard-shadow">
                <div class="bg-on-background text-white px-md py-2 -mx-md -mt-md mb-md font-display-lg text-headline-md">
                    JADWAL
                </div>
                <div class="border-2 border-dashed border-secondary p-md text-center flex flex-col items-center gap-sm">
                    <span class="material-symbols-outlined text-secondary" style="font-size:40px">schedule</span>
                    <p class="font-label-bold text-secondary">Jadwal pertandingan akan diumumkan menjelang hari-H.</p>
                </div>
            </div>
        </aside>

        <!-- Content Area -->
        <div class="md:col-span-8 flex flex-col gap-xl">

            <!-- Tentang Event (placeholder copy referencing real data) -->
            <div>
                <h2 class="font-display-lg text-display-lg-mobile border-l-8 border-primary pl-md mb-md uppercase">
                    Tentang Event
                </h2>
                <p class="font-body-lg text-secondary leading-relaxed mb-md">
                    <strong class="text-on-background">{{ $event->name }}</strong> adalah bagian dari seri kejuaraan Combat Pro yang diselenggarakan pada {{ $event->formatted_date }}. Kompetisi ini terbuka bagi para karateka untuk membuktikan ketangkasan, disiplin, dan semangat bertarung di atas matras.
                </p>
                <p class="font-body-lg text-secondary leading-relaxed">
                    Detail teknis, peraturan, dan informasi penyelenggaraan akan diperbarui oleh panitia menjelang hari pertandingan. Pastikan akunmu aktif agar menerima pembaruan terkini.
                </p>
            </div>

            <!-- Kategori Pertandingan (data-driven) -->
            <div>
                <h2 class="font-display-lg text-display-lg-mobile border-l-8 border-primary pl-md mb-md uppercase">
                    Kategori Pertandingan
                </h2>

                @if ($groupedCategories->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-md">
                        @foreach ($groupedCategories as $type => $categories)
                            <div class="border-2 border-on-background p-md bg-white">
                                <div class="flex items-center gap-sm mb-md">
                                    <span class="bg-primary text-white px-md py-1 font-display-lg uppercase">{{ $type }}</span>
                                    <span class="material-symbols-outlined text-primary">{{ $type === 'Open' ? 'sports_kabaddi' : 'emoji_events' }}</span>
                                </div>
                                <ul class="space-y-sm font-label-bold">
                                    @foreach ($categories as $category)
                                        @php
                                            $range = ($category->min_birth_date && $category->max_birth_date)
                                                ? 'Lahir ' . $category->min_birth_date->translatedFormat('M Y') . ' – ' . $category->max_birth_date->translatedFormat('M Y')
                                                : 'Semua umur';
                                        @endphp
                                        <li class="flex justify-between border-b border-surface-container py-1 gap-sm">
                                            <span>{{ $category->class_name }}</span>
                                            <span class="text-secondary text-right">{{ $range }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="border-2 border-dashed border-secondary p-lg text-center flex flex-col items-center gap-sm">
                        <span class="material-symbols-outlined text-secondary" style="font-size:40px">category</span>
                        <p class="font-label-bold text-secondary">Kategori pertandingan belum ditambahkan untuk event ini.</p>
                    </div>
                @endif
            </div>

            <!-- Venue / Lokasi (placeholder) -->
            <div class="border-2 border-on-background hard-shadow relative h-64 overflow-hidden flex items-center justify-center bg-surface-container-low">
                <div class="absolute inset-0 bg-on-background/5"></div>
                <div class="text-center z-10 px-md flex flex-col items-center">
                    <span class="material-symbols-outlined text-primary" style="font-size:48px">location_on</span>
                    <p class="font-display-lg text-headline-md mt-sm uppercase">Venue Location</p>
                    <p class="font-label-bold text-secondary mt-xs">Lokasi &amp; venue akan diumumkan oleh panitia.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-primary py-xl text-white">
        <div class="max-w-container-max mx-auto px-md text-center">
            <h2 class="font-display-lg text-display-lg-mobile md:text-display-lg mb-md uppercase">Siap Untuk Bertanding?</h2>
            <p class="font-body-lg mb-lg max-w-2xl mx-auto opacity-90">
                Ambil langkah pertamamu menuju podium juara. Daftar sekarang dan tunjukkan semangat bertarungmu di ajang Combat Pro.
            </p>
            <div class="flex justify-center">
                <a href="{{ $ctaHref }}"
                   class="bg-on-background text-white font-display-lg text-headline-md px-xl py-md h-20 border-2 border-white hover:bg-white hover:text-on-background transition-all flex items-center justify-center">
                    {{ $ctaLabel }}
                </a>
            </div>
        </div>
    </section>

    <!-- Footer (same as landing) -->
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
            <a class="font-label-sm text-label-sm uppercase tracking-widest text-secondary hover:text-accent opacity-80 hover:opacity-100 transition-all" href="#">Privacy Policy</a>
            <a class="font-label-sm text-label-sm uppercase tracking-widest text-secondary hover:text-accent opacity-80 hover:opacity-100 transition-all" href="#">Terms of Service</a>
            <a class="font-label-sm text-label-sm uppercase tracking-widest text-secondary hover:text-accent opacity-80 hover:opacity-100 transition-all" href="#">Contact</a>
        </div>
    </footer>
</body>
</html>
