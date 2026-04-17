<?php

namespace App\Http\Controllers;

use App\Models\Auditor;
use App\Models\Cycle;
use App\Models\Faculty;
use App\Models\Prodi;
use Illuminate\Http\JsonResponse;

class PublicStatsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $cycles = Cycle::orderByDesc('year')->orderByDesc('id')
            ->withCount('standards')
            ->get(['id', 'name', 'year', 'is_active', 'is_locked']);

        return response()->json([
            'stats' => [
                'prodi'    => Prodi::count(),
                'fakultas' => Faculty::count(),
                'siklus'   => $cycles->count(),
                'auditor'  => Auditor::count(),
            ],
            'cycles' => $cycles->map(fn ($c) => [
                'id'              => $c->id,
                'name'            => $c->name,
                'year'            => $c->year,
                'is_active'       => (bool) $c->is_active,
                'is_locked'       => (bool) $c->is_locked,
                'standards_count' => $c->standards_count,
            ])->values(),
        ]);
    }
}
