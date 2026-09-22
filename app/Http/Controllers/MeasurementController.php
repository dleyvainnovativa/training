<?php

namespace App\Http\Controllers;

use App\Models\Athlete;
use App\Models\Measurement;
use App\Models\Metric;
use Illuminate\Http\Request;

class MeasurementController extends Controller
{
    public function store(Request $request, Athlete $athlete)
    {
        $data = $request->validate([
            'metric_id'   => ['required', 'exists:metrics,id'],
            'value'       => ['required', 'numeric'],
            'measured_at' => ['required', 'date', 'before_or_equal:today'],
            'note'        => ['nullable', 'string', 'max:500'],
        ]);

        $athlete->measurements()->create($data);

        return back()->with('ok', 'Medición registrada.');
    }

    public function destroy(Athlete $athlete, Measurement $measurement)
    {
        abort_unless($measurement->athlete_id === $athlete->id, 404);
        $measurement->delete();
        return back()->with('ok', 'Medición eliminada.');
    }

    /**
     * History for one athlete+metric as JSON, for the chart.
     * Returns points oldest→newest plus metric meta.
     */
    public function history(Athlete $athlete, Metric $metric)
    {
        $points = $athlete->measurements()
            ->where('metric_id', $metric->id)
            ->orderBy('measured_at')
            ->get(['value', 'measured_at']);

        return response()->json([
            'metric' => [
                'name'      => $metric->name,
                'unit'      => $metric->unit,
                'direction' => $metric->direction,
            ],
            'points' => $points->map(fn ($m) => [
                'x' => $m->measured_at->format('Y-m-d'),
                'y' => (float) $m->value,
            ]),
        ]);
    }
}
