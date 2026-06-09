<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Client;
use App\Models\Message;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClientPortalController extends Controller
{
    /**
     * Personal dashboard summarising the client's activity.
     */
    public function dashboard(Request $request): View
    {
        $client = $this->resolveClient($request);

        $baseQuery = Order::query()->where('client_id', $client->id);

        $kpis = [
            'total_commandes' => (clone $baseQuery)->count(),
            'en_cours' => (clone $baseQuery)->whereIn('statut', [
                OrderStatus::Validee->value,
                OrderStatus::EnPreparation->value,
                OrderStatus::PreteALivraison->value,
            ])->count(),
            'livrees' => (clone $baseQuery)->where('statut', OrderStatus::Livree->value)->count(),
            'offres_actives' => Offer::query()->active()->count(),
        ];

        $recentOrders = (clone $baseQuery)
            ->with('items.product')
            ->latest('date_commande')
            ->limit(5)
            ->get();

        $agent = $client->agent;

        $unreadMessages = $agent !== null
            ? Message::query()
                ->where('sender_id', $agent->id)
                ->where('receiver_id', $request->user()->id)
                ->where('lu', false)
                ->count()
            : 0;

        return view('portal.dashboard', [
            'client' => $client,
            'kpis' => $kpis,
            'recentOrders' => $recentOrders,
            'agent' => $agent,
            'unreadMessages' => $unreadMessages,
        ]);
    }

    /**
     * Display the information of the Agent Marketeur in charge of this client.
     */
    public function marketeur(Request $request): View
    {
        $client = $this->resolveClient($request);

        return view('portal.marketeur', [
            'client' => $client,
            'agent' => $client->agent,
        ]);
    }

    /**
     * Conversation between the client and their dedicated Agent Marketeur.
     */
    public function messages(Request $request): View
    {
        $client = $this->resolveClient($request);
        $agent = $this->resolveAgent($client);
        $user = $request->user();

        $conversation = Message::query()
            ->between($user->id, $agent->id)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at')
            ->get();

        Message::query()
            ->where('sender_id', $agent->id)
            ->where('receiver_id', $user->id)
            ->where('lu', false)
            ->update(['lu' => true]);

        return view('portal.messages', [
            'agent' => $agent,
            'conversation' => $conversation,
        ]);
    }

    /**
     * Send a message from the client to their dedicated Agent Marketeur.
     */
    public function sendMessage(Request $request): RedirectResponse
    {
        $client = $this->resolveClient($request);
        $agent = $this->resolveAgent($client);

        $data = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
        ]);

        Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $agent->id,
            'content' => $data['content'],
            'lu' => false,
        ]);

        return redirect()->route('portal.messages')->with('status', 'Message envoyé à votre marketeur.');
    }

    /**
     * Track the connected client's own orders (read-only).
     */
    public function orders(Request $request): View
    {
        $client = $this->resolveClient($request);

        $orders = Order::query()
            ->where('client_id', $client->id)
            ->with('items.product')
            ->latest('date_commande')
            ->paginate(15);

        return view('portal.orders', [
            'client' => $client,
            'orders' => $orders,
        ]);
    }

    /**
     * Browse the active product catalogue.
     */
    public function catalogue(Request $request): View
    {
        $this->resolveClient($request);

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(24);

        return view('portal.catalogue', ['products' => $products]);
    }

    /**
     * View current promotional offers.
     */
    public function offers(Request $request): View
    {
        $this->resolveClient($request);

        $offers = Offer::query()
            ->active()
            ->with('product')
            ->latest()
            ->get();

        return view('portal.offers', ['offers' => $offers]);
    }

    /**
     * The Client model attached to the authenticated client account.
     */
    private function resolveClient(Request $request): Client
    {
        $client = $request->user()->clientProfile;

        abort_if($client === null, Response::HTTP_FORBIDDEN, 'Aucun profil client associé à ce compte.');

        return $client;
    }

    /**
     * The Agent Marketeur in charge of the client (required for messaging).
     */
    private function resolveAgent(Client $client): User
    {
        $agent = $client->agent;

        abort_if($agent === null, Response::HTTP_FORBIDDEN, 'Aucun marketeur ne vous est encore assigné.');

        return $agent;
    }
}
