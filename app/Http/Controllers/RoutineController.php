<?php

namespace App\Http\Controllers;

use App\Models\Athlete;
use App\Models\Exercise;
use App\Models\Routine;
use App\Models\RoutineItem;
use App\Services\RoutineBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RoutineController extends Controller
{
    public function __construct(
        private readonly RoutineBuilder $builder
    ) {}

    public function index()
    {
        $routines = Routine::with('athlete')
            ->orderByDesc('week_start')
            ->paginate(20);

        return view('routines.index', compact('routines'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'athlete_id' => ['required', 'exists:athletes,id'],
            'name'       => ['required', 'string', 'max:150'],
            'week_start' => ['required', 'date'],
        ]);

        // Normalize week_start to the Monday of that week.
        $data['week_start'] = Carbon::parse($data['week_start'])->startOfWeek()->toDateString();
        $data['status'] = 'draft';

        $routine = Routine::create($data);
        return redirect()->route('routines.show', $routine)->with('ok', 'Rutina creada.');
    }

    public function show(Routine $routine)
    {
        $routine->load('athlete');
        return view('routines.show', [
            'routine'   => $routine,
            'byDay'     => $routine->itemsByDay(),
            'days'      => Routine::DAYS,
            'exercises' => Exercise::where('active', true)->orderBy('name')->get(),
            'progress'  => $routine->progress(),
        ]);
    }

    /** Auto-fill from the recommendation engine. Replaces existing items. */
    public function autofill(Request $request, Routine $routine)
    {
        $data = $request->validate([
            'training_days'   => ['required', 'array', 'min:1'],
            'training_days.*' => ['integer', 'between:1,7'],
            'per_day'         => ['required', 'integer', 'between:1,8'],
        ]);

        $plan = $this->builder->buildForAthlete(
            $routine->athlete,
            $data['training_days'],
            $data['per_day']
        );

        if (empty($plan)) {
            return back()->with('warn', 'No hay recomendaciones para generar la rutina. Revisa reglas y mediciones.');
        }

        // Replace current items with the generated plan.
        $routine->items()->delete();
        foreach ($plan as $day => $items) {
            foreach ($items as $item) {
                $routine->items()->create([
                    'exercise_id' => $item['exercise_id'],
                    'day_of_week' => $day,
                    'position'    => $item['position'],
                    'prescription' => $item['prescription'] ?? null,
                ]);
            }
        }

        return back()->with('ok', 'Rutina generada desde las recomendaciones.');
    }

    /** Manually add a single exercise to a day. */
    public function addItem(Request $request, Routine $routine)
    {
        $data = $request->validate([
            'exercise_id' => ['required', 'exists:exercises,id'],
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'prescription' => ['nullable', 'string', 'max:120'],
        ]);

        $position = $routine->items()->where('day_of_week', $data['day_of_week'])->count();

        $routine->items()->create([
            'exercise_id'  => $data['exercise_id'],
            'day_of_week'  => $data['day_of_week'],
            'position'     => $position,
            'prescription' => $data['prescription'] ?? null,
        ]);

        return back()->with('ok', 'Ejercicio agregado.');
    }

    public function removeItem(Routine $routine, RoutineItem $item)
    {
        abort_unless($item->routine_id === $routine->id, 404);
        $item->delete();
        return back()->with('ok', 'Ejercicio quitado.');
    }

    /** Toggle completion for one item. Returns JSON for inline update. */
    public function toggleItem(Routine $routine, RoutineItem $item)
    {
        abort_unless($item->routine_id === $routine->id, 404);

        $item->completed = ! $item->completed;
        $item->completed_at = $item->completed ? now() : null;
        $item->save();

        return response()->json([
            'completed' => $item->completed,
            'progress'  => $routine->progress(),
        ]);
    }

    public function updateStatus(Request $request, Routine $routine)
    {
        $data = $request->validate([
            'status' => ['required', 'in:draft,active,archived'],
        ]);
        $routine->update($data);
        return back()->with('ok', 'Estado actualizado.');
    }

    public function destroy(Routine $routine)
    {
        $routine->delete();
        return redirect()->route('routines.index')->with('ok', 'Rutina eliminada.');
    }
}
