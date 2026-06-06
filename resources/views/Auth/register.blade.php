<x-guest-layout title="Inscription">
    <div class="bg-white dark:bg-[#0F172A] border border-gray-100 dark:border-white/10 rounded-2xl shadow-xl px-7 py-9 sm:px-9">
        <x-auth-brand subtitle="Créer un compte" />

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="form-label">Nom complet</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}"
                       placeholder="Votre nom" required autofocus autocomplete="name" class="form-input">
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                       placeholder="nom@premidis.com" required autocomplete="username" class="form-input">
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <label for="password" class="form-label">Mot de passe</label>
                <input id="password" type="password" name="password"
                       placeholder="••••••••" required autocomplete="new-password" class="form-input">
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                <input id="password_confirmation" type="password" name="password_confirmation"
                       placeholder="••••••••" required autocomplete="new-password" class="form-input">
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <button type="submit" class="btn-primary w-full">Créer le compte</button>

            <p class="text-center">
                <a href="{{ route('login') }}" class="text-sm font-medium text-[#6366F1] hover:underline">Déjà inscrit ? Se connecter</a>
            </p>
        </form>
    </div>
</x-guest-layout>
