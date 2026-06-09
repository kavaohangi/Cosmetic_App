<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\StoreClientRequest;
use App\Models\Client;
use App\Models\User;
use App\Services\ClientService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $filters = $request->validate([
            'ville' => ['nullable', 'string'],
            'agent_id' => ['nullable', 'integer', 'exists:users,id'],
            'periode' => ['nullable', 'in:jour,semaine,mois,intervalle'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'avec_credit' => ['nullable', 'boolean'],
            'tri' => ['nullable', 'in:nom,achats'],
        ]);

        [$from, $to] = $this->resolvePeriod($filters);

        $clients = Client::query()
            ->when($filters['ville'] ?? null, fn ($q) => $q->where('ville', $filters['ville']))
            // Un Agent Marketeur ne voit que ses propres clients (relation 1-1).
            ->when($user->role === Role::AgentMarketeur, fn ($q) => $q->where('agent_id', $user->id))
            ->when($filters['agent_id'] ?? null, fn ($q) => $q->where('agent_id', $filters['agent_id']))
            ->when(! empty($filters['avec_credit']), fn ($q) => $q->avecCredit())
            ->withCount(['orders as orders_count' => function ($q) use ($from, $to): void {
                if ($from !== null && $to !== null) {
                    $q->whereBetween('date_commande', [$from, $to]);
                }
            }])
            ->when(
                ($filters['tri'] ?? 'nom') === 'achats',
                fn ($q) => $q->orderByDesc('orders_count'),
                fn ($q) => $q->orderBy('name'),
            )
            ->with('agent')
            ->paginate(20)
            ->withQueryString();

        return view('clients.index', [
            'clients' => $clients,
            'filters' => $filters,
            'agents' => User::query()->where('role', Role::AgentMarketeur->value)->orderBy('name')->get(),
        ]);
    }

    /**
     * Resolve a [from, to] date range from the period filter.
     *
     * @param  array<string, mixed>  $filters
     * @return array{0: ?string, 1: ?string}
     */
    private function resolvePeriod(array $filters): array
    {
        return match ($filters['periode'] ?? null) {
            'jour' => [today()->toDateString(), today()->toDateString()],
            'semaine' => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
            'mois' => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
            'intervalle' => [
                $filters['date_from'] ?? now()->startOfMonth()->toDateString(),
                $filters['date_to'] ?? today()->toDateString(),
            ],
            default => [null, null],
        };
    }

    public function create(): View
    {
        Gate::authorize('create', Client::class);

        return view('clients.create');
    }

    public function store(StoreClientRequest $request, ClientService $clientService): RedirectResponse
    {
        $user = $request->user();

        $result = $clientService->createWithAccount(
            attributes: $request->validated(),
            creator: $user,
            // Chaque client est rattaché à un seul Agent Marketeur.
            agentId: $user->role === Role::AgentMarketeur ? $user->id : $request->input('agent_id'),
        );

        return redirect()
            ->route('clients.show', $result['client'])
            ->with('status', 'Client et compte créés.')
            ->with('client_credentials', [
                'email' => $result['login_email'],
                'password' => $result['plain_password'],
            ]);
    }

    public function show(Request $request, Client $client): View
    {
        $filters = $request->validate([
            'periode' => ['nullable', 'in:jour,semaine,mois,intervalle'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        [$from, $to] = $this->resolvePeriod($filters);

        $orders = $client->orders()
            ->when(
                $from !== null && $to !== null,
                fn ($q) => $q->whereBetween('date_commande', [$from, $to])
            )
            ->latest('date_commande')
            ->get();

        $client->load('creator');

        return view('clients.show', [
            'client' => $client,
            'orders' => $orders,
            'filters' => $filters,
            'totalAchats' => (float) $orders->sum('total'),
        ]);
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
