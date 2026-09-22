<?php

namespace App\Http\Controllers;

use App\Models\Metric;
use Illuminate\Http\Request;

class MetricController extends Controller
{
    public function index()
    {
        $metrics = Metric::orderBy('category')->orderBy('name')->get();
        return view('metrics.index', compact('metrics'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Metric::create($data);
        return back()->with('ok', 'Métrica creada.');
    }

    public function update(Request $request, Metric $metric)
    {
        $data = $this->validated($request);
        $metric->update($data);
        return back()->with('ok', 'Métrica actualizada.');
    }

    public function destroy(Metric $metric)
    {
        // Measurements cascade-delete via FK. Warn is handled in the UI.
        $metric->delete();
        return back()->with('ok', 'Métrica eliminada.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'      => ['required', 'string', 'max:120'],
            'unit'      => ['nullable', 'string', 'max:40'],
            'category'  => ['nullable', 'string', 'max:60'],
            'reference' => ['nullable', 'string', 'max:1000'],
            'direction' => ['required', 'in:higher,lower'],
            'min_range' => ['nullable', 'numeric'],
            'max_range' => ['nullable', 'numeric', 'gte:min_range'],
            'active'    => ['sometimes', 'boolean'],
        ], [
            'max_range.gte' => 'El máximo debe ser mayor o igual al mínimo.',
        ]);
    }
}
