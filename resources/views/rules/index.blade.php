@extends('layouts.app')

@section('title', 'Reglas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 gap-3 flex-wrap">
    <p class="card-subtle mb-0" style="max-width:640px">
        Define cuándo recomendar ejercicios. Cada regla evalúa el valor más reciente de una métrica
        y, si se cumple la condición, suma peso a una categoría de ejercicios.
    </p>
    <button class="btn btn-brand" onclick="openRule()"><i class="fa-solid fa-plus me-1"></i> Nueva regla</button>
</div>

@if (session('ok'))
    <script>document.addEventListener('DOMContentLoaded', () => App.toast(@json(session('ok')), 'ok'));</script>
@endif

@if ($metrics->isEmpty())
    <div class="card card-pad"><p class="card-subtle mb-0">Crea métricas activas antes de definir reglas.</p></div>
@else
<div class="card">
    <table class="table-clean">
        <thead>
            <tr><th>Regla</th><th>Condición</th><th>Recomienda</th><th>Intensidad</th><th>Peso</th><th></th></tr>
        </thead>
        <tbody>
        @forelse ($rules as $r)
            <tr>
                <td>{{ $r->name }} @unless($r->active)<span class="pill ms-1">Inactiva</span>@endunless</td>
                <td>{{ $r->describe() }}</td>
                <td><span class="pill brand">{{ $r->recommend_category }}</span></td>
                <td>{{ $r->recommend_intensity ? \App\Models\Exercise::INTENSITIES[$r->recommend_intensity] : '—' }}</td>
                <td class="mono">{{ $r->weight }}</td>
                <td class="text-end" style="white-space:nowrap">
                    <button class="btn btn-soft btn-sm" onclick='editRule(@json($r))'><i class="fa-solid fa-pen"></i></button>
                    <form method="POST" action="{{ route('rules.destroy', $r) }}" class="d-inline"
                          onsubmit="return confirm('Eliminar esta regla?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-soft btn-sm text-danger"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center card-subtle py-4">Aún no hay reglas. Crea la primera.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endif

{{-- Builder modal --}}
<div class="modal fade" id="ruleModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="ruleForm" method="POST">
        @csrf
        <input type="hidden" name="_method" id="r_method" value="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="r_title">Nueva regla</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nombre de la regla</label>
            <input name="name" id="r_name" class="form-control" required placeholder="p. ej. VO2 bajo → resistencia">
          </div>
          <div class="mb-3">
            <label class="form-label">Métrica</label>
            <select name="metric_id" id="r_metric" class="form-select" required>
              @foreach($metrics as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach
            </select>
          </div>
          <div class="row g-2">
            <div class="col-7 mb-3">
              <label class="form-label">Condición</label>
              <select name="condition" id="r_condition" class="form-select" onchange="toggleThreshold()">
                @foreach($conditions as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach
              </select>
            </div>
            <div class="col-5 mb-3" id="r_threshold_wrap">
              <label class="form-label">Umbral</label>
              <input name="threshold" id="r_threshold" type="number" step="any" class="form-control">
            </div>
          </div>
          <hr>
          <div class="row g-2">
            <div class="col-6 mb-3">
              <label class="form-label">Recomendar categoría</label>
              <select name="recommend_category" id="r_category" class="form-select" required>
                @foreach($categories as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach
              </select>
            </div>
            <div class="col-6 mb-3">
              <label class="form-label">Intensidad preferida</label>
              <select name="recommend_intensity" id="r_intensity" class="form-select">
                <option value="">Cualquiera</option>
                <option value="low">Baja</option>
                <option value="medium">Media</option>
                <option value="high">Alta</option>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Peso (1–10) <span class="card-subtle">— cuánto pesa esta regla al ordenar</span></label>
            <input name="weight" id="r_weight" type="number" min="1" max="10" value="1" class="form-control" required>
          </div>
          <div class="form-check">
            <input type="checkbox" name="active" id="r_active" class="form-check-input" value="1" checked>
            <label class="form-check-label" for="r_active">Activa</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-brand">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
    const ruleModalEl = document.getElementById('ruleModal');
    const ruleForm = document.getElementById('ruleForm');
    const ruleStoreUrl = "{{ route('rules.store') }}";
    const ruleUpdateBase = "{{ url('rules') }}";

    function toggleThreshold() {
        const c = document.getElementById('r_condition').value;
        const needs = (c === 'below' || c === 'above');
        document.getElementById('r_threshold_wrap').style.display = needs ? '' : 'none';
        document.getElementById('r_threshold').required = needs;
    }

    function openRule() {
        ruleForm.reset();
        document.getElementById('r_title').textContent = 'Nueva regla';
        document.getElementById('r_method').value = 'POST';
        ruleForm.action = ruleStoreUrl;
        document.getElementById('r_weight').value = 1;
        document.getElementById('r_active').checked = true;
        toggleThreshold();
        App.modal(ruleModalEl).show();
    }

    function editRule(r) {
        document.getElementById('r_title').textContent = 'Editar regla';
        document.getElementById('r_method').value = 'PUT';
        ruleForm.action = `${ruleUpdateBase}/${r.id}`;
        document.getElementById('r_name').value = r.name ?? '';
        document.getElementById('r_metric').value = r.metric_id ?? '';
        document.getElementById('r_condition').value = r.condition ?? 'below';
        document.getElementById('r_threshold').value = r.threshold ?? '';
        document.getElementById('r_category').value = r.recommend_category ?? '';
        document.getElementById('r_intensity').value = r.recommend_intensity ?? '';
        document.getElementById('r_weight').value = r.weight ?? 1;
        document.getElementById('r_active').checked = !!r.active;
        toggleThreshold();
        App.modal(ruleModalEl).show();
    }

    document.addEventListener('DOMContentLoaded', toggleThreshold);
</script>
@endsection
