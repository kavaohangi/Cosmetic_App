<?php

namespace App\Http\Controllers;

use App\Models\ConversionRate;
use App\Services\ConversionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConversionRateController extends Controller
{
    public function index(): View
    {
        return view('conversion-rates.index', [
            'current' => ConversionRate::current(),
            'history' => ConversionRate::query()->with('definer')->latest()->limit(20)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'taux_fc' => ['required', 'numeric', 'min:0.0001'],
        ]);

        DB::transaction(function () use ($data, $request): void {
            // Une seule devise active à la fois.
            ConversionRate::query()->where('actif', true)->update(['actif' => false]);

            ConversionRate::create([
                'taux_fc' => $data['taux_fc'],
                'defined_by' => $request->user()->id,
                'actif' => true,
            ]);
        });

        ConversionService::flushCache();

        return redirect()
            ->route('conversion-rates.index')
            ->with('status', 'Taux de change mis à jour.');
    }
}
