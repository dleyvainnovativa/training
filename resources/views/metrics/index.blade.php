@extends('layouts.app')

@section('title', 'Métricas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="card-subtle mb-0">Define las mediciones que registras: unidad, estándar de referencia y dirección deseada.</p>
    <button class="btn btn-brand" onclick="openMetric()">
        <i class="fa-solid fa-plus me-1"></i> Nueva métrica
    </button>
</div>

@if (session('ok'))
    <script>document.addEventListener('DOMContentLoaded', () => App.toast(@json(session('ok')), 'ok'));</script>
@endif

<div class="card">
    <table class="table-clean">
        <thead>
            <tr>
                <th>Nombre</th><th>Unidad</th><th>Categoría</th>
                <th>Dirección</th><th>Rango</th><th></th>
            </tr>
        </thead>
        <tbody>
        @forelse ($metrics as $m)
            <tr>
                <td>
                    {{ $m->name }}
                    @unless($m->active)<span class="pill ms-1">Inactiva</span>@endunless
                </td>
                <td class="mono">{{ $m->unit ?: '—' }}</td>
                <td>{{ $m->category ?: '—' }}</td>
                <td>
                    @if($m->higherIsBetter())
                        <span class="pill brand"><i class="fa-solid fa-arrow-trend-up"></i> Mayor mejor</span>
                    @else
                        <span class="pill"><i class="fa-solid fa-arrow-trend-down"></i> Menor mejor</span>
                    @endif
                </td>
                <td class="mono">
                    {{ $m->min_range !== null ? rtrim(rtrim($m->min_range,'0'),'.') : '–' }}
                    …
                    {{ $m->max_range !== null ? rtrim(rtrim($m->max_range,'0'),'.') : '–' }}
                </td>
                <td class="text-end">
                    <button class="btn btn-soft btn-sm" onclick='editMetric(@json($m))'>
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    <form method="POST" action="{{ route('metrics.destroy', $m) }}" class="d-inline"
                          onsubmit="return confirm('Eliminar la métrica y todas sus mediciones asociadas?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-soft btn-sm text-danger"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center card-subtle py-4">
                Aún no hay métricas. Crea la primera para empezar a registrar datos.
            </td></tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- Create/edit modal --}}
<div class="modal fade" id="metricModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="metricForm" method="POST">
        @csrf
        <input type="hidden" name="_method" id="metricMethod" value="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="metricModalTitle">Nueva métrica</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input name="name" id="m_name" class="form-control" required placeholder="p. ej. VO2 máx">
          </div>
          <div class="row g-2">
            <div class="col-6 mb-3">
              <label class="form-label">Unidad</label>
              <input name="unit" id="m_unit" class="form-control" placeholder="ml/kg/min">
            </div>
            <div class="col-6 mb-3">
              <label class="form-label">Categoría</label>
              <input name="category" id="m_category" class="form-control" placeholder="Resistencia">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Dirección deseada</label>
            <select name="direction" id="m_direction" class="form-select">
              <option value="higher">Mayor es mejor</option>
              <option value="lower">Menor es mejor</option>
            </select>
          </div>
          <div class="row g-2">
            <div class="col-6 mb-3">
              <label class="form-label">Rango mín. <span class="card-subtle">(opcional)</span></label>
              <input name="min_range" id="m_min" type="number" step="any" class="form-control">
            </div>
            <div class="col-6 mb-3">
              <label class="form-label">Rango máx. <span class="card-subtle">(opcional)</span></label>
              <input name="max_range" id="m_max" type="number" step="any" class="form-control">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Referencia / estándar <span class="card-subtle">(opcional)</span></label>
            <textarea name="reference" id="m_reference" class="form-control" rows="2"
                      placeholder="Notas sobre el estándar o cómo se mide."></textarea>
          </div>
          <div class="form-check">
            <input type="checkbox" name="active" id="m_active" class="form-check-input" value="1" checked>
            <label class="form-check-label" for="m_active">Activa</label>
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
    const metricModalEl = document.getElementById('metricModal');
    const metricForm = document.getElementById('metricForm');
    const storeUrl = "{{ route('metrics.store') }}";
    const updateUrlBase = "{{ url('metrics') }}";

    function openMetric() {
        metricForm.reset();
        document.getElementById('metricModalTitle').textContent = 'Nueva métrica';
        document.getElementById('metricMethod').value = 'POST';
        metricForm.action = storeUrl;
        document.getElementById('m_active').checked = true;
        App.modal(metricModalEl).show();
    }

    function editMetric(m) {
        document.getElementById('metricModalTitle').textContent = 'Editar métrica';
        document.getElementById('metricMethod').value = 'PUT';
        metricForm.action = `${updateUrlBase}/${m.id}`;
        document.getElementById('m_name').value = m.name ?? '';
        document.getElementById('m_unit').value = m.unit ?? '';
        document.getElementById('m_category').value = m.category ?? '';
        document.getElementById('m_direction').value = m.direction ?? 'higher';
        document.getElementById('m_min').value = m.min_range ?? '';
        document.getElementById('m_max').value = m.max_range ?? '';
        document.getElementById('m_reference').value = m.reference ?? '';
        document.getElementById('m_active').checked = !!m.active;
        App.modal(metricModalEl).show();
    }
</script>
@endsection
