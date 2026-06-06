<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Product;
use App\Models\TerrainComplaint;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TerrainComplaintController extends Controller
{
    /**
     * Display agent's complaints and propositions.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewOwn', TerrainComplaint::class);

        $user = $request->user();
        $filters = $request->validate([
            'type' => ['nullable', 'in:complaint,proposition'],
            'status' => ['nullable', 'in:pending,reviewed,resolved'],
        ]);

        $query = $user->terrainComplaints();

        if ($filters['type'] ?? null) {
            $query->where('type', $filters['type']);
        }

        if ($filters['status'] ?? null) {
            $query->where('status', $filters['status']);
        }

        $complaints = $query
            ->with('product')
            ->latest('date')
            ->paginate(15);

        return view('terrain-complaints.index', [
            'complaints' => $complaints,
            'filters' => $filters,
        ]);
    }

    /**
     * Show form to create a new complaint or proposition.
     */
    public function create(Request $request): View
    {
        Gate::authorize('create', TerrainComplaint::class);

        return view('terrain-complaints.create', [
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a new complaint or proposition.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', TerrainComplaint::class);

        $user = $request->user();

        $data = $request->validate([
            'type' => ['required', 'in:complaint,proposition'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'description' => ['required', 'string', 'min:10'],
            'date' => ['nullable', 'date'],
        ]);

        $data['user_id'] = $user->id;
        $data['date'] = $data['date'] ?? today();
        $data['status'] = 'pending';

        TerrainComplaint::create($data);

        return redirect()
            ->route('terrain-complaints.index')
            ->with('status', 'Votre '.($data['type'] === 'complaint' ? 'plainte' : 'proposition').' a été enregistrée.');
    }

    /**
     * Show complaint details.
     */
    public function show(TerrainComplaint $terrainComplaint): View
    {
        Gate::authorize('view', $terrainComplaint);

        return view('terrain-complaints.show', [
            'complaint' => $terrainComplaint->load('product', 'user'),
        ]);
    }

    /**
     * View all complaints for marketing agent's team.
     */
    public function teamComplaints(Request $request): View
    {
        Gate::authorize('viewTeam', TerrainComplaint::class);

        $user = $request->user();
        $filters = $request->validate([
            'agent_id' => ['nullable', 'integer', 'exists:users,id'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'type' => ['nullable', 'in:complaint,proposition'],
            'status' => ['nullable', 'in:pending,reviewed,resolved'],
            'period' => ['nullable', 'in:jour,semaine,mois'],
        ]);

        $query = $this->buildTeamComplaintQuery($user, $filters);

        $complaints = $query
            ->with(['user', 'product'])
            ->latest('date')
            ->paginate(15);

        return view('terrain-complaints.team', [
            'complaints' => $complaints,
            'agents' => $user->terrains()->orderBy('name')->get(),
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    /**
     * Update complaint status (for marketing agent).
     */
    public function updateStatus(Request $request, TerrainComplaint $terrainComplaint): RedirectResponse
    {
        Gate::authorize('review', $terrainComplaint);

        $data = $request->validate([
            'status' => ['required', 'in:pending,reviewed,resolved'],
            'response' => ['nullable', 'string'],
        ]);

        $terrainComplaint->update($data);

        return back()->with('status', 'Statut de la plainte mis à jour.');
    }

    /**
     * Build query for team complaints with filters
     *
     * @param array<string, mixed> $filters
     */
    private function buildTeamComplaintQuery(User $user, array $filters): Builder
    {
        $query = TerrainComplaint::query();

        // Filter by agent
        if ($filters['agent_id'] ?? null) {
            $query->where('user_id', $filters['agent_id']);
        } else {
            // Show only complaints from the marketing agent's team
            $agentIds = $user->terrains()->pluck('id')->toArray();
            $query->whereIn('user_id', $agentIds);
        }

        // Filter by product
        if ($filters['product_id'] ?? null) {
            $query->where('product_id', $filters['product_id']);
        }

        // Filter by type
        if ($filters['type'] ?? null) {
            $query->where('type', $filters['type']);
        }

        // Filter by status
        if ($filters['status'] ?? null) {
            $query->where('status', $filters['status']);
        }

        // Filter by period
        if ($filters['period'] ?? null) {
            $query = $this->filterByPeriod($query, $filters['period']);
        }

        return $query;
    }

    /**
     * Filter query by period (jour, semaine, mois)
     */
    private function filterByPeriod(Builder $query, string $period): Builder
    {
        return match ($period) {
            'jour' => $query->whereDate('date', today()),
            'semaine' => $query->whereBetween('date', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ]),
            'mois' => $query->whereBetween('date', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ]),
            default => $query,
        };
    }
}

