<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Combat Pro') }}</title>

    <!-- Tailwind + app assets, compiled by Vite (theme tokens live in tailwind.config.js) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Google Fonts: Anton (display) + Hanken Grotesk (body) + Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Hanken+Grotesk:wght@400;500;700&display=swap"
          rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
          rel="stylesheet"/>

    <style>
        /* Page-local base reset. Preflight is disabled project-wide, so we
           reproduce only the parts the landing layout depends on (box-sizing,
           solid borders so .border-2 actually shows, zeroed heading/body
           margins, block images). Scoped to this document only — other
           Blade templates are unaffected. */
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

        /* Layout: .container is disabled in corePlugins, so define it here. */
        .container { width: 100%; max-width: 1280px; margin-left: auto; margin-right: auto; }

        /* Signature hand-drawn shadows + clip used across the combat theme. */
        .hard-shadow { box-shadow: 4px 4px 0px 0px #1b1c1c; transition: box-shadow .12s ease, transform .12s ease; }
        .hard-shadow-red { box-shadow: 4px 4px 0px 0px #b9001c; }
        .strike-clip { clip-path: polygon(10% 0, 100% 0, 90% 100%, 0 100%); }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-surface text-on-background font-body-md selection:bg-primary selection:text-white">

    <!-- Top Navigation Bar -->
    <nav class="flex justify-between items-center px-md py-sm w-full sticky top-0 z-50 bg-surface border-b-2 border-on-background">
        <div class="font-display-lg-mobile text-display-lg-mobile text-primary italic uppercase tracking-tighter">
            COMBAT <span class="text-on-background">PRO</span>
        </div>

        <!-- Desktop Links -->
        <div class="hidden md:flex gap-lg items-center">
            <a class="font-label-bold text-label-bold text-primary border-b-4 border-accent transition-all duration-100 ease-in-out active:translate-y-0.5 px-xs" href="#events">TOURNAMENTS</a>
            <a class="font-label-bold text-label-bold text-on-background hover:border-b-4 hover:border-accent transition-all duration-100 ease-in-out active:translate-y-0.5 px-xs" href="#about">ABOUT</a>
            <a class="font-label-bold text-label-bold text-on-background hover:border-b-4 hover:border-accent transition-all duration-100 ease-in-out active:translate-y-0.5 px-xs" href="#contact">CONTACT</a>
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
    <section class="relative min-h-[90vh] flex items-center overflow-hidden bg-on-background">
        <div class="absolute inset-0 opacity-40">
            <img alt="Atlet Bertanding" class="w-full h-full object-cover"
                 src="https://images.unsplash.com/photo-1555597673-b21d5c935865?w=1920&q=80"
                 onerror="this.style.display='none';" />
        </div>
        <div class="absolute inset-0 bg-gradient-to-r from-on-background via-on-background/60 to-transparent"></div>

        <div class="container mx-auto px-lg relative z-10">
            <div class="max-w-3xl">
                <div class="bg-accent text-on-accent inline-block px-sm py-xs font-label-bold mb-md uppercase tracking-wider">
                    OFFICIAL TOURNAMENT PLATFORM
                </div>
                <h1 class="font-display-lg text-display-lg text-white uppercase leading-none mb-sm">
                    DOMINASI <span class="text-primary">ARENA</span>
                </h1>
                <p class="font-body-lg text-surface-variant max-w-xl mb-lg">
                    Platform turnamen olahraga tercanggih untuk para atlet sejati. Presisi dalam manajemen, kekuatan dalam eksekusi. Buktikan kemampuanmu sekarang.
                </p>
                <div class="flex flex-col sm:flex-row gap-md">
                    @guest
                    @if(Route::has('register'))
                    <a href="{{ route('register') }}" class="bg-primary text-on-primary font-headline-md px-lg py-sm border-2 border-white hard-shadow hover:bg-white hover:text-on-background transition-all text-center">
                        DAFTAR SEKARANG
                    </a>
                    @else
                    <a href="{{ route('login') }}" class="bg-primary text-on-primary font-headline-md px-lg py-sm border-2 border-white hard-shadow hover:bg-white hover:text-on-background transition-all text-center">
                        LOGIN / DAFTAR
                    </a>
                    @endif
                    @else
                    <a href="{{ route('dashboard') }}" class="bg-primary text-on-primary font-headline-md px-lg py-sm border-2 border-white hard-shadow hover:bg-white hover:text-on-background transition-all text-center">
                        DASHBOARD
                    </a>
                    @endguest
                    <a href="#events" class="bg-transparent text-white font-headline-md px-lg py-sm border-2 border-white hover:border-accent hover:text-accent transition-all text-center">
                        LIHAT JADWAL
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Bento Grid -->
    <section class="py-xl container mx-auto px-lg">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-md">
            <!-- Feature 1 -->
            <div class="border-2 border-on-background p-lg bg-white relative group overflow-hidden">
                <div class="absolute top-0 right-0 p-sm text-accent">
                    <span class="material-symbols-outlined text-4xl">notifications_active</span>
                </div>
                <h3 class="font-headline-md mb-sm uppercase">Quick Notifications</h3>
                <p class="font-body-md text-secondary">Update real-time jadwal pertandingan dan perubahan bagan langsung ke perangkatmu. Jangan lewatkan satu detik pun.</p>
                <div class="mt-md h-1 w-0 bg-accent group-hover:w-full transition-all duration-300"></div>
            </div>

            <!-- Feature 2 -->
            <div class="border-2 border-on-background p-lg bg-on-background text-white relative group overflow-hidden">
                <div class="absolute top-0 right-0 p-sm text-accent">
                    <span class="material-symbols-outlined text-4xl">app_registration</span>
                </div>
                <h3 class="font-headline-md mb-sm uppercase">Easy Registration</h3>
                <p class="font-body-md text-surface-variant">Pendaftaran digital yang efisien untuk klub, tim, atau individu. Upload berkas dan verifikasi hanya dalam hitungan menit.</p>
                <div class="mt-md h-1 w-0 bg-accent group-hover:w-full transition-all duration-300"></div>
            </div>

            <!-- Feature 3 -->
            <div class="border-2 border-on-background p-lg bg-white relative group overflow-hidden">
                <div class="absolute top-0 right-0 p-sm text-accent">
                    <span class="material-symbols-outlined text-4xl">account_tree</span>
                </div>
                <h3 class="font-headline-md mb-sm uppercase">Live Brackets</h3>
                <p class="font-body-md text-secondary">Sistem bagan otomatis yang transparan. Pantau kemenanganmu menuju podium juara secara live di layar utama.</p>
                <div class="mt-md h-1 w-0 bg-accent group-hover:w-full transition-all duration-300"></div>
            </div>
        </div>
    </section>

    <!-- Upcoming Events -->
    <section id="events" class="py-xl bg-surface-container-low border-y-2 border-on-background">
        <div class="container mx-auto px-lg">
            <div class="flex flex-col md:flex-row justify-between items-end mb-xl gap-md">
                <div>
                    <h2 class="font-display-lg-mobile md:text-display-lg text-on-background uppercase leading-none">
                        UPCOMING <br/><span class="text-primary">EVENTS</span>
                    </h2>
                </div>
                <div class="hidden md:block w-32 h-2 bg-accent mb-base"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-lg">
                <!-- Data events - diambil dari database via LandingController -->
                @foreach(($upcomingEvents ?? [
                    [
                        'id' => 1,
                        'date' => '12 OKT 2024',
                        'type' => 'OPEN',
                        'title' => 'KEJUARAAN NASIONAL OPEN',
                        'location' => 'ISTORA SENAYAN, JAKARTA',
                        'image' => 'https://images.unsplash.com/photo-1555597673-b21d5c935865?w=800&q=80'
                    ],
                    [
                        'id' => 2,
                        'date' => '25 NOV 2024',
                        'type' => 'CHAMPIONSHIP',
                        'title' => 'PROVINCIAL CHAMPIONSHIP',
                        'location' => 'GOR CITRA, BANDUNG',
                        'image' => 'https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?w=800&q=80'
                    ],
                    [
                        'id' => 3,
                        'date' => '05 DES 2024',
                        'type' => 'JUNIOR',
                        'title' => 'JUNIOR SPORTS CUP',
                        'location' => 'DBL ARENA, SURABAYA',
                        'image' => 'https://images.unsplash.com/photo-1574620053332-6ed12729cc44?w=800&q=80'
                    ]
                ]) as $event)
                <div class="flex flex-col border-2 border-on-background bg-white group">
                    <div class="aspect-[3/4] w-full overflow-hidden border-b-2 border-on-background">
                        <img alt="{{ $event['title'] }}" class="w-full h-full object-cover" src="{{ $event['image'] }}" />
                    </div>
                    <div class="bg-on-background text-white p-sm font-label-bold uppercase flex justify-between items-center">
                        <span>{{ $event['date'] }}</span>
                        <span class="bg-accent text-on-accent px-xs">{{ $event['type'] }}</span>
                    </div>
                    <div class="p-md flex-grow">
                        <h4 class="font-headline-md mb-xs">{{ $event['title'] }}</h4>
                        <p class="text-secondary font-label-sm mb-md flex items-center gap-xs">
                            <span class="material-symbols-outlined text-sm text-accent">location_on</span> {{ $event['location'] }}
                        </p>
                    </div>
                    <div class="p-md pt-0">
                        <a href="{{ route('events.show', $event['id']) }}" class="block w-full border-2 border-on-background py-sm font-headline-md uppercase text-center group-hover:bg-on-background group-hover:text-white transition-all">
                            Detail Event
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Registration Status Section -->
    <section class="py-xl container mx-auto px-lg">
        <div class="bg-primary p-lg md:p-xl border-4 border-on-background hard-shadow-red relative overflow-hidden">
            <!-- Decorative "Strike" bg -->
            <div class="absolute -right-20 top-0 text-accent opacity-20 font-display-lg select-none pointer-events-none rotate-12">
                STRIKE STRIKE STRIKE
            </div>

            <div class="relative z-10 grid md:grid-cols-2 gap-lg items-center">
                <div>
                    <h2 class="font-headline-lg text-white uppercase mb-sm">
                        MULAI LANGKAHMU <br/>DI <span class="text-accent">ARENA</span>
                    </h2>
                    <p class="text-primary-fixed font-body-lg">
                        Daftarkan dirimu atau cek status pendaftaran yang sedang berlangsung. Cukup masukkan email atau ID pendaftaran.
                    </p>
                </div>

                <div class="bg-white p-lg border-2 border-on-background hard-shadow">
                    <form action="{{ route('check-status') ?? '#' }}" method="POST" class="flex flex-col gap-md">
                        @csrf
                        <div>
                            <label class="block font-label-bold text-on-background mb-xs uppercase">Email / ID Pendaftaran</label>
                            <input class="w-full border-2 border-on-background p-sm focus:outline-none focus:ring-0 focus:border-accent transition-all"
                                   placeholder="Masukkan ID anda..." type="text" name="registration_id" />
                        </div>
                        <div class="flex flex-col sm:flex-row gap-md">
                            @guest
                            <button type="submit" class="flex-1 bg-on-background text-white font-headline-md py-sm hover:bg-accent hover:text-on-accent transition-all uppercase">
                                Cek Status
                            </button>
                            @if(Route::has('register'))
                            <a href="{{ route('register') }}" class="flex-1 border-2 border-on-background text-on-background font-headline-md py-sm hover:bg-on-background hover:text-white transition-all uppercase text-center">
                                Pendaftaran Baru
                            </a>
                            @else
                            <a href="{{ route('login') }}" class="flex-1 border-2 border-on-background text-on-background font-headline-md py-sm hover:bg-on-background hover:text-white transition-all uppercase text-center">
                                Login Untuk Daftar
                            </a>
                            @endif
                            @else
                            <a href="{{ route('participants.index') }}" class="flex-1 bg-on-background text-white font-headline-md py-sm hover:bg-accent hover:text-on-accent transition-all uppercase text-center">
                                Cek Status Peserta
                            </a>
                            <a href="{{ route('participants.create') }}" class="flex-1 border-2 border-on-background text-on-background font-headline-md py-sm hover:bg-on-background hover:text-white transition-all uppercase text-center">
                                Peserta Baru
                            </a>
                            @endguest
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-xl container mx-auto px-lg">
        <div class="grid md:grid-cols-2 gap-xl items-center">
            <div>
                <h2 class="font-display-lg-mobile md:text-headline-lg text-on-background uppercase mb-md">
                    TENTANG <span class="text-primary">PLATFORM</span>
                </h2>
                <p class="font-body-lg text-secondary mb-lg">
                    Kami adalah platform turnamen olahraga terdepan yang menghubungkan atlet, pelatih, dan penyelenggara dari berbagai cabang olahraga dalam satu ekosistem digital yang terintegrasi.
                </p>
                <div class="space-y-md">
                    <div class="flex items-start gap-md">
                        <span class="material-symbols-outlined text-accent text-3xl">verified</span>
                        <div>
                            <h4 class="font-headline-md uppercase mb-xs">Sistem Terintegrasi</h4>
                            <p class="font-body-md text-secondary">Manajemen peserta, jadwal, dan hasil pertandingan dalam satu platform.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-md">
                        <span class="material-symbols-outlined text-accent text-3xl">speed</span>
                        <div>
                            <h4 class="font-headline-md uppercase mb-xs">Proses Cepat</h4>
                            <p class="font-body-md text-secondary">Pendaftaran dan verifikasi data dalam hitungan menit, bukan hari.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-md">
                        <span class="material-symbols-outlined text-accent text-3xl">security</span>
                        <div>
                            <h4 class="font-headline-md uppercase mb-xs">Data Aman</h4>
                            <p class="font-body-md text-secondary">Sistem keamanan tingkat tinggi untuk melindungi data sensitif atlet.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-4 border-on-background hard-shadow">
                <img src="https://images.unsplash.com/photo-1571902943202-507ec2618e8f?w=800&q=80"
                     alt="Latihan Olahraga"
                     class="w-full h-auto"
                     onerror="this.src='https://images.unsplash.com/photo-1555597673-b21d5c935865?w=800&q=80'; this.onerror=null;" />
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-xl bg-on-background text-surface-variant">
        <div class="container mx-auto px-lg">
            <div class="text-center max-w-2xl mx-auto mb-xl">
                <h2 class="font-display-lg-mobile md:text-display-lg text-white uppercase mb-md">
                    HUBUNGI <span class="text-accent">KAMI</span>
                </h2>
                <p class="font-body-lg text-surface-variant">
                    Punya pertanyaan atau membutuhkan bantuan? Tim kami siap membantu Anda.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-lg">
                <div class="text-center">
                    <span class="material-symbols-outlined text-accent text-5xl mb-md">email</span>
                    <h4 class="font-headline-md uppercase mb-xs text-white">Email</h4>
                    <p class="font-label-sm text-surface-variant">info@combatpro.id</p>
                </div>
                <div class="text-center">
                    <span class="material-symbols-outlined text-accent text-5xl mb-md">phone</span>
                    <h4 class="font-headline-md uppercase mb-xs text-white">Telepon</h4>
                    <p class="font-label-sm text-surface-variant">+62 21 1234 5678</p>
                </div>
                <div class="text-center">
                    <span class="material-symbols-outlined text-accent text-5xl mb-md">location_on</span>
                    <h4 class="font-headline-md uppercase mb-xs text-white">Alamat</h4>
                    <p class="font-label-sm text-surface-variant">Jakarta, Indonesia</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
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

    <script>
        // Micro-interaction for the registration card shadow
        const regCard = document.querySelector('.hard-shadow');
        if (regCard) {
            regCard.addEventListener('mouseenter', () => {
                regCard.style.transform = 'translate(-2px, -2px)';
                regCard.style.boxShadow = '6px 6px 0px 0px #1b1c1c';
            });
            regCard.addEventListener('mouseleave', () => {
                regCard.style.transform = 'translate(0px, 0px)';
                regCard.style.boxShadow = '4px 4px 0px 0px #1b1c1c';
            });
        }

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href === '#' || href.length < 2) return;
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>
