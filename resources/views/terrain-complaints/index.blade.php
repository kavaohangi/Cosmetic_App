@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Plaintes & Propositions</h1>
                    <p class="text-gray-600 mt-2">Gérez vos plaintes clients et propositions d'amélioration</p>
                </div>
                <a href="{{ route('terrain-complaints.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition">
                    + Nouvelle plainte/proposition
                </a>
            </div>
        </div>

        <!-- Status Messages -->
        @if (session('status'))
            <div class="mb-4 p-4 text-green-700 bg-green-50 border border-green-200 rounded-lg">
                {{ session('status') }}
            </div>
        @endif

        <!-- Filters -->
        <div class="mb-6 bg-white rounded-lg shadow p-4">
            <form method="GET" action="{{ route('terrain-complaints.index') }}" class="flex flex-col gap-4 md:flex-row md:items-center md:gap-3">
                <select name="type" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Tous les types</option>
                    <option value="complaint" @selected($filters['type'] === 'complaint')>Plaintes</option>
                    <option value="proposition" @selected($filters['type'] === 'proposition')>Propositions</option>
                </select>

                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Tous les statuts</option>
                    <option value="pending" @selected($filters['status'] === 'pending')>En attente</option>
                    <option value="reviewed" @selected($filters['status'] === 'reviewed')>Examinée</option>
                    <option value="resolved" @selected($filters['status'] === 'resolved')>Résolue</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition">
                    Filtrer
                </button>
                <a href="{{ route('terrain-complaints.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">
                    Réinitialiser
                </a>
            </form>
        </div>

        <!-- Complaints List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            @if ($complaints->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($complaints as $complaint)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                            @if ($complaint->type === 'complaint')
                                                bg-red-100 text-red-800
                                            @else
                                                bg-blue-100 text-blue-800
                                            @endif
                                        ">
                                            {{ $complaint->typeLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="font-semibold">{{ $complaint->product->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
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
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $complaint->date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="{{ route('terrain-complaints.show', $complaint) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                            Voir
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="bg-white px-4 py-3 border-t border-gray-200 flex justify-center">
                    {{ $complaints->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <p class="text-gray-600 text-lg">Aucune plainte ou proposition trouvée.</p>
                    <a href="{{ route('terrain-complaints.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition">
                        + Créer une plainte/proposition
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
