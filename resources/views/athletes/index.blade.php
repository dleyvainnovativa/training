@extends('layouts.app')

@section('title', 'Atletas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 gap-3 flex-wrap">
    <form method="GET" class="d-flex gap-2" style="max-width:320px;flex:1">
        <input name="q" value="{{ $q }}" class="form-control" placeholder="Buscar por nombre o disciplina">
        <button class="btn btn-soft"><i class="fa-solid fa-magnifying-glass"></i></button>
    </form>
    <button class="btn btn-brand" onclick="openAthlete()">
        <i class="fa-solid fa-plus me-1"></i> Nuevo atleta
    </button>
</div>

@if (session('ok'))
    <script>document.addEventListener('DOMContentLoaded', () => App.toast(@json(session('ok')), 'ok'));</script>
@endif

<div class="card">
    <table class="table-clean">
        <thead>
            <tr><th>Nombre</th><th>Disciplina</th><th>Edad</th><th>Estado</th><th></th></tr>
        </thead>
        <tbody>
        @forelse ($athletes as $a)
            <tr style="cursor:pointer" onclick="location.href='{{ route('athletes.show', $a) }}'">
                <td data-label="Nombre">{{ $a->name }}</td>
                <td data-label="Disciplina">{{ $a->discipline ?: '—' }}</td>
                <td data-label="Edad" class="mono">{{ $a->age() ?? '—' }}</td>
                <td data-label="Estado">
                    @if($a->status === 'active')
                        <span class="pill brand">Activo</span>
                    @else
                        <span class="pill">Inactivo</span>
                    @endif
                </td>
                <td data-label="" class="text-end">
                    <a href="{{ route('athletes.show', $a) }}" class="btn btn-soft btn-sm">
                        Ver <i class="fa-solid fa-chevron-right ms-1"></i>
                    </a>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center card-subtle py-4">
                @if($q) Sin resultados para "{{ $q }}". @else Aún no hay atletas. Registra el primero. @endif
            </td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">{{ $athletes->links() }}</div>

{{-- Create modal --}}
<div class="modal fade" id="athleteModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('athletes.store') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Nuevo atleta</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input name="name" class="form-control" required>
          </div>
          <div class="row g-2">
            <div class="col-6 mb-3">
              <label class="form-label">Fecha de nacimiento</label>
              <input name="birthdate" type="date" class="form-control">
            </div>
            <div class="col-6 mb-3">
              <label class="form-label">Sexo</label>
              <select name="sex" class="form-select">
                <option value="">—</option>
                <option value="M">Masculino</option>
                <option value="F">Femenino</option>
                <option value="O">Otro</option>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Disciplina</label>
            <input name="discipline" class="form-control" placeholder="p. ej. Atletismo, Natación">
          </div>
          <div class="mb-3">
            <label class="form-label">Estado</label>
            <select name="status" class="form-select">
              <option value="active">Activo</option>
              <option value="inactive">Inactivo</option>
            </select>
          </div>
          <div class="mb-1">
            <label class="form-label">Notas</label>
            <textarea name="notes" class="form-control" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-brand">Registrar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
    function openAthlete() { App.modal('#athleteModal').show(); }
</script>
@endsection
