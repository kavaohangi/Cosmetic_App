<x-guest-layout title="Réinitialisation">
    <div class="bg-white dark:bg-[#0F172A] border border-gray-100 dark:border-white/10 rounded-2xl shadow-xl px-7 py-9 sm:px-9">
        <x-auth-brand subtitle="Nouveau mot de passe" />

        <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}"
                       required autofocus autocomplete="username" class="form-input">
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

            <button type="submit" class="btn-primary w-full">Réinitialiser le mot de passe</button>
        </form>
    </div>
</x-guest-layout>
