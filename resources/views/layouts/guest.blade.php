<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'STIT Mambaul Hikmah') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.PNG') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-900 bg-white dark:bg-gray-900 selection:bg-siakad-primary selection:text-white">
    <div class="min-h-screen flex">
        
        <!-- Left Side - Form -->
        <div class="w-full lg:w-[480px] xl:w-[560px] flex flex-col justify-center px-8 lg:px-16 relative z-10 bg-white dark:bg-gray-900 py-12">
            <!-- Brand Header -->
            <div class="mb-8">
                <a href="/" class="inline-flex items-center gap-3">
                    <img src="{{ asset('logo.PNG') }}" alt="STIT Mambaul Hikmah Logo" class="h-12 w-auto object-contain">
                    <div>
                        <span class="block font-bold text-lg text-siakad-dark dark:text-white leading-tight">STIT Mambaul Hikmah</span>
                        <span class="block text-xs font-medium text-siakad-secondary dark:text-gray-400">Sistem Informasi Akademik</span>
                    </div>
                </a>
            </div>

            <div class="w-full max-w-[400px] mx-auto">
                {{ $slot }}
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center text-xs text-gray-400 dark:text-gray-500">
                &copy; {{ date('Y') }} STIT Mambaul Hikmah. All rights reserved.
            </div>
        </div>

        <!-- Right Side - Visual -->
        <div class="hidden lg:flex flex-1 relative bg-siakad-dark overflow-hidden items-center justify-center">
            <!-- Background Gradients -->
            <div class="absolute inset-0 bg-gradient-to-br from-siakad-dark via-[#163247] to-gray-900"></div>
            <div class="absolute top-0 right-0 w-[800px] h-[800px] bg-emerald-600/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-siakad-primary/20 rounded-full blur-3xl translate-y-1/2 -translate-x-1/3"></div>
            
            <!-- Pattern Overlay -->
            <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>

            <!-- Glassmorphism Card Content -->
            <div class="relative z-10 max-w-lg text-center p-12">
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-8 shadow-2xl relative overflow-hidden group hover:bg-white/10 transition-colors duration-500">
                    <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                    
                    <div class="w-24 h-24 p-2 bg-white/10 backdrop-blur-md rounded-2xl mx-auto mb-6 flex items-center justify-center shadow-lg shadow-black/20 transform group-hover:scale-105 transition-transform duration-500">
                        <img src="{{ asset('logo.PNG') }}" alt="STIT Mambaul Hikmah Logo" class="w-full h-full object-contain filter drop-shadow">
                    </div>

                    <h2 class="text-2xl lg:text-3xl font-bold text-white mb-2 tracking-tight">STIT Mambaul Hikmah</h2>
                    <p class="text-amber-400 text-xs font-semibold uppercase tracking-widest mb-3">SIAKAD Terpadu</p>
                    <p class="text-indigo-100/80 text-base leading-relaxed">
                        Sistem Informasi Akademik terintegrasi untuk mendukung efisiensi, akurasi, dan transparansi pendidikan tinggi.
                    </p>

                    <div class="mt-8 flex justify-center gap-2">
                        <div class="w-12 h-1 bg-white/30 rounded-full"></div>
                        <div class="w-2 h-1 bg-white/10 rounded-full"></div>
                        <div class="w-2 h-1 bg-white/10 rounded-full"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
