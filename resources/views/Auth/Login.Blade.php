<x-guest-layout title="Connexion">
    <div class="bg-white dark:bg-[#0F172A] border border-gray-100 dark:border-white/10 rounded-2xl shadow-xl px-7 py-9 sm:px-9">
        <x-auth-brand />

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1.5">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                       placeholder="nom@premidis.com" required autofocus autocomplete="username"
                       class="form-input py-3">
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1.5">Mot de passe</label>
                <input id="password" type="password" name="password"
                       placeholder="••••••••" required autocomplete="current-password"
                       class="form-input py-3">
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between">
                <label for="remember_me" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 select-none">
                    <input id="remember_me" type="checkbox" name="remember"
                           class="rounded border-gray-300 text-[#6366F1] focus:ring-[#6366F1] dark:bg-white/5 dark:border-white/10">
                    Se souvenir de moi
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-[#6366F1] hover:underline">Mot de passe oublié ?</a>
                @endif
            </div>

            <button type="submit" class="btn-primary w-full py-3 text-base">Se connecter</button>
        </form>
    </div>
</x-guest-layout>
