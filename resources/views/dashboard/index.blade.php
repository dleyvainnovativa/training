@extends('layouts.app')

@section('title', 'Panel')

@section('content')
<div class="mb-4">
    <p class="card-subtle mb-0">Resumen del centro.</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <a href="{{ route('athletes.index') }}" class="card card-pad stat-tile text-decoration-none">
            <span class="stat-label"><i class="fa-solid fa-users me-1"></i>Atletas activos</span>
            <span class="stat-value">{{ $stats['athletes'] }}</span>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-pad stat-tile">
            <span class="stat-label"><i class="fa-solid fa-calendar-week me-1"></i>Rutinas activas</span>
            <span class="stat-value">{{ $stats['routines'] }}</span>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ route('metrics.index') }}" class="card card-pad stat-tile text-decoration-none">
            <span class="stat-label"><i class="fa-solid fa-ruler-combined me-1"></i>Métricas</span>
            <span class="stat-value">{{ $stats['metrics'] }}</span>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-pad stat-tile">
            <span class="stat-label"><i class="fa-solid fa-dumbbell me-1"></i>Ejercicios</span>
            <span class="stat-value">{{ $stats['exercises'] }}</span>
        </div>
    </div>
</div>

<div class="card card-pad">
    <h2 class="card-title mb-3">Actividad reciente</h2>
    @if($recent->isEmpty())
    <p class="card-subtle mb-0">Sin mediciones registradas todavía.</p>
    @else
    <table class="table-clean">
        <thead>
            <tr>
                <th>Atleta</th>
                <th>Métrica</th>
                <th>Valor</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recent as $m)
            <tr onclick="location.href='{{ route('athletes.show', $m->athlete_id) }}'" style="cursor:pointer">
                <td data-label="Atleta">{{ $m->athlete?->name ?? '—' }}</td>
                <td data-label="Métrica">{{ $m->metric?->name ?? '—' }}</td>
                <td data-label="Valor" class="mono">{{ rtrim(rtrim($m->value,'0'),'.') }} {{ $m->metric?->unit }}</td>
                <td data-label="Fecha" class="card-subtle">{{ $m->measured_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection