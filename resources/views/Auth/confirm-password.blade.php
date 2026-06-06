<x-guest-layout title="Confirmation">
    <div class="bg-white dark:bg-[#0F172A] border border-gray-100 dark:border-white/10 rounded-2xl shadow-xl px-7 py-9 sm:px-9">
        <x-auth-brand subtitle="Zone sécurisée" />

        <p class="mb-5 text-sm text-gray-500 dark:text-gray-400 text-center">
            Veuillez confirmer votre mot de passe avant de continuer.
        </p>

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
            @csrf

            <div>
                <label for="password" class="form-label">Mot de passe</label>
                <input id="password" type="password" name="password"
                       placeholder="••••••••" required autocomplete="current-password" class="form-input">
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <button type="submit" class="btn-primary w-full">Confirmer</button>
        </form>
    </div>
</x-guest-layout>
