@extends('layouts.app')

@section('title', 'Ejercicios')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 gap-3 flex-wrap">
    <form method="GET" class="d-flex gap-2 flex-wrap" style="flex:1">
        <input name="q" value="{{ $q }}" class="form-control" style="max-width:240px"
               placeholder="Buscar ejercicio">
        <select name="category" class="form-select" style="max-width:180px" onchange="this.form.submit()">
            <option value="">Todas las categorías</option>
            @foreach($categories as $c)
                <option value="{{ $c }}" @selected($category === $c)>{{ $c }}</option>
            @endforeach
        </select>
        <button class="btn btn-soft"><i class="fa-solid fa-magnifying-glass"></i></button>
    </form>
    <button class="btn btn-brand" onclick="openExercise()">
        <i class="fa-solid fa-plus me-1"></i> Nuevo ejercicio
    </button>
</div>

@if (session('ok'))
    <script>document.addEventListener('DOMContentLoaded', () => App.toast(@json(session('ok')), 'ok'));</script>
@endif

<div class="card">
    <table class="table-clean">
        <thead>
            <tr><th>Nombre</th><th>Categoría</th><th>Intensidad</th><th>Equipo</th><th>Etiquetas</th><th></th></tr>
        </thead>
        <tbody>
        @forelse ($exercises as $e)
            <tr>
                <td data-label="Nombre">
                    {{ $e->name }}
                    @unless($e->active)<span class="pill ms-1">Inactivo</span>@endunless
                </td>
                <td data-label="Categoría">@if($e->category)<span class="pill brand">{{ $e->category }}</span>@else — @endif</td>
                <td data-label="Intensidad">
                    @php $ic = ['low'=>'','medium'=>'brand','high'=>''][$e->intensity] ?? ''; @endphp
                    <span class="pill {{ $ic }}">{{ $e->intensityLabel() }}</span>
                </td>
                <td data-label="Equipo">{{ $e->equipment ?: '—' }}</td>
                <td data-label="Etiquetas">
                    @foreach(($e->tags ?? []) as $t)
                        <span class="pill">{{ $t }}</span>
                    @endforeach
                    @if(empty($e->tags)) — @endif
                </td>
                <td data-label="" class="text-end" style="white-space:nowrap">
                    <button class="btn btn-soft btn-sm" onclick='editExercise(@json($e))'>
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    <form method="POST" action="{{ route('exercises.destroy', $e) }}" class="d-inline"
                          onsubmit="return confirm('Eliminar este ejercicio?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-soft btn-sm text-danger"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center card-subtle py-4">
                @if($q || $category) Sin resultados con esos filtros. @else Aún no hay ejercicios. Crea el primero. @endif
            </td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">{{ $exercises->links() }}</div>

{{-- Create/edit modal --}}
<div class="modal fade" id="exerciseModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="exerciseForm" method="POST">
        @csrf
        <input type="hidden" name="_method" id="ex_method" value="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="ex_title">Nuevo ejercicio</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input name="name" id="ex_name" class="form-control" required>
          </div>
          <div class="row g-2">
            <div class="col-6 mb-3">
              <label class="form-label">Categoría</label>
              <select name="category" id="ex_category" class="form-select">
                <option value="">—</option>
                @foreach($categories as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach
              </select>
            </div>
            <div class="col-6 mb-3">
              <label class="form-label">Intensidad</label>
              <select name="intensity" id="ex_intensity" class="form-select">
                <option value="low">Baja</option>
                <option value="medium" selected>Media</option>
                <option value="high">Alta</option>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Equipo <span class="card-subtle">(opcional)</span></label>
            <input name="equipment" id="ex_equipment" class="form-control" placeholder="p. ej. Mancuernas, Cinta">
          </div>
          <div class="mb-3">
            <label class="form-label">Etiquetas <span class="card-subtle">(separadas por coma)</span></label>
            <input name="tags" id="ex_tags" class="form-control" placeholder="tren inferior, cuádriceps, explosivo">
          </div>
          <div class="mb-3">
            <label class="form-label">Descripción <span class="card-subtle">(opcional)</span></label>
            <textarea name="description" id="ex_description" class="form-control" rows="2"></textarea>
          </div>
          <div class="form-check">
            <input type="checkbox" name="active" id="ex_active" class="form-check-input" value="1" checked>
            <label class="form-check-label" for="ex_active">Activo</label>
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
    const exModalEl = document.getElementById('exerciseModal');
    const exForm = document.getElementById('exerciseForm');
    const exStoreUrl = "{{ route('exercises.store') }}";
    const exUpdateBase = "{{ url('exercises') }}";

    function openExercise() {
        exForm.reset();
        document.getElementById('ex_title').textContent = 'Nuevo ejercicio';
        document.getElementById('ex_method').value = 'POST';
        exForm.action = exStoreUrl;
        document.getElementById('ex_intensity').value = 'medium';
        document.getElementById('ex_active').checked = true;
        App.modal(exModalEl).show();
    }

    function editExercise(e) {
        document.getElementById('ex_title').textContent = 'Editar ejercicio';
        document.getElementById('ex_method').value = 'PUT';
        exForm.action = `${exUpdateBase}/${e.id}`;
        document.getElementById('ex_name').value = e.name ?? '';
        document.getElementById('ex_category').value = e.category ?? '';
        document.getElementById('ex_intensity').value = e.intensity ?? 'medium';
        document.getElementById('ex_equipment').value = e.equipment ?? '';
        document.getElementById('ex_tags').value = (e.tags ?? []).join(', ');
        document.getElementById('ex_description').value = e.description ?? '';
        document.getElementById('ex_active').checked = !!e.active;
        App.modal(exModalEl).show();
    }
</script>
@endsection
