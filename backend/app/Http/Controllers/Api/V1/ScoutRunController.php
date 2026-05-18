<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ScoutRun;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScoutRunController extends Controller
{
    /**
     * GET /api/v1/admin/scout-runs
     *
     * List scout run history with optional filters.
     *
     * Query params:
     *   country   — ISO 2-letter code, e.g. ?country=MX
     *   finished  — 1 to show only finished runs
     *   per_page  — items per page (default 20, max 100)
     */
    public function index(Request $request): JsonResponse
    {
        $query = ScoutRun::with('country')
            ->orderByDesc('started_at');

        if ($request->filled('country')) {
            $query->forCountry($request->country);
        }

        if ($request->boolean('finished')) {
            $query->finished();
        }

        $perPage = min((int) $request->get('per_page', 20), 100);
        $runs    = $query->paginate($perPage);

        return response()->json([
            'data' => $runs->map(fn ($run) => $this->format($run)),
            'meta' => [
                'current_page' => $runs->currentPage(),
                'last_page'    => $runs->lastPage(),
                'per_page'     => $runs->perPage(),
                'total'        => $runs->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/admin/scout-runs/{id}
     *
     * Show a single scout run.
     */
    public function show(int $id): JsonResponse
    {
        $run = ScoutRun::with('country')->findOrFail($id);

        return response()->json(['data' => $this->format($run)]);
    }

    /**
     * GET /api/v1/admin/scout-runs/stats
     *
     * Aggregate stats across all runs.
     */
    public function stats(): JsonResponse
    {
        $totals = ScoutRun::finished()->selectRaw(
            'COUNT(*) as total_runs,
             SUM(urls_found)   as total_found,
             SUM(urls_saved)   as total_saved,
             SUM(urls_skipped) as total_skipped'
        )->first();

        $byCountry = ScoutRun::finished()
            ->selectRaw('country_name, COUNT(*) as runs, SUM(urls_saved) as saved')
            ->groupBy('country_name')
            ->orderByDesc('runs')
            ->limit(20)
            ->get();

        $recentRuns = ScoutRun::with('country')
            ->orderByDesc('started_at')
            ->limit(5)
            ->get()
            ->map(fn ($r) => $this->format($r));

        return response()->json([
            'data' => [
                'totals'      => $totals,
                'by_country'  => $byCountry,
                'recent_runs' => $recentRuns,
            ],
        ]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function format(ScoutRun $run): array
    {
        return [
            'id'               => $run->id,
            'country'          => [
                'id'    => $run->country?->id,
                'code'  => $run->country?->code,
                'name'  => $run->country_name,
                'emoji' => $run->country?->flag_emoji,
            ],
            'query'            => $run->query,
            'urls_found'       => $run->urls_found,
            'urls_saved'       => $run->urls_saved,
            'urls_skipped'     => $run->urls_skipped,
            'is_finished'      => $run->is_finished,
            'duration_seconds' => $run->duration_seconds,
            'started_at'       => $run->started_at?->toIso8601String(),
            'finished_at'      => $run->finished_at?->toIso8601String(),
            'notes'            => $run->notes,
        ];
    }
}
