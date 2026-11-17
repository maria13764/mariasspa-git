<x-guest-layout>
    <div class="mb-8">
        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[#c27867]">Welcome back</p>
        <h2 class="mt-3 font-serif text-4xl text-[#173b3f]">Sign in to your spaces.</h2>
        <p class="mt-3 text-sm leading-6 text-slate-500">Access your appointments and care details with your Maria's Spa account.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="space-y-2">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block w-full border-slate-200 bg-white/70 py-3 focus:border-[#c27867] focus:ring-[#c27867]" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-5 space-y-2">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block w-full border-slate-200 bg-white/70 py-3 focus:border-[#c27867] focus:ring-[#c27867]"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="mt-5 flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-[#c27867] shadow-sm focus:ring-[#c27867]" name="remember">
                <span class="ms-2 text-sm text-slate-500">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="mt-8 flex items-center justify-between gap-4">
            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-[#c27867] underline-offset-4 hover:underline" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3 bg-[#173b3f] px-6 py-3 text-sm uppercase tracking-[0.12em] hover:bg-[#24595d] focus:bg-[#24595d] active:bg-[#102e31]">
                {{ __('Enter dashboard') }}
            </x-primary-button>
        </div>
    </form>

    <div class="mt-6">
        <a class="flex w-full items-center justify-center gap-3 rounded-md border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-[#c27867] focus:ring-offset-2" href="{{ route('google.redirect') }}">
            <span aria-hidden="true" class="flex h-5 w-5 items-center justify-center rounded-full border border-slate-200 font-serif text-sm font-bold text-[#4285f4]">G</span>
            <span>Continue with Google</span>
        </a>
        <p class="mt-2 text-center text-xs text-slate-500">New Google users will get an account automatically.</p>
    </div>

    @if (Route::has('register'))
        <div class="mt-4">
            <a class="flex w-full items-center justify-center rounded-md border border-[#173b3f] px-4 py-3 text-sm font-semibold uppercase tracking-[0.12em] text-[#173b3f] transition hover:bg-[#173b3f] hover:text-white focus:outline-none focus:ring-2 focus:ring-[#c27867] focus:ring-offset-2" href="{{ route('register') }}">
                Register
            </a>
        </div>
    @endif
</x-guest-layout>
