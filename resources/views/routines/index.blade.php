@extends('layouts.app')

@section('title', 'Rutinas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="card-subtle mb-0">Planes semanales por atleta. Genera desde recomendaciones o arma a mano.</p>
    <button class="btn btn-brand" onclick="App.modal('#routineModal').show()">
        <i class="fa-solid fa-plus me-1"></i> Nueva rutina
    </button>
</div>

@if (session('ok'))
    <script>document.addEventListener('DOMContentLoaded', () => App.toast(@json(session('ok')), 'ok'));</script>
@endif

<div class="card">
    <table class="table-clean">
        <thead>
            <tr><th>Rutina</th><th>Atleta</th><th>Semana</th><th>Estado</th><th>Progreso</th><th></th></tr>
        </thead>
        <tbody>
        @forelse ($routines as $r)
            <tr style="cursor:pointer" onclick="location.href='{{ route('routines.show', $r) }}'">
                <td>{{ $r->name }}</td>
                <td>{{ $r->athlete?->name ?? '—' }}</td>
                <td class="mono">{{ $r->week_start->format('d/m/Y') }}</td>
                <td>
                    @php $sc = ['draft'=>'','active'=>'brand','archived'=>''][$r->status] ?? ''; @endphp
                    @php $sl = ['draft'=>'Borrador','active'=>'Activa','archived'=>'Archivada'][$r->status] ?? $r->status; @endphp
                    <span class="pill {{ $sc }}">{{ $sl }}</span>
                </td>
                <td style="min-width:120px">
                    @php $p = $r->progress(); @endphp
                    <div class="d-flex align-items-center gap-2">
                        <div style="flex:1;height:6px;background:var(--surface-2);border-radius:999px;overflow:hidden">
                            <div style="height:100%;width:{{ $p }}%;background:var(--brand-500)"></div>
                        </div>
                        <span class="mono" style="font-size:.78rem">{{ $p }}%</span>
                    </div>
                </td>
                <td class="text-end">
                    <a href="{{ route('routines.show', $r) }}" class="btn btn-soft btn-sm">
                        Abrir <i class="fa-solid fa-chevron-right ms-1"></i>
                    </a>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center card-subtle py-4">Aún no hay rutinas. Crea la primera.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">{{ $routines->links() }}</div>

{{-- Create modal --}}
<div class="modal fade" id="routineModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('routines.store') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Nueva rutina</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Atleta</label>
            <select name="athlete_id" class="form-select" required>
              <option value="">Selecciona…</option>
              @foreach(\App\Models\Athlete::where('status','active')->orderBy('name')->get() as $a)
                <option value="{{ $a->id }}">{{ $a->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input name="name" class="form-control" required placeholder="p. ej. Semana base — resistencia">
          </div>
          <div class="mb-1">
            <label class="form-label">Semana (se ajusta al lunes)</label>
            <input name="week_start" type="date" class="form-control" value="{{ now()->startOfWeek()->format('Y-m-d') }}" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-brand">Crear</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
