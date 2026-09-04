<x-guest-layout>
    <div class="mb-10">
        <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Welcome back</h2>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Please enter your details to sign in.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

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
                       value="{{ old('email') }}" 
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
                {{ __('Sign in') }}
            </button>
        </div>

        <!-- Divider -->
        <!-- Future: Social Login -->
        
        <p class="text-center text-xs text-gray-500 dark:text-gray-400 mt-8">
            Having trouble? <a href="#" class="font-medium text-siakad-primary hover:text-siakad-dark dark:text-cyan-400 dark:hover:text-cyan-300 transition-colors">Contact Support</a>
        </p>
    </form>
</x-guest-layout>
