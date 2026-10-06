<x-guest-layout>
    <div class="mb-10">
        <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Selamat Datang</h2>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Silahkan masukkan data anda untuk login.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if(session('google_link_data'))
    <!-- Banner Penautan Akun Google -->
    <div class="mb-6 p-4 rounded-2xl border border-indigo-200 dark:border-indigo-800 bg-indigo-50/70 dark:bg-indigo-950/40">
        <div class="flex items-start gap-3">
            @if(!empty(session('google_link_data.avatar')))
                <img src="{{ session('google_link_data.avatar') }}" class="w-10 h-10 rounded-full border border-indigo-200 dark:border-indigo-700 flex-shrink-0" alt="Google Avatar">
            @else
                <div class="w-10 h-10 rounded-full bg-white dark:bg-gray-800 flex items-center justify-center text-indigo-600 shadow-sm flex-shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                    </svg>
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">
                        Penautan Akun
                    </span>
                    <form action="{{ route('auth.google.cancel') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 dark:text-red-400 font-medium">Batal</button>
                    </form>
                </div>
                <p class="text-sm font-bold text-gray-900 dark:text-white mt-1 truncate">{{ session('google_link_data.email') }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Masukkan email & password akun SIAKAD Anda di bawah untuk menautkan akun. Selanjutnya Anda bisa login langsung dengan Google.
                </p>
            </div>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div class="relative group">
            <x-input-label for="email" :value="__('Email address')" class="sr-only" />
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-siakad-primary transition-colors duration-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                    </svg>
                </div>
                <input id="email" 
                       class="block w-full pl-11 pr-4 py-3.5 bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-xl focus:ring-2 focus:ring-siakad-primary focus:border-siakad-primary transition-all duration-200 outline-none placeholder-gray-400 dark:placeholder-gray-500 font-medium sm:text-sm" 
                       type="email" 
                       name="email" 
                       value="{{ old('email', session('google_link_data.email')) }}" 
                       required 
                       autofocus 
                       autocomplete="username" 
                       placeholder="Enter your email" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="relative group">
            <x-input-label for="password" :value="__('Password')" class="sr-only" />
            
            <div class="relative" x-data="{ show: false }">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-siakad-primary transition-colors duration-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input id="password" 
                       class="block w-full pl-11 pr-12 py-3.5 bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-xl focus:ring-2 focus:ring-siakad-primary focus:border-siakad-primary transition-all duration-200 outline-none placeholder-gray-400 dark:placeholder-gray-500 font-medium sm:text-sm" 
                       :type="show ? 'text' : 'password'"
                       name="password"
                       required 
                       autocomplete="current-password"
                       placeholder="Enter your password" />
                
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                    <svg class="h-5 w-5" x-show="!show" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg class="h-5 w-5" x-show="show" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.05 10.05 0 011.577-2.387M8 8.05A2.992 2.992 0 007.828 10.828l3.125 3.125a2.991 2.991 0 003.354-.055m1.515-2.074a2.992 2.992 0 00-.776-3.875" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                    </svg>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center group cursor-pointer">
                <div class="relative flex items-start">
                    <div class="flex items-center h-5">
                        <input id="remember_me" 
                                type="checkbox" 
                                class="w-4 h-4 border border-gray-300 rounded text-siakad-primary focus:ring-siakad-primary/20 dark:bg-gray-800 dark:border-gray-600 dark:checked:bg-siakad-primary transition duration-150 ease-in-out" 
                                name="remember">
                    </div>
                    <div class="ml-2 text-sm">
                        <span class="text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors">{{ __('Remember for 30 days') }}</span>
                    </div>
                </div>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-siakad-primary hover:text-siakad-dark dark:text-cyan-400 dark:hover:text-cyan-300 transition-colors" href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-siakad-primary hover:bg-siakad-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-siakad-primary transition-all duration-200 transform hover:-translate-y-0.5 hover:shadow-lg">
                {{ session('google_link_data') ? __('Tautkan Akun & Masuk') : __('Sign in') }}
            </button>
        </div>

        @if(!session('google_link_data'))
        <!-- Divider -->
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
            </div>
            <div class="relative flex justify-center text-xs uppercase">
                <span class="bg-white dark:bg-gray-900 px-3 text-gray-500 dark:text-gray-400 font-medium">
                    Atau masuk dengan
                </span>
            </div>
        </div>

        <!-- Google Login Button -->
        <a href="{{ route('auth.google') }}" class="w-full flex items-center justify-center gap-3 py-3 px-4 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 transition-all duration-200 shadow-sm transform hover:-translate-y-0.5">
            <svg class="h-5 w-5" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
            </svg>
            <span>Masuk dengan Google</span>
        </a>
        @endif
        
        <p class="text-center text-xs text-gray-500 dark:text-gray-400 mt-8">
            Having trouble? <a href="#" class="font-medium text-siakad-primary hover:text-siakad-dark dark:text-cyan-400 dark:hover:text-cyan-300 transition-colors">Contact Support</a>
        </p>
    </form>
</x-guest-layout>
