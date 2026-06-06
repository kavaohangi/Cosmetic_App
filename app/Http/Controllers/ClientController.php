<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Models\Client;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $clients = Client::query()
            ->when($request->filled('ville'), fn ($q) => $q->where('ville', $request->string('ville')))
            ->withCount('orders')
            ->orderBy('name')
            ->paginate(20);

        return view('clients.index', ['clients' => $clients]);
    }

    public function create(): View
    {
        Gate::authorize('create', Client::class);

        return view('clients.create');
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $client = Client::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('clients.show', $client)
            ->with('status', 'Client créé.');
    }

    public function show(Client $client): View
    {
        $client->load(['orders', 'creator']);

        return view('clients.show', ['client' => $client]);
    }

    public function edit(Client $client): View
    {
        Gate::authorize('update', $client);

        return view('clients.edit', ['client' => $client]);
    }

    public function update(StoreClientRequest $request, Client $client): RedirectResponse
    {
        Gate::authorize('update', $client);

        $client->update($request->validated());

        return redirect()
            ->route('clients.show', $client)
            ->with('status', 'Client mis à jour.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        Gate::authorize('delete', $client);

        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('status', 'Client supprimé.');
    }
}
