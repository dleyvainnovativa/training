<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    public function index(Request $request)
    {
        $q        = trim($request->get('q', ''));
        $category = $request->get('category', '');

        $exercises = Exercise::query()
            ->when($q !== '', fn ($query) => $query->where('name', 'like', "%{$q}%"))
            ->when($category !== '', fn ($query) => $query->where('category', $category))
            ->orderBy('category')->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('exercises.index', [
            'exercises'  => $exercises,
            'q'          => $q,
            'category'   => $category,
            'categories' => Exercise::CATEGORIES,
        ]);
    }

    public function store(Request $request)
    {
        Exercise::create($this->validated($request));
        return back()->with('ok', 'Ejercicio creado.');
    }

    public function update(Request $request, Exercise $exercise)
    {
        $exercise->update($this->validated($request));
        return back()->with('ok', 'Ejercicio actualizado.');
    }

    public function destroy(Exercise $exercise)
    {
        $exercise->delete();
        return back()->with('ok', 'Ejercicio eliminado.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:150'],
            'category'    => ['nullable', 'in:' . implode(',', Exercise::CATEGORIES)],
            'tags'        => ['nullable', 'string', 'max:500'],
            'intensity'   => ['required', 'in:low,medium,high'],
            'equipment'   => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'active'      => ['sometimes', 'boolean'],
        ]);

        // Tags arrive as a comma-separated string; normalize to a clean array.
        $data['tags'] = $this->parseTags($request->get('tags', ''));
        $data['active'] = $request->boolean('active');

        return $data;
    }

    private function parseTags(string $raw): array
    {
        return collect(explode(',', $raw))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
