<x-guest-layout>
    <div class="space-y-6">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Connexion</p>
            <h2 class="text-3xl font-bold tracking-tight text-slate-900">Accédez à votre espace</h2>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="email" :value="__('Email')" class="mb-2 block text-sm font-medium text-slate-700" />
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">✉</span>
                    <x-text-input id="email" class="block w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-slate-900 shadow-sm transition focus:border-[var(--brand-primary)] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[var(--brand-primary)]/10" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <div class="mb-2 flex items-center justify-between">
                    <x-input-label for="password" :value="__('Password')" class="block text-sm font-medium text-slate-700" />
                    @if (Route::has('password.request'))
                        <a class="text-sm font-medium text-[var(--brand-primary)] transition hover:text-[var(--brand-accent)]" href="{{ route('password.request') }}">
                            Mot de passe oublié ?
                        </a>
                    @endif
                </div>

                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">🔒</span>
                    <x-text-input id="password" class="block w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-slate-900 shadow-sm transition focus:border-[var(--brand-primary)] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[var(--brand-primary)]/10" type="password" name="password" required autocomplete="current-password" />
                </div>

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between pt-1">
                <label for="remember_me" class="inline-flex items-center gap-3 text-sm text-slate-600">
                    <input id="remember_me" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-[var(--brand-primary)] shadow-sm focus:ring-[var(--brand-primary)]/20" name="remember">
                    <span>Se souvenir de moi</span>
                </label>
            </div>

            <x-primary-button class="mt-2 w-full justify-center rounded-2xl bg-[var(--brand-primary)] px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-[var(--brand-primary)]/20 transition hover:bg-[var(--brand-accent)] focus:ring-[var(--brand-primary)]/30">
                Se connecter
            </x-primary-button>
        </form>
    </div>
</x-guest-layout>
