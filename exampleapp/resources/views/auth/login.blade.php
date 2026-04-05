@extends('layouts.auth')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-slate-200/80 p-4 md:p-6">
    <div class="w-full max-w-5xl min-h-[600px] flex flex-col md:flex-row rounded-[28px] overflow-hidden shadow-2xl bg-white">
        {{-- Left panel: Branding & marketing (vibrant blue) --}}
        <div class="md:w-[45%] bg-blue-500 flex flex-col justify-between p-8 md:p-10 text-white">
            <div>
                {{-- Logo + brand --}}
                <div class="flex items-center gap-3">
                    <div class="flex -space-x-1.5">
                        <span class="w-9 h-9 rounded-lg bg-white/20 border-2 border-white/40 inline-block"></span>
                        <span class="w-9 h-9 rounded-lg bg-white/30 border-2 border-white/40 inline-block"></span>
                    </div>
                    <div>
                        <span class="text-xl font-bold tracking-tight">ACADCLEAR</span>
                        <span class="block text-xs font-medium text-blue-100 tracking-widest uppercase mt-0.5">Academic Clearance System</span>
                    </div>
                </div>
            </div>

            {{-- Illustration area (education / learning theme) --}}
            <div class="flex-1 flex items-center justify-center my-8 md:my-10">
                <div class="relative w-full max-w-[220px]">
                    <div class="aspect-square rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center shadow-inner">
                        <svg class="w-24 h-24 text-white/90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <div class="absolute -top-2 -right-2 w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-lg font-bold">@</div>
                    <div class="absolute -bottom-1 -left-1 w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/></svg>
                    </div>
                </div>
            </div>

            <div>
                <p class="text-lg font-medium leading-snug text-white/95 max-w-[280px]">
                    Discover the new way to manage and organize your academic clearances.
                </p>
                <a href="#" class="inline-block mt-4 text-sm font-medium text-blue-100 hover:text-white transition">See more</a>
            </div>
        </div>

        {{-- Right panel: Form (dark navy) --}}
        <div class="md:w-[55%] bg-[#0f172a] flex flex-col p-8 md:p-10">
            {{-- Nav --}}
            <nav class="flex items-center justify-end gap-6 text-sm font-medium mb-8">
                <a href="{{ route('landing.index') }}" class="text-white/80 hover:text-white transition">Plans</a>
                <a href="#" class="text-white/80 hover:text-white transition">About Us</a>
                <a href="#" class="text-white/80 hover:text-white transition">Prices</a>
                <a href="#" class="text-white/80 hover:text-white transition">Contact</a>
                <a href="{{ route('register') }}" class="text-blue-300 hover:text-blue-200 transition">Register</a>
            </nav>

            <div class="flex-1 flex flex-col justify-center max-w-sm mx-auto w-full">
                <h2 class="text-2xl font-bold text-white text-center mb-6">Log in to your account</h2>

                <x-auth-session-status class="mb-4 rounded-lg bg-emerald-500/20 text-emerald-200 px-4 py-3 text-sm" :status="session('status')" />
                <x-auth-validation-errors class="mb-4 rounded-lg bg-red-500/20 text-red-200 px-4 py-3 text-sm" :errors="$errors" />

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf
                    @php
                        $recaptchaSiteKey = config('services.recaptcha.site_key');
                    @endphp
                    <div>
                        <x-text-input
                            id="email"
                            class="block w-full rounded-xl border-0 bg-white text-slate-800 placeholder:text-slate-400 focus:ring-2 focus:ring-blue-400 py-3 px-4 transition"
                            type="email"
                            name="email"
                            :value="old('email')"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="Email"
                        />
                        <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-red-300 text-sm" />
                    </div>

                    <div>
                        <x-text-input
                            id="password"
                            class="block w-full rounded-xl border-0 bg-white text-slate-800 placeholder:text-slate-400 focus:ring-2 focus:ring-blue-400 py-3 px-4 transition"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Password"
                        />
                        <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-red-300 text-sm" />
                    </div>

                    <div>
                        @if (!empty($recaptchaSiteKey))
                            <div class="g-recaptcha" data-sitekey="{{ $recaptchaSiteKey }}"></div>
                            <x-input-error :messages="$errors->get('g-recaptcha-response')" class="mt-1.5 text-red-300 text-sm" />
                        @else
                            <label class="inline-flex items-center gap-2 cursor-pointer group">
                                <input
                                    id="not_robot"
                                    type="checkbox"
                                    name="not_robot"
                                    value="1"
                                    {{ old('not_robot') ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-slate-500 bg-slate-800/50 text-blue-500 focus:ring-blue-500 focus:ring-offset-[#0f172a]"
                                >
                                <span class="text-sm text-white/80 group-hover:text-white">I'm not a robot</span>
                            </label>
                            <x-input-error :messages="$errors->get('not_robot')" class="mt-1.5 text-red-300 text-sm" />
                        @endif
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <label class="inline-flex items-center gap-2 cursor-pointer group">
                            <input
                                id="remember_me"
                                type="checkbox"
                                name="remember"
                                class="w-4 h-4 rounded border-slate-500 bg-slate-800/50 text-blue-500 focus:ring-blue-500 focus:ring-offset-[#0f172a]"
                            >
                            <span class="text-sm text-white/80 group-hover:text-white">{{ __('Remember me') }}</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a class="text-sm font-medium text-blue-300 hover:text-blue-200 transition" href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif
                    </div>

                    <button
                        type="submit"
                        class="w-full py-3.5 px-4 rounded-xl font-semibold text-white bg-blue-500 hover:bg-blue-600 focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 focus:ring-offset-[#0f172a] transition"
                    >
                        {{ __('Log in') }}
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-white/80">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="font-medium text-blue-300 hover:text-blue-200 transition">Register</a>
                </p>

                <div class="mt-4 rounded-lg bg-white/5 border border-white/10 px-4 py-2.5 text-center">
                    <p class="text-xs text-white/60">Test: admin@test.com / password</p>
                </div>
            </div>
        </div>
    </div>
</div>
@if (!empty(config('services.recaptcha.site_key')))
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endif
@endsection
