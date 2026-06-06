<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockAlertController;
use App\Http\Controllers\TerrainController;
use App\Http\Controllers\TerrainComplaintController;
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
        Route::delete('/agents/{agent}', [AgentController::class, 'destroy'])->name('agents.destroy');
    });

    Route::middleware('role:marketeur_terrain')->group(function () {
        Route::get('/terrain/create', [TerrainController::class, 'create'])->name('terrain.create');
        Route::post('/terrain', [TerrainController::class, 'store'])->name('terrain.store');
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
        Route::get('/terrain-complaints/{terrainComplaint}', [TerrainComplaintController::class, 'show'])->name('terrain-complaints.show');
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
    Route::resource('products', ProductController::class);
    Route::resource('clients', ClientController::class);
    Route::resource('orders', OrderController::class)->except(['edit', 'update']);

    Route::middleware('role:chef_marketing|admin')->group(function () {
        Route::patch('/orders/{order}/validate', [OrderController::class, 'validateOrder'])->name('orders.validate');
        Route::patch('/orders/{order}/reject', [OrderController::class, 'rejectOrder'])->name('orders.reject');
    });

    /*
    |--------------------------------------------------------------------------
    | Alertes de stock (rupture / réapprovisionnement)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin|directeur|chef_marketing|magasinier|agent_marketeur')->group(function () {
        Route::get('/stock-alerts', [StockAlertController::class, 'index'])->name('stock-alerts.index');
    });

    // Seul le Magasinier confirme la disponibilité ; l'Admin garde un accès de secours.
    Route::middleware('role:magasinier|admin')->group(function () {
        Route::patch('/stock-alerts/{alert}/resolve', [StockAlertController::class, 'resolve'])->name('stock-alerts.resolve');
    });

    Route::post('/notifications/read-all', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    })->name('notifications.read-all');
});

require __DIR__.'/auth.php';
