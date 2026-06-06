<x-guest-layout title="Mot de passe oublié">
    <div class="bg-white dark:bg-[#0F172A] border border-gray-100 dark:border-white/10 rounded-2xl shadow-xl px-7 py-9 sm:px-9">
        <x-auth-brand subtitle="Réinitialiser le mot de passe" />

        <p class="mb-5 text-sm text-gray-500 dark:text-gray-400 text-center">
            Indiquez votre email : nous vous enverrons un lien pour choisir un nouveau mot de passe.
        </p>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                       placeholder="nom@premidis.com" required autofocus class="form-input">
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <button type="submit" class="btn-primary w-full">Envoyer le lien de réinitialisation</button>

            <p class="text-center">
                <a href="{{ route('login') }}" class="text-sm font-medium text-[#6366F1] hover:underline">Retour à la connexion</a>
            </p>
        </form>
    </div>
</x-guest-layout>
