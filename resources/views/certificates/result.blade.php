<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Sertifikat — {{ $participant->name }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/media/logos/logo3.png') }}" />
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
                        "surface-dim": "#dbdad9",
                        "surface": "#faf8f8",
                        "inverse-surface": "#303030",
                        "inverse-on-surface": "#faf8f8",
                        "primary": "#b9001c",
                        "medal-gold": "#d4af37",
                        "medal-silver": "#a8a9ad",
                        "medal-bronze": "#96502c",
                    },
                    "fontFamily": {
                        "anton": ["Anton", "sans-serif"],
                        "grotesk": ["Hanken Grotesk", "sans-serif"],
                    },
                },
            },
        };
    </script>
    <style type="text/tailwindcss">
        .font-headline-lg { font-family: Anton, sans-serif; }
        .font-headline-md { font-family: Anton, sans-serif; }
        .font-body-md { font-family: Hanken Grotesk, sans-serif; }
        .font-body-lg { font-family: Hanken Grotesk, sans-serif; }
        .hard-shadow { box-shadow: 6px 6px 0 0 #1b1c1c; }
        a:hover { color: #b9001c; }
        .btn-primary {
            background-color: #b9001c;
            color: #ffffff;
        }
        .btn-primary:hover { background-color: #930014; color: #ffffff; }
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
        <section class="mb-xl">
            <a href="{{ route('certificates.public.index') }}" class="inline-flex items-center gap-xs font-bold uppercase hover:text-primary transition-all mb-4">
                <span class="material-symbols-outlined text-sm">arrow_back</span> Cari NIK Lain
            </a>
            <h1 class="font-headline-lg text-headline-lg uppercase mb-xs leading-none">
                SERTIFIKAT <span class="text-primary">{{ strtoupper($participant->name) }}</span>
            </h1>
            @if ($participant->contingent)
                <p class="font-body-lg text-body-lg text-secondary">Kontingen: {{ $participant->contingent->name }}</p>
            @endif
        </section>

        @if ($certificates->isEmpty())
            <div class="bg-white border-2 border-on-surface p-xl text-center my-xl hard-shadow">
                <span class="material-symbols-outlined text-primary text-6xl mb-4">workspace_premium</span>
                <h3 class="font-headline-lg uppercase mb-2">Belum Ada Sertifikat</h3>
                <p class="font-body-lg text-secondary">Pendaftaran belum terverifikasi atau pembayaran belum lunas. Hubungi panitia bila merasa ini keliru.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
                @foreach ($certificates as $cert)
                    <div class="bg-white border-2 border-on-surface p-lg hard-shadow flex flex-col gap-sm">
                        <div class="flex items-start justify-between gap-sm">
                            <div>
                                <p class="font-bold uppercase text-primary text-sm">{{ $cert['event']->name }}</p>
                                <p class="font-body-lg">{{ $cert['category'] }}</p>
                            </div>
                            @if (in_array($cert['scope']->value, ['champion_gold', 'champion_silver', 'champion_bronze', 'champion_other']))
                                <span class="inline-flex items-center gap-xs border-2 border-on-surface px-sm py-xs font-bold uppercase text-sm whitespace-nowrap">
                                    <span class="material-symbols-outlined text-lg
                                        {{ $cert['scope']->value === 'champion_gold' ? 'text-medal-gold' : ($cert['scope']->value === 'champion_silver' ? 'text-medal-silver' : 'text-medal-bronze') }}">
                                        {{ $cert['scope']->value === 'champion_other' ? 'military_tech' : 'military_tech' }}
                                    </span>
                                    {{ $cert['status'] }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-xs border-2 border-on-surface px-sm py-xs font-bold uppercase text-sm whitespace-nowrap">
                                    <span class="material-symbols-outlined text-lg">confirmation_number</span>
                                    {{ $cert['status'] }}
                                </span>
                            @endif
                        </div>
                        <div class="mt-auto pt-sm border-t-2 border-surface-variant">
                            <a href="{{ route('certificates.public.pdf', $cert['registration']) }}"
                               class="btn-primary font-headline-md uppercase px-lg py-sm tracking-wide transition-all active:scale-95 inline-flex items-center gap-xs">
                                <span class="material-symbols-outlined text-lg">download</span>
                                Unduh PDF
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </main>
</body>
</html>
