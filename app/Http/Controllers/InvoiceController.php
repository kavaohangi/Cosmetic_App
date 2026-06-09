<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\SaleType;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Services\InvoiceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $invoiceService) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Invoice::class);

        $user = $request->user();

        $invoices = Invoice::query()
            ->where('agent_id', $user->id)
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->string('statut')))
            ->with('client')
            ->latest('date')
            ->paginate(20);

        return view('invoices.index', [
            'invoices' => $invoices,
            'statuts' => InvoiceStatus::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Invoice::class);

        return view('invoices.create', [
            'clients' => Client::query()->where('agent_id', $request->user()->id)->orderBy('name')->get(),
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Invoice::class);

        $data = $request->validate([
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'type_vente' => ['required', 'in:'.implode(',', SaleType::values())],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantite' => ['required', 'integer', 'min:1'],
        ]);

        $invoice = $this->invoiceService->create(
            $data['client_id'],
            $request->user()->id,
            SaleType::from($data['type_vente']),
            $data['items'],
            $data['notes'] ?? null,
        );

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('status', 'Facture créée.');
    }

    public function show(Invoice $invoice): View
    {
        Gate::authorize('view', $invoice);

        $invoice->load(['client', 'agent', 'lines.product']);

        return view('invoices.show', ['invoice' => $invoice]);
    }

    public function pay(Request $request, Invoice $invoice): RedirectResponse
    {
        Gate::authorize('update', $invoice);

        $data = $request->validate([
            'montant' => ['required', 'numeric', 'min:0.01'],
        ]);

        $this->invoiceService->registerPayment($invoice, (float) $data['montant']);

        return back()->with('status', 'Paiement enregistré.');
    }
}
