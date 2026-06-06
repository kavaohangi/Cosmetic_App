<?php

namespace App\Http\Controllers;

use App\Models\TerrainReport;
use App\Models\User;
use App\Notifications\RapportTerrainSoumis;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Team sales reporting for an Agent Marketeur, filterable by period and agent.
     */
    public function terrain(Request $request): View
    {
        return view('reports.terrain', $this->gather($request));
    }

    /**
     * Build the full reporting dataset shared by the page and the exports.
     *
     * @return array<string, mixed>
     */
    private function gather(Request $request): array
    {
        $filters = $request->validate([
            'period' => ['nullable', 'string', 'in:jour,semaine,mois'],
            'agent_id' => ['nullable', 'integer', 'exists:users,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $user = $request->user();
        $period = $filters['period'] ?? 'mois';

        [$from, $to] = $this->resolveRange($period, $filters);

        $reports = $this->fetchReports($user->id, $from, $to, $filters['agent_id'] ?? null);

        return [
            'agent' => $user,
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'filters' => $filters,
            'agents' => $user->terrains()->orderBy('name')->get(),
            'reports' => $reports,
            'leaderboard' => $this->buildLeaderboard($reports),
            'topProducts' => $this->buildTopProducts($reports),
            'totalCa' => (float) $reports->sum('montant_total'),
            'totalUnites' => (int) $reports->sum('nb_ventes'),
        ];
    }

    private function periodeLabel(CarbonImmutable $from, CarbonImmutable $to): string
    {
        return $from->format('d/m/Y').' - '.$to->format('d/m/Y');
    }

    /**
     * Export the current report as a PDF document.
     */
    public function exportPdf(Request $request): Response
    {
        $data = $this->gather($request);
        $data['periodeLabel'] = $this->periodeLabel($data['from'], $data['to']);

        $pdf = Pdf::loadView('reports.pdf', $data)->setPaper('a4', 'portrait');

        return $pdf->download('rapport-terrain-'.now()->format('Ymd-His').'.pdf');
    }

    /**
     * Export the current report as a spreadsheet (Excel-compatible).
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $data = $this->gather($request);
        $filename = 'rapport-terrain-'.now()->format('Ymd-His').'.xls';

        return response()->streamDownload(function () use ($data): void {
            echo $this->buildSpreadsheetHtml($data);
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    /**
     * Submit the monthly report to the Chef Marketing (the agent's supervisor).
     */
    public function submitToChef(Request $request): RedirectResponse
    {
        $data = $this->gather($request);
        $user = $data['agent'];
        $chef = $user->supervisor;

        if (! $chef) {
            return back()->with('warning', "Aucun Chef Marketing (sup\u00e9rieur) n'est rattach\u00e9 \u00e0 votre compte.");
        }

        $chef->notify(new RapportTerrainSoumis(
            $user,
            $this->periodeLabel($data['from'], $data['to']),
            $data['totalCa'],
            $data['totalUnites'],
            $data['reports']->count(),
        ));

        return back()->with('status', 'Rapport soumis au Chef Marketing ('.$chef->name.').');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function buildSpreadsheetHtml(array $data): string
    {
        $esc = fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES);
        $rows = '';

        $rows .= '<tr><th colspan="3" style="background:#eee">Classement des agents</th></tr>';
        $rows .= '<tr><th>Agent</th><th>Unités</th><th>CA (FCFA)</th></tr>';
        foreach ($data['leaderboard'] as $row) {
            $rows .= '<tr><td>'.$esc($row['user']?->name ?? '-').'</td><td>'.$row['unites'].'</td><td>'.$row['ca'].'</td></tr>';
        }

        $rows .= '<tr><th colspan="3" style="background:#eee">Produits les plus vendus</th></tr>';
        $rows .= '<tr><th>Produit</th><th>Quantité</th><th>CA (FCFA)</th></tr>';
        foreach ($data['topProducts'] as $p) {
            $rows .= '<tr><td>'.$esc($p['name']).'</td><td>'.$p['quantite'].'</td><td>'.$p['ca'].'</td></tr>';
        }

        $rows .= '<tr><th colspan="3" style="background:#eee">Détail des rapports</th></tr>';
        $rows .= '<tr><th>Date</th><th>Agent</th><th>CA (FCFA)</th></tr>';
        foreach ($data['reports'] as $report) {
            $rows .= '<tr><td>'.$esc($report->date?->format('d/m/Y')).'</td><td>'.$esc($report->user?->name).'</td><td>'.$report->montant_total.'</td></tr>';
        }

        $periode = $this->periodeLabel($data['from'], $data['to']);

        return '<html><head><meta charset="UTF-8"></head><body>'
            .'<h3>Rapport terrain — '.$esc($periode).'</h3>'
            .'<p>CA total : '.$data['totalCa'].' FCFA · Unités : '.$data['totalUnites'].'</p>'
            .'<table border="1" cellspacing="0" cellpadding="4">'.$rows.'</table>'
            .'</body></html>';
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function resolveRange(string $period, array $filters): array
    {
        if (! empty($filters['date_from']) || ! empty($filters['date_to'])) {
            $from = ! empty($filters['date_from']) ? CarbonImmutable::parse($filters['date_from'])->startOfDay() : CarbonImmutable::now()->startOfMonth();
            $to = ! empty($filters['date_to']) ? CarbonImmutable::parse($filters['date_to'])->endOfDay() : CarbonImmutable::now()->endOfDay();

            return [$from, $to];
        }

        $now = CarbonImmutable::now();

        return match ($period) {
            'jour' => [$now->startOfDay(), $now->endOfDay()],
            'semaine' => [$now->startOfWeek(), $now->endOfWeek()],
            default => [$now->startOfMonth(), $now->endOfMonth()],
        };
    }

    /**
     * @return Collection<int, TerrainReport>
     */
    private function fetchReports(int $supervisorId, CarbonImmutable $from, CarbonImmutable $to, ?int $agentId): Collection
    {
        return TerrainReport::query()
            ->where('supervisor_id', $supervisorId)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->when($agentId, fn ($q) => $q->where('user_id', $agentId))
            ->with(['user', 'items.product'])
            ->latest('date')
            ->get();
    }

    /**
     * Ranking of terrain agents by revenue.
     *
     * @param  Collection<int, TerrainReport>  $reports
     * @return Collection<int, array{user:User, ca:float, unites:int, reports:int}>
     */
    private function buildLeaderboard(Collection $reports): Collection
    {
        return $reports
            ->groupBy('user_id')
            ->map(function (Collection $group): array {
                return [
                    'user' => $group->first()->user,
                    'ca' => (float) $group->sum('montant_total'),
                    'unites' => (int) $group->sum(fn (TerrainReport $r) => $r->items->sum('quantite')),
                    'reports' => $group->count(),
                ];
            })
            ->sortByDesc('ca')
            ->values();
    }

    /**
     * Best selling products across the period.
     *
     * @param  Collection<int, TerrainReport>  $reports
     * @return Collection<int, array{name:string, quantite:int, ca:float}>
     */
    private function buildTopProducts(Collection $reports): Collection
    {
        return $reports
            ->flatMap(fn (TerrainReport $r) => $r->items)
            ->groupBy('product_id')
            ->map(function (Collection $items): array {
                return [
                    'name' => $items->first()->product?->name ?? '—',
                    'quantite' => (int) $items->sum('quantite'),
                    'ca' => (float) $items->sum('sous_total'),
                ];
            })
            ->sortByDesc('ca')
            ->values();
    }
}
