<?php

namespace App\Http\Controllers;

use App\Models\DailyClosure;
use App\Services\DailyClosureService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class DailyClosureController extends Controller
{
    public function __construct(private DailyClosureService $service) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $today = CarbonImmutable::today();

        $closures = DailyClosure::query()
            ->where('user_id', $user->id)
            ->with('day')
            ->latest('closed_at')
            ->paginate(20);

        return view('closures.index', [
            'closures' => $closures,
            'isWorkingDay' => $this->service->isWorkingDay($today),
            'alreadyClosed' => $this->service->alreadyClosed($user, $today),
            'today' => $today,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        try {
            $closure = $this->service->close($user, CarbonImmutable::today());
        } catch (RuntimeException $e) {
            return back()->with('warning', $e->getMessage());
        }

        return redirect()
            ->route('closures.show', $closure)
            ->with('status', 'Journée clôturée : rapport généré automatiquement.');
    }

    public function show(Request $request, DailyClosure $closure): View
    {
        abort_unless($closure->user_id === $request->user()->id, 403);

        $closure->load('day', 'user');

        return view('closures.show', ['closure' => $closure]);
    }
}
