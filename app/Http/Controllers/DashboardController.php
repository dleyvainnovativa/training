<?php

namespace App\Http\Controllers;

use App\Models\Athlete;
use App\Models\Measurement;
use App\Models\Metric;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'athletes'  => Athlete::where('status', 'active')->count(),
            'routines'  => 0, // wired in Tier 5
            'metrics'   => Metric::where('active', true)->count(),
            'exercises' => 0, // wired in Tier 3
        ];

        $recent = Measurement::with(['athlete', 'metric'])
            ->latest('measured_at')
            ->limit(8)
            ->get();

        return view('dashboard.index', [
            'user'   => $request->user(),
            'stats'  => $stats,
            'recent' => $recent,
        ]);
    }
}
