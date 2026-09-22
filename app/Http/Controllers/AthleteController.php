<?php

namespace App\Http\Controllers;

use App\Models\Athlete;
use App\Models\Metric;
use Illuminate\Http\Request;

class AthleteController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));
        $athletes = Athlete::query()
            ->when($q !== '', fn ($query) =>
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('discipline', 'like', "%{$q}%"))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('athletes.index', compact('athletes', 'q'));
    }

    public function store(Request $request)
    {
        Athlete::create($this->validated($request));
        return back()->with('ok', 'Atleta registrado.');
    }

    public function show(Athlete $athlete)
    {
        $metrics = Metric::where('active', true)->orderBy('name')->get();
        $latest  = $athlete->latestByMetric();

        return view('athletes.show', compact('athlete', 'metrics', 'latest'));
    }

    public function update(Request $request, Athlete $athlete)
    {
        $athlete->update($this->validated($request));
        return back()->with('ok', 'Atleta actualizado.');
    }

    public function destroy(Athlete $athlete)
    {
        $athlete->delete();
        return redirect()->route('athletes.index')->with('ok', 'Atleta eliminado.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'       => ['required', 'string', 'max:150'],
            'birthdate'  => ['nullable', 'date', 'before:today'],
            'sex'        => ['nullable', 'in:M,F,O'],
            'discipline' => ['nullable', 'string', 'max:100'],
            'status'     => ['required', 'in:active,inactive'],
            'notes'      => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
