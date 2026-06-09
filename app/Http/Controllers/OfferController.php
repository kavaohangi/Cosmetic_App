<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function index(): View
    {
        $offers = Offer::query()
            ->with('product')
            ->latest()
            ->paginate(20);

        return view('offers.index', ['offers' => $offers]);
    }

    public function create(): View
    {
        return view('offers.create', [
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'remise_pourcentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
            'actif' => ['nullable', 'boolean'],
        ]);

        Offer::create([
            ...$data,
            'actif' => $request->boolean('actif', true),
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('offers.index')
            ->with('status', 'Offre créée.');
    }

    public function destroy(Offer $offer): RedirectResponse
    {
        $offer->delete();

        return redirect()
            ->route('offers.index')
            ->with('status', 'Offre supprimée.');
    }
}
