@extends('layouts.app')

@section('title', $routine->name)

@section('content')
@if (session('ok'))
    <script>document.addEventListener('DOMContentLoaded', () => App.toast(@json(session('ok')), 'ok'));</script>
@endif
@if (session('warn'))
    <script>document.addEventListener('DOMContentLoaded', () => App.toast(@json(session('warn')), 'warn'));</script>
@endif

<div class="mb-3">
    <a href="{{ route('routines.index') }}" class="card-subtle"><i class="fa-solid fa-arrow-left me-1"></i>Rutinas</a>
</div>

<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
    <div>
        <h1 class="mb-1">{{ $routine->name }}</h1>
        <p class="card-subtle mb-0">
            {{ $routine->athlete?->name }} · Semana del {{ $routine->week_start->format('d/m/Y') }}
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div class="text-end me-2">
            <div class="card-subtle" style="font-size:.75rem">Progreso</div>
            <div class="mono" id="progressText" style="font-size:1.2rem;font-weight:600">{{ $progress }}%</div>
        </div>
        <button class="btn btn-brand" onclick="App.modal('#autofillModal').show()">
            <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Generar
        </button>
        <button class="btn btn-soft" onclick="App.modal('#addModal').show()">
            <i class="fa-solid fa-plus me-1"></i> Agregar
        </button>
    </div>
</div>

<div class="progress-track mb-4" style="height:8px;background:var(--surface-2);border-radius:999px;overflow:hidden">
    <div id="progressBar" style="height:100%;width:{{ $progress }}%;background:var(--brand-500);transition:width .2s"></div>
</div>

{{-- Week grid --}}
<div class="row g-3">
    @foreach($days as $dow => $label)
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card card-pad" style="min-height:120px">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h2 class="card-title mb-0">{{ $label }}</h2>
                    <span class="pill">{{ optional($byDay->get($dow))->count() ?? 0 }}</span>
                </div>

                @forelse(($byDay->get($dow) ?? []) as $item)
                    <div class="d-flex align-items-center gap-2 py-2"
                         style="border-bottom:1px solid var(--border)">
                        <input type="checkbox" class="form-check-input mt-0"
                               {{ $item->completed ? 'checked' : '' }}
                               onchange="toggleItem({{ $routine->id }}, {{ $item->id }}, this)">
                        <div style="flex:1">
                            <div style="{{ $item->completed ? 'text-decoration:line-through;color:var(--text-muted)' : '' }}">
                                {{ $item->exercise?->name ?? '—' }}
                            </div>
                            @if($item->prescription)
                                <div class="card-subtle mono" style="font-size:.75rem">{{ $item->prescription }}</div>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('routines.items.remove', [$routine, $item]) }}"
                              onsubmit="return confirm('Quitar este ejercicio?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-soft btn-sm text-danger" style="padding:.15rem .4rem">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="card-subtle mb-0" style="font-size:.85rem">Sin ejercicios.</p>
                @endforelse
            </div>
        </div>
    @endforeach
</div>

{{-- Autofill modal --}}
<div class="modal fade" id="autofillModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('routines.autofill', $routine) }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Generar desde recomendaciones</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="card-subtle">Se evaluarán las reglas contra los valores actuales del atleta y se repartirán los ejercicios recomendados en los días elegidos. Esto reemplaza los ejercicios actuales.</p>
          <div class="mb-3">
            <label class="form-label">Días de entrenamiento</label>
            <div class="d-flex flex-wrap gap-2">
              @foreach($days as $dow => $label)
                <label class="pill" style="cursor:pointer">
                  <input type="checkbox" name="training_days[]" value="{{ $dow }}"
                         {{ in_array($dow, [1,3,5]) ? 'checked' : '' }}> {{ $label }}
                </label>
              @endforeach
            </div>
          </div>
          <div class="mb-1">
            <label class="form-label">Ejercicios por día</label>
            <input name="per_day" type="number" min="1" max="8" value="3" class="form-control" style="max-width:120px">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-brand">Generar</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Manual add modal --}}
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('routines.items.add', $routine) }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Agregar ejercicio</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Ejercicio</label>
            <select name="exercise_id" class="form-select" required>
              @foreach($exercises as $e)
                <option value="{{ $e->id }}">{{ $e->name }}{{ $e->category ? " · {$e->category}" : '' }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Día</label>
            <select name="day_of_week" class="form-select" required>
              @foreach($days as $dow => $label)<option value="{{ $dow }}">{{ $label }}</option>@endforeach
            </select>
          </div>
          <div class="mb-1">
            <label class="form-label">Prescripción <span class="card-subtle">(opcional)</span></label>
            <input name="prescription" class="form-control" placeholder="p. ej. 4x400 m, 3x10">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-brand">Agregar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
    async function toggleItem(routineId, itemId, checkbox) {
        const url = `{{ url('routines') }}/${routineId}/items/${itemId}/toggle`;
        try {
            const data = await App.http.patch(url, {});
            document.getElementById('progressBar').style.width = data.progress + '%';
            document.getElementById('progressText').textContent = data.progress + '%';
            const label = checkbox.closest('div').querySelector('div');
            if (label) {
                label.style.textDecoration = data.completed ? 'line-through' : '';
                label.style.color = data.completed ? 'var(--text-muted)' : '';
            }
        } catch (e) {
            checkbox.checked = !checkbox.checked; // revert on failure
            App.toast('No se pudo actualizar.', 'bad');
        }
    }
</script>
@endsection
