@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Plaintes & Propositions de l'équipe</h1>
            <p class="text-gray-600 mt-2">Consultez et gérez les plaintes/propositions de vos agents terrain</p>
        </div>

        <!-- Status Messages -->
        @if (session('status'))
            <div class="mb-4 p-4 text-green-700 bg-green-50 border border-green-200 rounded-lg">
                {{ session('status') }}
            </div>
        @endif

        <!-- Filters -->
        <div class="mb-6 bg-white rounded-lg shadow p-4">
            <form method="GET" action="{{ route('terrain-complaints.team') }}" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                    <select name="agent_id" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Tous les agents</option>
                        @foreach ($agents as $agent)
                            <option value="{{ $agent->id }}" @selected($filters['agent_id'] == $agent->id)>
                                {{ $agent->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="product_id" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Tous les produits</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected($filters['product_id'] == $product->id)>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>

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

                    <select name="period" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Toutes les périodes</option>
                        <option value="jour" @selected($filters['period'] === 'jour')>Aujourd'hui</option>
                        <option value="semaine" @selected($filters['period'] === 'semaine')>Cette semaine</option>
                        <option value="mois" @selected($filters['period'] === 'mois')>Ce mois</option>
                    </select>
                </div>

                <div class="flex flex-col gap-2 md:flex-row md:justify-end">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition">
                        Filtrer
                    </button>
                    <a href="{{ route('terrain-complaints.team') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition text-center">
                        Réinitialiser
                    </a>
                </div>
            </form>
        </div>

        <!-- Complaints Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            @if ($complaints->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agent</th>
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $complaint->user->name }}
                                    </td>
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
                                        {{ $complaint->product->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <form method="POST" action="{{ route('terrain-complaints.update-status', $complaint) }}" class="inline-block">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" class="px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                onchange="this.form.submit()">
                                                <option value="pending" @selected($complaint->status === 'pending')>En attente</option>
                                                <option value="reviewed" @selected($complaint->status === 'reviewed')>Examinée</option>
                                                <option value="resolved" @selected($complaint->status === 'resolved')>Résolue</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $complaint->date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
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
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
