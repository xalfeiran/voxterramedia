<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\MediaOutlet;

class StatsController extends Controller
{
    public function index()
    {
        $total = MediaOutlet::active()->count();

        $byCountry = Country::withCount(['regions'])
            ->get()
            ->map(fn($c) => [
                'code'    => $c->code,
                'name'    => $c->name,
                'name_es' => $c->name_es,
                'emoji'   => $c->flag_emoji,
                'outlets' => MediaOutlet::active()
                    ->whereHas('city.region.country', fn($q) => $q->where('id', $c->id))
                    ->count(),
            ]);

        $byType = MediaOutlet::active()
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        $byLanguage = MediaOutlet::active()
            ->selectRaw('language, count(*) as count')
            ->groupBy('language')
            ->pluck('count', 'language');

        return response()->json([
            'data' => [
                'total'       => $total,
                'by_country'  => $byCountry,
                'by_type'     => $byType,
                'by_language' => $byLanguage,
            ],
        ]);
    }
}
