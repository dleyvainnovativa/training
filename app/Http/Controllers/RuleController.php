<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\Metric;
use App\Models\Rule;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule as ValidationRule;

class RuleController extends Controller
{
    public function index()
    {
        $rules = Rule::with('metric')->orderBy('name')->get();
        return view('rules.index', [
            'rules'      => $rules,
            'metrics'    => Metric::where('active', true)->orderBy('name')->get(),
            'categories' => Exercise::CATEGORIES,
            'conditions' => Rule::CONDITIONS,
        ]);
    }

    public function store(Request $request)
    {
        Rule::create($this->validated($request));
        return back()->with('ok', 'Regla creada.');
    }

    public function update(Request $request, Rule $rule)
    {
        $rule->update($this->validated($request));
        return back()->with('ok', 'Regla actualizada.');
    }

    public function destroy(Rule $rule)
    {
        $rule->delete();
        return back()->with('ok', 'Regla eliminada.');
    }

    private function validated(Request $request): array
    {
        // Threshold is required only for the below/above conditions.
        $needsThreshold = in_array($request->input('condition'), ['below', 'above'], true);

        $data = $request->validate([
            'name'                => ['required', 'string', 'max:150'],
            'metric_id'           => ['required', 'exists:metrics,id'],
            'condition'           => ['required', ValidationRule::in(array_keys(Rule::CONDITIONS))],
            'threshold'           => [$needsThreshold ? 'required' : 'nullable', 'numeric'],
            'recommend_category'  => ['required', ValidationRule::in(Exercise::CATEGORIES)],
            'recommend_intensity' => ['nullable', 'in:low,medium,high'],
            'weight'              => ['required', 'integer', 'min:1', 'max:10'],
            'active'              => ['sometimes', 'boolean'],
        ], [
            'threshold.required' => 'Esta condición requiere un umbral.',
        ]);

        $data['active'] = $request->boolean('active');
        return $data;
    }
}
