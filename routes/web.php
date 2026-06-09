<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientPortalController;
use App\Http\Controllers\ConversionRateController;
use App\Http\Controllers\DailyClosureController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockAlertController;
use App\Http\Controllers\TerrainComplaintController;
use App\Http\Controllers\TerrainController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect('/login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | Rapports terrain
    |--------------------------------------------------------------------------
    */
    Route::get('/terrain', [TerrainController::class, 'index'])->name('terrain.index');

    Route::get('/terrain/equipe', [TerrainController::class, 'team'])
        ->middleware('role:agent_marketeur|chef_marketing|directeur|admin')
        ->name('terrain.team');

    // Gestion des utilisateurs : Admin & Chef Marketing gèrent tout le monde ;
    // un Agent Marketeur peut gérer ses propres agents de terrain.
    Route::middleware('role:admin|chef_marketing|agent_marketeur')->group(function () {
        Route::get('/agents/create', [AgentController::class, 'create'])->name('agents.create');
        Route::post('/agents', [AgentController::class, 'store'])->name('agents.store');
        Route::get('/agents/{agent}', [AgentController::class, 'show'])->name('agents.show');
        Route::patch('/agents/{agent}/toggle-active', [AgentController::class, 'toggleActive'])->name('agents.toggle-active');
        Route::delete('/agents/{agent}', [AgentController::class, 'destroy'])->name('agents.destroy');
    });

    // Taux de change USD -> FC, défini par le Chef Marketing.
    Route::middleware('role:chef_marketing|admin|directeur')->group(function () {
        Route::get('/taux-change', [ConversionRateController::class, 'index'])->name('conversion-rates.index');
        Route::post('/taux-change', [ConversionRateController::class, 'store'])->name('conversion-rates.store');
    });

    Route::middleware('role:marketeur_terrain')->group(function () {
        Route::get('/terrain/create', [TerrainController::class, 'create'])->name('terrain.create');
        Route::post('/terrain', [TerrainController::class, 'store'])->name('terrain.store');
        Route::get('/mon-evaluation', [ReportController::class, 'mine'])->name('reports.mine');
    });

    // Rapports d'équipe terrain (Agent Marketeur) : filtres + classement + exports
    Route::middleware('role:agent_marketeur|chef_marketing|directeur|admin')->group(function () {
        Route::get('/rapports/terrain', [ReportController::class, 'terrain'])->name('reports.terrain');
        Route::get('/rapports/terrain/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.terrain.pdf');
        Route::get('/rapports/terrain/export/excel', [ReportController::class, 'exportExcel'])->name('reports.terrain.excel');
        Route::post('/rapports/terrain/soumettre', [ReportController::class, 'submitToChef'])->name('reports.terrain.submit');
    });

    Route::get('/terrain/{terrain}', [TerrainController::class, 'show'])->name('terrain.show');

    /*
    |--------------------------------------------------------------------------
    | Plaintes et propositions terrain
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:marketeur_terrain')->group(function () {
        Route::get('/terrain-complaints', [TerrainComplaintController::class, 'index'])->name('terrain-complaints.index');
        Route::get('/terrain-complaints/create', [TerrainComplaintController::class, 'create'])->name('terrain-complaints.create');
        Route::post('/terrain-complaints', [TerrainComplaintController::class, 'store'])->name('terrain-complaints.store');
        Route::get('/terrain-complaints/{terrainComplaint}', [TerrainComplaintController::class, 'show'])->whereNumber('terrainComplaint')->name('terrain-complaints.show');
    });

    // Plaintes de l'équipe terrain (Agent Marketeur)
    Route::middleware('role:agent_marketeur|chef_marketing|directeur|admin')->group(function () {
        Route::get('/terrain-complaints/team', [TerrainComplaintController::class, 'teamComplaints'])->name('terrain-complaints.team');
        Route::patch('/terrain-complaints/{terrainComplaint}/status', [TerrainComplaintController::class, 'updateStatus'])->name('terrain-complaints.update-status');
    });

    /*
    |--------------------------------------------------------------------------
    | Messagerie (chat hiérarchie + collègues même niveau)
    |--------------------------------------------------------------------------
    */
    // Messagerie réservée aux agents marketeurs et marketeurs terrain.
    Route::middleware('role:agent_marketeur|marketeur_terrain')->group(function () {
        Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
        Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');

        Route::middleware('can:chat-with,user')->group(function () {
            Route::get('/messages/{user}', [MessageController::class, 'index'])->name('messages.conversation');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Ressources métier
    |--------------------------------------------------------------------------
    */
    // Vue Magasinier : stock vs commandes + ajustement de stock
    Route::middleware('role:magasinier|chef_marketing|admin|directeur')->group(function () {
        Route::get('/produits-commandes', [ProductController::class, 'orders'])->name('products.orders');
        Route::patch('/products/{product}/adjust', [ProductController::class, 'adjust'])->name('products.adjust');
    });

    Route::resource('products', ProductController::class);
    Route::resource('clients', ClientController::class);
    Route::resource('orders', OrderController::class)->except(['edit', 'update']);

    Route::middleware('role:chef_marketing|admin')->group(function () {
        Route::patch('/orders/{order}/validate', [OrderController::class, 'validateOrder'])->name('orders.validate');
        Route::patch('/orders/{order}/reject', [OrderController::class, 'rejectOrder'])->name('orders.reject');
    });

    /*
    |--------------------------------------------------------------------------
    | Livraisons (bons de livraison) & bons de sortie
    |--------------------------------------------------------------------------
    */
    // Seul le Chef Marketing (et l'Admin) émet le bon de sortie à partir d'une commande validée.
    // Déclaré avant la route wildcard {delivery} pour éviter toute collision sur "create".
    Route::middleware('role:admin|chef_marketing')->group(function () {
        Route::get('/deliveries/create', [DeliveryController::class, 'create'])->name('deliveries.create');
        Route::post('/deliveries', [DeliveryController::class, 'store'])->name('deliveries.store');
    });

    // La sortie physique est confirmée par le Magasinier (Chef Marketing / Admin en secours).
    Route::middleware('role:magasinier|chef_marketing|admin')->group(function () {
        Route::patch('/deliveries/{delivery}/confirm', [DeliveryController::class, 'confirm'])->name('deliveries.confirm');
    });

    // Consultation des bons de livraison/sortie (l'Agent Marketeur est en lecture seule).
    Route::middleware('role:admin|directeur|chef_marketing|agent_marketeur|magasinier')->group(function () {
        Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');
        Route::get('/deliveries/{delivery}', [DeliveryController::class, 'show'])->name('deliveries.show');
    });

    /*
    |--------------------------------------------------------------------------
    | Factures (Agent Marketeur)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:agent_marketeur|chef_marketing|admin|directeur')->group(function () {
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::patch('/invoices/{invoice}/pay', [InvoiceController::class, 'pay'])->name('invoices.pay');
    });

    /*
    |--------------------------------------------------------------------------
    | Clôture journalière (Magasinier, Chef Marketing, Agent Marketeur, Terrain)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:magasinier|chef_marketing|agent_marketeur|marketeur_terrain')->group(function () {
        Route::get('/clotures', [DailyClosureController::class, 'index'])->name('closures.index');
        Route::post('/clotures', [DailyClosureController::class, 'store'])->name('closures.store');
        Route::get('/clotures/{closure}', [DailyClosureController::class, 'show'])->name('closures.show');
    });

    /*
    |--------------------------------------------------------------------------
    | Alertes de stock (rupture / réapprovisionnement)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin|directeur|chef_marketing|magasinier|agent_marketeur')->group(function () {
        Route::get('/stock-alerts', [StockAlertController::class, 'index'])->name('stock-alerts.index');
    });

    // Le Magasinier peut signaler une rupture au Chef Marketing (facultatif).
    Route::middleware('role:magasinier')->group(function () {
        Route::get('/stock-alerts/create', [StockAlertController::class, 'create'])->name('stock-alerts.create');
        Route::post('/stock-alerts', [StockAlertController::class, 'store'])->name('stock-alerts.store');
    });

    // Seul le Magasinier confirme la disponibilité ; l'Admin garde un accès de secours.
    Route::middleware('role:magasinier|admin')->group(function () {
        Route::patch('/stock-alerts/{alert}/resolve', [StockAlertController::class, 'resolve'])->name('stock-alerts.resolve');
    });

    /*
    |--------------------------------------------------------------------------
    | Espace client (lecture seule) : suivi commandes, catalogue, offres
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:client')->prefix('mon-espace')->group(function () {
        Route::get('/', [ClientPortalController::class, 'dashboard'])->name('portal.dashboard');
        Route::get('/commandes', [ClientPortalController::class, 'orders'])->name('portal.orders');
        Route::get('/catalogue', [ClientPortalController::class, 'catalogue'])->name('portal.catalogue');
        Route::get('/offres', [ClientPortalController::class, 'offers'])->name('portal.offers');
        Route::get('/mon-marketeur', [ClientPortalController::class, 'marketeur'])->name('portal.marketeur');
        Route::get('/messages', [ClientPortalController::class, 'messages'])->name('portal.messages');
        Route::post('/messages', [ClientPortalController::class, 'sendMessage'])->name('portal.messages.send');
    });

    /*
    |--------------------------------------------------------------------------
    | Offres / promotions (gérées par le Chef Marketing)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:chef_marketing|admin')->group(function () {
        Route::get('/offres', [OfferController::class, 'index'])->name('offers.index');
        Route::get('/offres/create', [OfferController::class, 'create'])->name('offers.create');
        Route::post('/offres', [OfferController::class, 'store'])->name('offers.store');
        Route::delete('/offres/{offer}', [OfferController::class, 'destroy'])->name('offers.destroy');
    });

    Route::post('/notifications/read-all', function () {
        auth()->user()->unreadNotifications->markAsRead();

        return back();
    })->name('notifications.read-all');
});

require __DIR__.'/auth.php';
