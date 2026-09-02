<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Klasemen | {{ $event->name }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/media/logos/logo3.png') }}" />
    <!-- Tailwind + app assets, compiled by Vite (theme tokens live in tailwind.config.js) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Google Fonts: Anton (display) + Hanken Grotesk (body) + Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Hanken+Grotesk:wght@400;500;700&display=swap"
          rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
          rel="stylesheet"/>
    <script>document.documentElement.classList.add('js')</script>

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
        table { border-collapse: collapse; }
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
        /* Nav filter tipe: hanya tampil bila JS aktif (tanpa JS semua section terlihat) */
        #typeNav { display: none; }
        .js #typeNav { display: flex; }
        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; }
        }
    </style>
</head>
<body class="bg-surface font-body-md text-on-surface selection:bg-primary selection:text-white">
    <!-- TopNavBar — pola sama dengan welcome.blade.php (tanpa varian dark:) -->
    <header class="bg-surface border-b-2 border-on-surface sticky top-0 z-50">
        <div class="flex justify-between items-center w-full px-gutter py-sm max-w-container-max mx-auto">
            <div class="font-display-lg-mobile text-display-lg-mobile text-primary italic uppercase tracking-tighter">
                <a href="{{ route('landing') }}">COMBAT <span class="text-on-surface">PRO</span></a>
            </div>
            <nav class="hidden md:flex gap-lg items-center">
                <a class="font-label-bold text-label-bold text-primary border-b-4 border-accent transition-all duration-100 ease-in-out active:translate-y-0.5 px-xs" href="{{ route('landing') }}#events">TOURNAMENTS</a>
                <a class="font-label-bold text-label-bold text-on-surface hover:border-b-4 hover:border-accent transition-all duration-100 ease-in-out active:translate-y-0.5 px-xs" href="{{ route('certificates.public.index') }}">CEK SERTIFIKAT</a>
                <a class="font-label-bold text-label-bold text-on-surface hover:border-b-4 hover:border-accent transition-all duration-100 ease-in-out active:translate-y-0.5 px-xs" href="{{ route('landing') }}#about">ABOUT</a>
                <a class="font-label-bold text-label-bold text-on-surface hover:border-b-4 hover:border-accent transition-all duration-100 ease-in-out active:translate-y-0.5 px-xs" href="{{ route('landing') }}#contact">CONTACT</a>
            </nav>
            <div class="flex items-center gap-sm">
                @guest
                <a href="{{ route('login') }}" class="bg-primary text-on-primary font-headline-md px-md py-xs border-2 border-on-surface hard-shadow hover:bg-on-surface transition-all">LOGIN</a>
                @else
                <a href="{{ route('dashboard') }}" class="bg-primary text-on-primary font-headline-md px-md py-xs border-2 border-on-surface hard-shadow hover:bg-on-surface transition-all">DASHBOARD</a>
                @endguest
            </div>
        </div>
    </header>

    <main class="max-w-container-max mx-auto px-gutter py-lg">
        <!-- Header Section -->
        <section class="mb-md">
            <a href="{{ route('events.show', $event->id) }}" class="inline-flex items-center gap-xs font-label-bold uppercase hover:text-primary transition-all mb-base">
                <span class="material-symbols-outlined text-sm">arrow_back</span> Kembali ke Detail Event
            </a>
            <h1 class="font-headline-lg text-headline-md md:text-headline-lg uppercase leading-none mb-xs">
                KLASEMEN <span class="text-primary">{{ $event->name }}</span>
            </h1>
            <p class="font-body-md text-body-md text-secondary">
                Peringkat kontingen berdasarkan perolehan medali.
            </p>
        </section>

        <!-- Filter tipe: hanya muncul bila ada lebih dari 1 tipe klasemen -->
        @if (count($standingsPerTipe) > 1)
        <nav id="typeNav" class="sticky top-[70px] z-40 bg-surface/95 backdrop-blur-sm border-b-2 border-on-surface -mx-gutter px-gutter py-sm gap-sm flex-wrap mb-lg" aria-label="Filter tipe klasemen">
            @foreach ($standingsPerTipe as $typeName => $standings)
                <button type="button" data-target="p{{ $loop->index }}"
                    class="filter-chip font-label-bold text-label-bold uppercase border-2 border-on-surface bg-white px-4 py-1 hover:bg-surface-variant transition-colors cursor-pointer">
                    {{ strtoupper($typeName) }}
                </button>
            @endforeach
            <button type="button" data-target="all"
                class="filter-chip font-label-bold text-label-bold uppercase border-2 border-dashed border-outline text-secondary px-4 py-1 hover:bg-surface-variant transition-colors cursor-pointer">
                Semua
            </button>
        </nav>
        @endif

        @foreach ($standingsPerTipe as $typeName => $standings)
            @php $panelId = 'p'.$loop->index; @endphp

            <section data-panel="{{ $panelId }}" class="mb-lg" aria-label="Klasemen {{ $typeName }}">
                <!-- Judul section: chip tipe + rule + jumlah kontingen -->
                <div class="flex items-center gap-md mb-md">
                    <h2 class="bg-on-surface text-surface font-headline-md text-body-md uppercase tracking-[0.15em] px-3 py-1 leading-none">
                        KLASEMEN {{ strtoupper($typeName) }}
                    </h2>
                    <div class="h-0.5 flex-1 bg-outline-variant" aria-hidden="true"></div>
                    <span class="font-label-sm text-label-sm text-secondary uppercase tracking-wide">{{ count($standings) }} Kontingen</span>
                </div>

                @if (empty($standings))
                    <div class="border-2 border-dashed border-outline-variant p-md text-center text-secondary font-body-md flex items-center justify-center gap-sm">
                        <span class="material-symbols-outlined text-primary" aria-hidden="true">emoji_events</span>
                        Belum ada hasil — pertandingan belum dimulai atau hasil belum dimasukkan panitia.
                    </div>
                @else
                    @php
                        $top3 = array_slice($standings, 0, 3);
                        $podium = [
                            ['rank' => 2, 'item' => $top3[1] ?? null, 'disc' => 'bg-medal-silver text-on-surface', 'emph' => false],
                            ['rank' => 1, 'item' => $top3[0] ?? null, 'disc' => 'bg-medal-gold text-on-surface', 'emph' => true],
                            ['rank' => 3, 'item' => $top3[2] ?? null, 'disc' => 'bg-medal-bronze text-white', 'emph' => false],
                        ];
                    @endphp

                    <!-- Podium ringkas: 1 baris, juara 1 di tengah ditonjolkan -->
                    @if ($top3 !== [])
                    <div class="grid grid-cols-3 gap-sm mb-md">
                        @foreach ($podium as $slot)
                            @if ($slot['item'])
                                @php $t = $slot['item']; @endphp
                                <div class="bg-white border-2 {{ $slot['emph'] ? 'border-primary hard-shadow-red' : 'border-on-surface hard-shadow' }} px-sm py-md text-center">
                                    <div class="flex items-center justify-center gap-xs mb-xs">
                                        <span class="inline-flex items-center justify-center {{ $slot['emph'] ? 'w-8 h-8 text-body-md' : 'w-7 h-7 text-label-bold' }} font-headline-md border-2 border-on-surface {{ $slot['disc'] }} {{ $slot['emph'] ? '' : 'text-label-bold' }}">{{ $slot['rank'] }}</span>
                                    </div>
                                    <div class="font-headline-md text-label-bold uppercase truncate mb-xs">{{ $t->name }}</div>
                                    <div class="flex justify-center gap-md font-headline-md text-body-md">
                                        <span class="inline-flex items-center gap-xs"><span class="w-2 h-2 rounded-full bg-medal-gold" aria-hidden="true"></span>{{ $t->gold }}</span>
                                        <span class="inline-flex items-center gap-xs"><span class="w-2 h-2 rounded-full bg-medal-silver" aria-hidden="true"></span>{{ $t->silver }}</span>
                                        <span class="inline-flex items-center gap-xs"><span class="w-2 h-2 rounded-full bg-medal-bronze" aria-hidden="true"></span>{{ $t->bronze }}</span>
                                    </div>
                                </div>
                            @else
                                <div class="border-2 border-dashed border-outline-variant" aria-hidden="true"></div>
                            @endif
                        @endforeach
                    </div>
                    @endif

                    <!-- Tabel peringkat -->
                    <section class="overflow-x-auto">
                        <table class="w-full border-collapse border-2 border-on-surface text-body-md">
                            <thead>
                                <tr class="bg-on-surface text-surface uppercase font-label-bold text-label-sm tracking-wider">
                                    <th class="px-md py-sm text-left w-16">Rank</th>
                                    <th class="px-md py-sm text-left">Kontingen</th>
                                    <th class="px-md py-sm text-center w-20">Emas</th>
                                    <th class="px-md py-sm text-center w-20">Perak</th>
                                    <th class="px-md py-sm text-center w-20">Perunggu</th>
                                    <th class="px-md py-sm text-center w-20 bg-primary text-white">Total</th>
                                </tr>
                            </thead>
                            <tbody class="font-label-bold text-label-bold">
                                @foreach($standings as $team)
                                <tr class="rank-row border-b {{ $loop->last ? 'border-b-0' : 'border-outline-variant' }} transition-colors">
                                    <td class="px-md py-sm">
                                        @if ($team->rank <= 3)
                                            <span class="inline-flex items-center justify-center w-7 h-7 font-headline-md text-label-bold border-2 border-on-surface {{ $team->rank == 1 ? 'bg-medal-gold text-on-surface' : ($team->rank == 2 ? 'bg-medal-silver text-on-surface' : 'bg-medal-bronze text-white') }}">{{ $team->rank }}</span>
                                        @else
                                            <span class="font-headline-md text-secondary">{{ str_pad($team->rank, 2, '0', STR_PAD_LEFT) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-md py-sm uppercase">{{ $team->name }}</td>
                                    <td class="px-md py-sm text-center"><span class="medal-count text-medal-gold inline-flex items-center gap-xs"><span class="w-1.5 h-1.5 rounded-full bg-medal-gold" aria-hidden="true"></span>{{ $team->gold }}</span></td>
                                    <td class="px-md py-sm text-center"><span class="medal-count text-secondary inline-flex items-center gap-xs"><span class="w-1.5 h-1.5 rounded-full bg-medal-silver" aria-hidden="true"></span>{{ $team->silver }}</span></td>
                                    <td class="px-md py-sm text-center"><span class="medal-count text-medal-bronze inline-flex items-center gap-xs"><span class="w-1.5 h-1.5 rounded-full bg-medal-bronze" aria-hidden="true"></span>{{ $team->bronze }}</span></td>
                                    <td class="px-md py-sm text-center font-headline-md text-body-md">{{ $team->total }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </section>
                @endif
            </section>
        @endforeach
    </main>

    <!-- Footer -->
    <footer class="bg-on-surface border-t-4 border-primary">
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
        // Filter chip tipe klasemen — tanpa JS semua section tetap terlihat
        const nav = document.getElementById('typeNav');
        if (nav) {
            const chips = [...nav.querySelectorAll('.filter-chip')];
            const panels = [...document.querySelectorAll('[data-panel]')];
            const activate = (chip) => {
                chips.forEach(c => c.classList.toggle('active-filter', c === chip));
                const target = chip.dataset.target;
                panels.forEach(p => { p.hidden = target !== 'all' && p.dataset.panel !== target; });
            };
            chips.forEach(c => c.addEventListener('click', () => activate(c)));
            activate(chips[0]); // default: tipe pertama, halaman langsing pendek
        }
    </script>
</body>
</html>
