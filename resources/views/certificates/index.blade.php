<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Cek Sertifikat</title>
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
            <h1 class="font-headline-lg text-headline-lg md:text-display-lg uppercase mb-xs leading-none">
                CEK <span class="text-primary">SERTIFIKAT</span>
            </h1>
            <p class="font-body-lg text-body-lg text-secondary">
                Masukkan NIK (16 digit) untuk melihat dan mengunduh sertifikat Anda.
            </p>
        </section>

        <section class="max-w-xl">
            <form method="POST" action="{{ route('certificates.public.lookup') }}" class="bg-white border-2 border-on-surface p-lg hard-shadow flex flex-col gap-md">
                @csrf
                <div>
                    <label for="nik" class="block font-bold uppercase text-sm mb-xs">NIK Peserta</label>
                    <input id="nik" name="nik" type="text" inputmode="numeric" maxlength="16" pattern="[0-9]{16}" required
                           value="{{ old('nik') }}"
                           placeholder="16 digit angka"
                           class="w-full border-2 border-on-surface px-md py-sm font-body-lg tracking-wider focus:outline-none focus:ring-2 focus:ring-primary" />
                    @error('nik')
                        <p class="text-primary font-bold mt-xs">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="btn-primary font-headline-md uppercase px-lg py-sm tracking-wide transition-all active:scale-95">
                    <span class="material-symbols-outlined align-middle mr-xs">search</span>
                    Cari Sertifikat
                </button>
            </form>
            <p class="font-body-md text-secondary mt-sm text-sm">
                Peserta tanpa NIK? Hubungi panitia untuk pencetakan sertifikat.
            </p>
        </section>
    </main>
</body>
</html>
