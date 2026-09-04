<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-900 bg-white dark:bg-gray-900 selection:bg-siakad-primary selection:text-white">
    <div class="min-h-screen flex">
        
        <!-- Left Side - Form -->
        <div class="w-full lg:w-[480px] xl:w-[560px] flex flex-col justify-center px-8 lg:px-16 relative z-10 bg-white dark:bg-gray-900">
            <!-- Mobile Logo -->
            <div class="lg:hidden absolute top-8 left-8">
                <a href="/" class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded-xl bg-siakad-primary flex items-center justify-center text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                    <span class="font-bold text-xl text-siakad-dark dark:text-white">{{ config('app.name') }}</span>
                </a>
            </div>

            <div class="w-full max-w-[400px] mx-auto">
                {{ $slot }}
            </div>

            <!-- Footer -->
            <div class="absolute bottom-8 left-0 right-0 text-center text-xs text-gray-400 dark:text-gray-500">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </div>
        </div>

        <!-- Right Side - Visual -->
        <div class="hidden lg:flex flex-1 relative bg-siakad-dark overflow-hidden items-center justify-center">
            <!-- Background Gradients -->
            <div class="absolute inset-0 bg-gradient-to-br from-siakad-dark via-[#163247] to-gray-900"></div>
            <div class="absolute top-0 right-0 w-[800px] h-[800px] bg-siakad-primary/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-indigo-500/10 rounded-full blur-3xl translate-y-1/2 -translate-x-1/3"></div>
            
            <!-- Pattern Overlay -->
            <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>

            <!-- Glassmorphism Card Content -->
            <div class="relative z-10 max-w-lg text-center p-12">
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-8 shadow-2xl relative overflow-hidden group hover:bg-white/10 transition-colors duration-500">
                    <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                    
                    <div class="w-20 h-20 bg-gradient-to-br from-indigo-400 to-cyan-300 rounded-2xl mx-auto mb-8 flex items-center justify-center shadow-lg shadow-indigo-500/30 transform group-hover:scale-110 transition-transform duration-500">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>

                    <h2 class="text-3xl font-bold text-white mb-4 tracking-tight">Kulim SIAKAD-AI</h2>
                    <p class="text-indigo-100/80 text-lg leading-relaxed">
                        Platform manajemen akademik modern berbasis AI untuk efisiensi dan transparansi pendidikan tinggi.
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
