<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AgentController extends Controller
{
    public function create(Request $request): View
    {
        return view('agents.create', [
            'subordinateRole' => $this->subordinateRoleFor($request->user()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $creator = $request->user();
        $role = $this->subordinateRoleFor($creator);

        $magasinRule = $role === Role::MarketeurTerrain
            ? ['required', 'string', 'max:255']
            : ['nullable', 'string', 'max:255'];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'magasin' => $magasinRule,
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'magasin.required' => 'Un Marketeur Terrain doit obligatoirement être associé à un magasin.',
        ]);

        $agent = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'magasin' => $role === Role::MarketeurTerrain ? ($data['magasin'] ?? null) : null,
            'password' => Hash::make($data['password']),
            'role' => $role,
            'supervisor_id' => $creator->id,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $agent->syncRoles([$role->value]);

        return redirect()
            ->route('terrain.team')
            ->with('status', "{$role->label()} créé : {$agent->name}");
    }

    public function show(Request $request, User $agent): View
    {
        abort_unless($this->canManage($request->user(), $agent), 403);

        $agent->loadCount(['orders', 'managedClients']);

        $orders = $agent->orders()
            ->with('client')
            ->latest('date_commande')
            ->limit(15)
            ->get();

        return view('agents.show', [
            'agent' => $agent,
            'orders' => $orders,
        ]);
    }

    /**
     * Activate / deactivate a field user (e.g. mark as on leave).
     */
    public function toggleActive(Request $request, User $agent): RedirectResponse
    {
        abort_unless($this->canManage($request->user(), $agent), 403);

        $agent->update(['is_active' => ! $agent->is_active]);

        $etat = $agent->is_active ? 'activé' : 'désactivé';

        return back()->with('status', "{$agent->name} a été {$etat}.");
    }

    public function destroy(Request $request, User $agent): RedirectResponse
    {
        $creator = $request->user();

        abort_unless($this->canManage($creator, $agent), 403);

        $name = $agent->name;
        $agent->delete();

        return redirect()
            ->route('terrain.team')
            ->with('status', "Utilisateur supprimé : {$name}");
    }

    /**
     * Determine which role a creator may assign to a new subordinate.
     */
    private function subordinateRoleFor(User $creator): Role
    {
        return match ($creator->role) {
            Role::Admin, Role::ChefMarketing => Role::AgentMarketeur,
            Role::AgentMarketeur => Role::MarketeurTerrain,
            default => abort(403, "Vous n'êtes pas autorisé à créer un utilisateur."),
        };
    }

    /**
     * Admin & Chef Marketing manage everyone; an Agent Marketeur only manages
     * his own terrain agents.
     */
    private function canManage(User $creator, User $target): bool
    {
        if (in_array($creator->role, [Role::Admin, Role::ChefMarketing], true)) {
            return true;
        }

        return $creator->role === Role::AgentMarketeur
            && $target->role === Role::MarketeurTerrain
            && $target->supervisor_id === $creator->id;
    }
}
