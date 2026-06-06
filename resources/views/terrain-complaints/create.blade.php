@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow p-6 md:p-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                Signaler une plainte ou une proposition
            </h1>
            <p class="text-gray-600 mb-6">
                Décrivez votre plainte ou proposez une amélioration pour un produit spécifique.
            </p>

            <!-- Errors -->
            @if ($errors->any())
                <div class="mb-6 p-4 text-red-700 bg-red-50 border border-red-200 rounded-lg">
                    <h3 class="font-semibold mb-2">Erreurs :</h3>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('terrain-complaints.store') }}" class="space-y-6">
                @csrf

                <!-- Type -->
                <div>
                    <label for="type" class="block text-sm font-semibold text-gray-700 mb-2">
                        Type <span class="text-red-500">*</span>
                    </label>
                    <select id="type" name="type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Sélectionnez --</option>
                        <option value="complaint" @selected(old('type') === 'complaint')>Plainte</option>
                        <option value="proposition" @selected(old('type') === 'proposition')>Proposition</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Product -->
                <div>
                    <label for="product_id" class="block text-sm font-semibold text-gray-700 mb-2">
                        Produit concerné <span class="text-red-500">*</span>
                    </label>
                    <select id="product_id" name="product_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Sélectionnez un produit --</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                        Description <span class="text-red-500">*</span>
                    </label>
                    <textarea id="description" name="description" required rows="6"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Décrivez votre plainte ou proposition de manière détaillée...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date -->
                <div>
                    <label for="date" class="block text-sm font-semibold text-gray-700 mb-2">
                        Date
                    </label>
                    <input type="date" id="date" name="date" value="{{ old('date', today()->toDateString()) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="flex flex-col gap-3 md:flex-row md:justify-between pt-6 border-t border-gray-200">
                    <a href="{{ route('terrain-complaints.index') }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition text-center">
                        Annuler
                    </a>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition">
                        Soumettre
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
