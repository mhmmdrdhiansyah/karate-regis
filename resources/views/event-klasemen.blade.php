<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Klasemen | {{ $event->name }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/media/logos/logo3.png') }}" />
    <!-- Use Tailwind CDN as requested by the original HTML template to ensure perfect matching -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Anton&amp;family=Hanken+Grotesk:wght@400;500;700&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "secondary": "#5f5e5e",
                        "primary-container": "#e21d2c",
                        "surface-variant": "#e4e2e2",
                        "on-secondary-fixed-variant": "#474746",
                        "on-secondary-container": "#636262",
                        "on-surface-variant": "#5d3f3d",
                        "on-primary-fixed-variant": "#930014",
                        "outline-variant": "#e7bdb9",
                        "on-surface": "#1b1c1c",
                        "primary-fixed-dim": "#ffb3ae",
                        "inverse-primary": "#ffb3ae",
                        "surface-dim": "#dbdad9",
                        "secondary-fixed": "#e5e2e1",
                        "on-tertiary-fixed": "#1a1c1c",
                        "on-error": "#ffffff",
                        "primary-fixed": "#ffdad7",
                        "surface-container-highest": "#e4e2e2",
                        "tertiary-container": "#727474",
                        "surface": "#fbf9f8",
                        "surface-tint": "#c0001d",
                        "inverse-on-surface": "#f2f0f0",
                        "surface-container": "#efeded",
                        "secondary-fixed-dim": "#c8c6c5",
                        "on-primary": "#ffffff",
                        "on-primary-fixed": "#410004",
                        "tertiary-fixed": "#e2e2e2",
                        "background": "#fbf9f8",
                        "surface-container-low": "#f5f3f3",
                        "error-container": "#ffdad6",
                        "secondary-container": "#e2dfde",
                        "surface-container-lowest": "#ffffff",
                        "on-tertiary-fixed-variant": "#454747",
                        "on-background": "#1b1c1c",
                        "outline": "#926f6c",
                        "tertiary": "#5a5b5c",
                        "on-secondary": "#ffffff",
                        "on-primary-container": "#fff9f8",
                        "on-tertiary": "#ffffff",
                        "on-error-container": "#93000a",
                        "surface-bright": "#fbf9f8",
                        "inverse-surface": "#303031",
                        "primary": "#b9001c",
                        "error": "#ba1a1a",
                        "surface-container-high": "#e9e8e7",
                        "on-tertiary-container": "#fbfbfb",
                        "on-secondary-fixed": "#1c1b1b",
                        "tertiary-fixed-dim": "#c6c6c7",
                        "medal-gold": "#FFD700",
                        "medal-silver": "#C0C0C0",
                        "medal-bronze": "#CD7F32"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "sm": "12px",
                        "gutter": "24px",
                        "xl": "80px",
                        "xs": "4px",
                        "lg": "48px",
                        "md": "24px",
                        "base": "8px",
                        "container-max": "1280px"
                    },
                    "fontFamily": {
                        "headline-md": ["Anton"],
                        "body-md": ["Hanken Grotesk"],
                        "display-lg-mobile": ["Anton"],
                        "label-sm": ["Hanken Grotesk"],
                        "label-bold": ["Hanken Grotesk"],
                        "display-lg": ["Anton"],
                        "headline-lg": ["Anton"],
                        "body-lg": ["Hanken Grotesk"]
                    },
                    "fontSize": {
                        "headline-md": ["32px", { "lineHeight": "36px", "letterSpacing": "0.01em", "fontWeight": "400" }],
                        "body-md": ["16px", { "lineHeight": "24px", "fontWeight": "400" }],
                        "display-lg-mobile": ["48px", { "lineHeight": "48px", "letterSpacing": "0.02em", "fontWeight": "400" }],
                        "label-sm": ["12px", { "lineHeight": "16px", "fontWeight": "500" }],
                        "label-bold": ["14px", { "lineHeight": "20px", "letterSpacing": "0.05em", "fontWeight": "700" }],
                        "display-lg": ["72px", { "lineHeight": "72px", "letterSpacing": "0.02em", "fontWeight": "400" }],
                        "headline-lg": ["40px", { "lineHeight": "44px", "letterSpacing": "0.01em", "fontWeight": "400" }],
                        "body-lg": ["18px", { "lineHeight": "28px", "fontWeight": "400" }]
                    }
                },
            },
        }
    </script>
    <style>
        .hard-shadow {
            box-shadow: 4px 4px 0px 0px rgba(0, 0, 0, 1);
        }
        .hard-shadow-red {
            box-shadow: 4px 4px 0px 0px #b9001c;
        }
        .rank-row:hover {
            background-color: #1b1c1c;
            color: #ffffff;
        }
        .rank-row:hover .medal-count {
            color: #ffffff;
        }
        .active-filter {
            background-color: #b9001c;
            color: #ffffff;
        }
    </style>
</head>
<body class="bg-surface font-body-md text-on-surface selection:bg-primary selection:text-white">
    <!-- TopNavBar -->
    <header class="bg-surface dark:bg-inverse-surface border-b-2 border-on-surface dark:border-surface-variant sticky top-0 z-50">
        <div class="flex justify-between items-center w-full px-gutter py-sm max-w-container-max mx-auto">
            <div class="font-headline-lg text-headline-lg text-primary dark:text-primary-fixed-dim tracking-tighter uppercase">
                <a href="{{ route('landing') }}">COMBAT PRO</a>
            </div>
            <nav class="hidden md:flex gap-md items-center">
                <a class="text-on-surface dark:text-on-surface-variant hover:text-primary transition-colors font-body-md text-body-md" href="{{ route('landing') }}#events">Tournaments</a>
                <a class="text-on-surface dark:text-on-surface-variant hover:text-primary transition-colors font-body-md text-body-md" href="{{ route('certificates.public.index') }}">Cek Sertifikat</a>
                <a class="text-on-surface dark:text-on-surface-variant hover:text-primary transition-colors font-body-md text-body-md" href="{{ route('landing') }}#about">About</a>
                <a class="text-on-surface dark:text-on-surface-variant hover:text-primary transition-colors font-body-md text-body-md" href="{{ route('landing') }}#contact">Contact</a>
            </nav>
            <div class="flex items-center gap-sm">
                @guest
                <a href="{{ route('login') }}" class="bg-primary text-white font-headline-md px-6 py-2 uppercase tracking-wide hover:bg-on-surface transition-all active:scale-95">Sign In</a>
                @else
                <a href="{{ route('dashboard') }}" class="bg-primary text-white font-headline-md px-6 py-2 uppercase tracking-wide hover:bg-on-surface transition-all active:scale-95">Dashboard</a>
                @endguest
            </div>
        </div>
    </header>

    <main class="max-w-container-max mx-auto px-gutter py-lg">
        <!-- Header Section -->
        <section class="mb-xl flex flex-col justify-between items-start gap-md">
            <div>
                <a href="{{ route('events.show', $event->id) }}" class="inline-flex items-center gap-xs font-label-bold uppercase hover:text-primary transition-all mb-4">
                    <span class="material-symbols-outlined text-sm">arrow_back</span> Kembali ke Detail Event
                </a>
                <h1 class="font-headline-lg text-headline-lg md:text-display-lg uppercase mb-xs leading-none">
                    KLASEMEN <span class="text-primary">PEROLEHAN MEDALI</span>
                </h1>
                <p class="font-body-lg text-body-lg text-secondary">
                    Peringkat kontingen berdasarkan prestasi di turnamen {{ $event->name }}.
                </p>
            </div>
        </section>

        @if(empty($standings))
            <div class="bg-white border-2 border-on-surface p-xl text-center my-xl hard-shadow">
                <span class="material-symbols-outlined text-primary text-6xl mb-4">emoji_events</span>
                <h3 class="font-headline-lg uppercase mb-2">Belum Ada Hasil</h3>
                <p class="font-body-lg text-secondary">Pertandingan belum dimulai atau hasil belum dimasukkan oleh panitia.</p>
            </div>
        @else
            <!-- Top 3 Podium (Visual Focus) -->
            <section class="grid grid-cols-1 md:grid-cols-3 gap-lg mb-xl items-end mt-12">
                @php
                    $top3 = array_slice($standings, 0, 3);
                    $first = $top3[0] ?? null;
                    $second = $top3[1] ?? null;
                    $third = $top3[2] ?? null;
                @endphp

                <!-- 2nd Place -->
                @if($second)
                <div class="order-2 md:order-1 flex flex-col items-center">
                    <div class="w-full bg-white border-2 border-on-surface p-md text-center hard-shadow relative">
                        <div class="absolute -top-6 left-1/2 -translate-x-1/2 bg-medal-silver text-on-surface font-headline-md px-4 py-1 border-2 border-on-surface">2</div>
                        <h3 class="font-headline-md text-headline-md uppercase mb-sm">{{ $second->name }}</h3>
                        <div class="flex justify-center gap-md">
                            <div class="text-center"><span class="block text-medal-gold font-bold">{{ $second->gold }}</span><span class="text-xs uppercase font-bold">Emas</span></div>
                            <div class="text-center"><span class="block text-medal-silver font-bold">{{ $second->silver }}</span><span class="text-xs uppercase font-bold">Perak</span></div>
                            <div class="text-center"><span class="block text-medal-bronze font-bold">{{ $second->bronze }}</span><span class="text-xs uppercase font-bold">Prunggu</span></div>
                        </div>
                    </div>
                </div>
                @else
                <div class="order-2 md:order-1 flex flex-col items-center"></div>
                @endif

                <!-- 1st Place -->
                @if($first)
                <div class="order-1 md:order-2 flex flex-col items-center">
                    <div class="w-full bg-on-surface text-surface border-2 border-on-surface p-lg text-center hard-shadow-red relative -top-4">
                        <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-medal-gold text-on-surface font-headline-md px-6 py-2 border-2 border-on-surface animate-bounce">1</div>
                        <h3 class="font-headline-lg text-headline-lg uppercase mb-sm">{{ $first->name }}</h3>
                        <div class="flex justify-center gap-lg">
                            <div class="text-center"><span class="block text-medal-gold font-headline-md">{{ $first->gold }}</span><span class="text-sm uppercase font-bold text-surface-variant">Emas</span></div>
                            <div class="text-center"><span class="block text-medal-silver font-headline-md">{{ $first->silver }}</span><span class="text-sm uppercase font-bold text-surface-variant">Perak</span></div>
                            <div class="text-center"><span class="block text-medal-bronze font-headline-md">{{ $first->bronze }}</span><span class="text-sm uppercase font-bold text-surface-variant">Prunggu</span></div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- 3rd Place -->
                @if($third)
                <div class="order-3 md:order-3 flex flex-col items-center">
                    <div class="w-full bg-white border-2 border-on-surface p-md text-center hard-shadow relative">
                        <div class="absolute -top-6 left-1/2 -translate-x-1/2 bg-medal-bronze text-white font-headline-md px-4 py-1 border-2 border-on-surface">3</div>
                        <h3 class="font-headline-md text-headline-md uppercase mb-sm">{{ $third->name }}</h3>
                        <div class="flex justify-center gap-md">
                            <div class="text-center"><span class="block text-medal-gold font-bold">{{ $third->gold }}</span><span class="text-xs uppercase font-bold">Emas</span></div>
                            <div class="text-center"><span class="block text-medal-silver font-bold">{{ $third->silver }}</span><span class="text-xs uppercase font-bold">Perak</span></div>
                            <div class="text-center"><span class="block text-medal-bronze font-bold">{{ $third->bronze }}</span><span class="text-xs uppercase font-bold">Prunggu</span></div>
                        </div>
                    </div>
                </div>
                @endif
            </section>

            <!-- Detailed Rankings Table -->
            <section class="overflow-x-auto mt-8 mb-xl">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-on-surface text-surface uppercase font-headline-md tracking-wider">
                            <th class="p-md text-left border-2 border-on-surface w-20">Rank</th>
                            <th class="p-md text-left border-2 border-on-surface">Kontingen</th>
                            <th class="p-md text-center border-2 border-on-surface w-32">Emas</th>
                            <th class="p-md text-center border-2 border-on-surface w-32">Perak</th>
                            <th class="p-md text-center border-2 border-on-surface w-32">Perunggu</th>
                            <th class="p-md text-center border-2 border-on-surface w-32 bg-primary text-white">Total</th>
                        </tr>
                    </thead>
                    <tbody class="font-label-bold text-label-bold">
                        @foreach($standings as $team)
                        <tr class="rank-row transition-all group">
                            <td class="p-md border-2 border-on-surface text-headline-md font-headline-md">{{ str_pad($team->rank, 2, '0', STR_PAD_LEFT) }}</td>
                            <td class="p-md border-2 border-on-surface text-lg uppercase">{{ $team->name }}</td>
                            <td class="p-md border-2 border-on-surface text-center medal-count text-medal-gold">{{ $team->gold }}</td>
                            <td class="p-md border-2 border-on-surface text-center medal-count text-secondary">{{ $team->silver }}</td>
                            <td class="p-md border-2 border-on-surface text-center medal-count text-medal-bronze">{{ $team->bronze }}</td>
                            <td class="p-md border-2 border-on-surface text-center text-lg font-headline-md">{{ $team->total }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endif
    </main>

    <!-- Footer -->
    <footer class="bg-on-surface dark:bg-surface-container-highest border-t-4 border-primary">
        <div class="flex flex-col md:flex-row justify-between items-center w-full px-gutter py-lg max-w-container-max mx-auto gap-md">
            <div class="font-headline-md text-headline-md text-primary-fixed uppercase">COMBAT PRO</div>
            <div class="flex flex-wrap justify-center gap-md">
                <a class="text-surface-variant hover:text-white font-label-bold text-label-bold transition-all hover:underline decoration-primary decoration-2" href="#">Privacy Policy</a>
                <a class="text-surface-variant hover:text-white font-label-bold text-label-bold transition-all hover:underline decoration-primary decoration-2" href="#">Terms of Service</a>
                <a class="text-surface-variant hover:text-white font-label-bold text-label-bold transition-all hover:underline decoration-primary decoration-2" href="#">Contact</a>
            </div>
            <div class="text-surface font-label-bold text-label-bold">
                © 2026 Combat Pro. Precision & Power.
            </div>
        </div>
    </footer>

    <script>
        // Micro-interactions for table rows
        document.querySelectorAll('.rank-row').forEach(row => {
            row.addEventListener('mouseenter', () => {
                row.classList.add('scale-[1.01]');
            });
            row.addEventListener('mouseleave', () => {
                row.classList.remove('scale-[1.01]');
            });
        });
    </script>
</body>
</html>
