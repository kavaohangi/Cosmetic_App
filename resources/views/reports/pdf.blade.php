<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #1f2937; font-size: 12px; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        h2 { font-size: 14px; margin: 18px 0 6px; border-bottom: 1px solid #ddd; padding-bottom: 4px; }
        .muted { color: #6b7280; font-size: 11px; }
        .kpis { margin: 10px 0; }
        .kpis span { display: inline-block; margin-right: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; font-size: 11px; text-transform: uppercase; color: #6b7280; }
        td.num, th.num { text-align: right; }
    </style>
</head>
<body>
    <h1>Rapport terrain</h1>
    <p class="muted">Agent marketeur : {{ $agent->name }} · Période : {{ $periodeLabel }}</p>
    <p class="muted">Généré le {{ now()->format('d/m/Y H:i') }}</p>

    <div class="kpis">
        <span><strong>CA total :</strong> {{ number_format($totalCa, 0, ',', ' ') }} FCFA</span>
        <span><strong>Unités vendues :</strong> {{ $totalUnites }}</span>
        <span><strong>Rapports :</strong> {{ $reports->count() }}</span>
    </div>

    <h2>Classement des agents</h2>
    <table>
        <thead><tr><th>#</th><th>Agent</th><th>Magasin</th><th class="num">Unités</th><th class="num">Prix total (FCFA)</th></tr></thead>
        <tbody>
        @forelse ($leaderboard as $i => $row)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $row['user']?->name ?? '—' }}</td>
                <td>{{ $row['magasin'] ?? '—' }}</td>
                <td class="num">{{ $row['unites'] }}</td>
                <td class="num">{{ number_format($row['ca'], 0, ',', ' ') }}</td>
            </tr>
        @empty
            <tr><td colspan="5">Aucune donnée.</td></tr>
        @endforelse
        </tbody>
    </table>

    <h2>Produits les plus vendus</h2>
    <table>
        <thead><tr><th>Produit</th><th class="num">Quantité</th><th class="num">CA (FCFA)</th></tr></thead>
        <tbody>
        @forelse ($topProducts as $p)
            <tr>
                <td>{{ $p['name'] }}</td>
                <td class="num">{{ $p['quantite'] }}</td>
                <td class="num">{{ number_format($p['ca'], 0, ',', ' ') }}</td>
            </tr>
        @empty
            <tr><td colspan="3">Aucune vente.</td></tr>
        @endforelse
        </tbody>
    </table>

    <h2>Détail des rapports</h2>
    <table>
        <thead><tr><th>Date</th><th>Agent</th><th class="num">Unités</th><th class="num">CA (FCFA)</th></tr></thead>
        <tbody>
        @forelse ($reports as $report)
            <tr>
                <td>{{ $report->date?->format('d/m/Y') }}</td>
                <td>{{ $report->user?->name }}</td>
                <td class="num">{{ $report->items->sum('quantite') }}</td>
                <td class="num">{{ number_format($report->montant_total, 0, ',', ' ') }}</td>
            </tr>
        @empty
            <tr><td colspan="4">Aucun rapport.</td></tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
