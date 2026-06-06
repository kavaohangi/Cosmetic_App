@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Header -->
            <div class="p-6 md:p-8 border-b border-gray-200">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">
                            {{ $complaint->typeLabel() }} - {{ $complaint->product->name }}
                        </h1>
                        <p class="text-gray-600 mt-2">Créée le {{ $complaint->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                    <div class="space-y-2">
                        <div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @if ($complaint->type === 'complaint')
                                    bg-red-100 text-red-800
                                @else
                                    bg-blue-100 text-blue-800
                                @endif
                            ">
                                {{ $complaint->typeLabel() }}
                            </span>
                        </div>
                        <div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @if ($complaint->status === 'pending')
                                    bg-yellow-100 text-yellow-800
                                @elseif ($complaint->status === 'reviewed')
                                    bg-blue-100 text-blue-800
                                @else
                                    bg-green-100 text-green-800
                                @endif
                            ">
                                {{ $complaint->statusLabel() }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Details -->
            <div class="p-6 md:p-8 space-y-6">
                <!-- Produit -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">Produit</h3>
                    <div class="flex items-center gap-3">
                        @if ($complaint->product->image_url)
                            <img src="{{ asset('storage/' . $complaint->product->image_url) }}" alt="{{ $complaint->product->name }}" class="w-16 h-16 object-cover rounded-lg">
                        @else
                            <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                        <div>
                            <h4 class="font-semibold text-gray-900">{{ $complaint->product->name }}</h4>
                            <p class="text-gray-600 text-sm">{{ $complaint->product->description }}</p>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">Description</h3>
                    <div class="bg-gray-50 p-4 rounded-lg text-gray-900 whitespace-pre-wrap">
                        {{ $complaint->description }}
                    </div>
                </div>

                <!-- Date -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">Date</h3>
                    <p class="text-gray-900">{{ $complaint->date->format('d/m/Y') }}</p>
                </div>

                <!-- Response (if reviewed) -->
                @if ($complaint->status !== 'pending' && $complaint->response)
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">Réponse</h3>
                        <div class="bg-green-50 p-4 rounded-lg border border-green-200 text-gray-900 whitespace-pre-wrap">
                            {{ $complaint->response }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="p-6 md:p-8 border-t border-gray-200 bg-gray-50">
                <div class="flex flex-col gap-3 md:flex-row md:justify-between">
                    <a href="{{ route('terrain-complaints.index') }}" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold rounded-lg transition text-center">
                        ← Retour
                    </a>
                    @can('delete', $complaint)
                        <form method="POST" action="{{ route('terrain-complaints.index') }}" class="inline" onsubmit="return confirm('Êtes-vous sûr ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition">
                                Supprimer
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
