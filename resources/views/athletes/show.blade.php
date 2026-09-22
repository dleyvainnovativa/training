@extends('layouts.app')

@section('title', $athlete->name)

@section('content')
@if (session('ok'))
    <script>document.addEventListener('DOMContentLoaded', () => App.toast(@json(session('ok')), 'ok'));</script>
@endif

<div class="mb-4">
    <a href="{{ route('athletes.index') }}" class="card-subtle"><i class="fa-solid fa-arrow-left me-1"></i>Atletas</a>
</div>

<div class="row g-3">
    {{-- Left: profile + capture --}}
    <div class="col-lg-4">
        <div class="card card-pad mb-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h2 class="card-title mb-1">{{ $athlete->name }}</h2>
                    <p class="card-subtle mb-2">
                        {{ $athlete->discipline ?: 'Sin disciplina' }}
                        @if($athlete->age()) · {{ $athlete->age() }} años @endif
                    </p>
                </div>
                @if($athlete->status === 'active')
                    <span class="pill brand">Activo</span>
                @else <span class="pill">Inactivo</span> @endif
            </div>

            {{-- Vitals / physical profile --}}
            @php
                $bmi = $athlete->bmi();
                $weight = $athlete->latestWeightKg();
                $hasVitals = $athlete->height_cm || $weight || $athlete->dominant_hand || $athlete->position;
            @endphp
            @if($hasVitals)
                <div class="d-flex flex-wrap gap-2 mt-3">
                    @if($athlete->height_cm)
                        <span class="pill"><i class="fa-solid fa-ruler-vertical me-1"></i>{{ rtrim(rtrim($athlete->height_cm, '0'), '.') }} cm</span>
                    @endif
                    @if($weight)
                        <span class="pill"><i class="fa-solid fa-weight-scale me-1"></i>{{ rtrim(rtrim((string) $weight, '0'), '.') }} kg</span>
                    @endif
                    @if($bmi)
                        <span class="pill brand"><i class="fa-solid fa-calculator me-1"></i>IMC {{ $bmi }}</span>
                    @endif
                    @if($athlete->dominantHandLabel())
                        <span class="pill"><i class="fa-solid fa-hand me-1"></i>{{ $athlete->dominantHandLabel() }}</span>
                    @endif
                    @if($athlete->position)
                        <span class="pill"><i class="fa-solid fa-location-dot me-1"></i>{{ $athlete->position }}</span>
                    @endif
                </div>
            @endif

            @if($athlete->phone || $athlete->emergency_contact)
                <div class="mt-3 pt-3" style="border-top:1px solid var(--border)">
                    @if($athlete->phone)
                        <p class="card-subtle mb-1"><i class="fa-solid fa-phone me-2"></i>{{ $athlete->phone }}</p>
                    @endif
                    @if($athlete->emergency_contact)
                        <p class="card-subtle mb-0"><i class="fa-solid fa-kit-medical me-2"></i>{{ $athlete->emergency_contact }}</p>
                    @endif
                </div>
            @endif

            @if($athlete->notes)
                <p class="card-subtle mt-3 mb-0">{{ $athlete->notes }}</p>
            @endif
        </div>

        <div class="card card-pad">
            <h2 class="card-title mb-3">Registrar medición</h2>
            @if($metrics->isEmpty())
                <p class="card-subtle mb-0">Primero crea al menos una métrica activa.</p>
            @else
            <form method="POST" action="{{ route('athletes.measurements.store', $athlete) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Métrica</label>
                    <select name="metric_id" class="form-select" required>
                        @foreach($metrics as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}{{ $m->unit ? " ({$m->unit})" : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-2">
                    <div class="col-6 mb-3">
                        <label class="form-label">Valor</label>
                        <input name="value" type="number" step="any" class="form-control" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Fecha</label>
                        <input name="measured_at" type="date" class="form-control"
                               value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nota <span class="card-subtle">(opcional)</span></label>
                    <input name="note" class="form-control">
                </div>
                <button class="btn btn-brand w-100">Guardar medición</button>
            </form>
            @endif
        </div>
    </div>

    {{-- Right: current values + chart --}}
    <div class="col-lg-8">
        <div class="card card-pad mb-3">
            <h2 class="card-title mb-3">Valores actuales</h2>
            @if($latest->isEmpty())
                <p class="card-subtle mb-0">Sin mediciones todavía. Registra la primera desde el panel de la izquierda.</p>
            @else
            <div class="row g-2">
                @foreach($latest as $row)
                    <div class="col-6 col-md-4">
                        <div class="card card-pad stat-tile" style="cursor:pointer"
                             onclick='loadHistory({{ $row->metric_id }}, @json($row->metric->name))'>
                            <span class="stat-label">{{ $row->metric->name }}</span>
                            <span class="stat-value">{{ rtrim(rtrim($row->value, '0'), '.') }}</span>
                            <span class="card-subtle" style="font-size:.75rem">
                                {{ $row->metric->unit }} · {{ $row->measured_at->format('d/m/Y') }}
                            </span>
                        </div>
                        
                    </div>
                @endforeach
                <div class="row g-3 mt-1">
  <div class="col-12">
    @include('athletes.partials.recommendations', ['athlete' => $athlete])
  </div>
</div>
            </div>
            @endif
        </div>

        <div class="card card-pad">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="card-title mb-0">Historial</h2>
                <span class="card-subtle" id="chartHint">Toca un valor para ver su evolución</span>
            </div>
            <canvas id="historyChart" height="120"></canvas>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
    const historyUrlBase = "{{ url('athletes/'.$athlete->id.'/history') }}";
    let chart = null;

    async function loadHistory(metricId, metricName) {
        document.getElementById('chartHint').textContent = metricName;
        try {
            const data = await App.http.get(`${historyUrlBase}/${metricId}`);
            renderChart(data);
        } catch (e) {
            App.toast('No se pudo cargar el historial.', 'bad');
        }
    }

    function renderChart(data) {
        const ctx = document.getElementById('historyChart');
        const labels = data.points.map(p => p.x);
        const values = data.points.map(p => p.y);
        const styles = getComputedStyle(document.documentElement);
        const brand = styles.getPropertyValue('--brand-500').trim();

        if (chart) chart.destroy();
        chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: `${data.metric.name}${data.metric.unit ? ' ('+data.metric.unit+')' : ''}`,
                    data: values,
                    borderColor: brand,
                    backgroundColor: brand + '22',
                    fill: true,
                    tension: 0.25,
                    pointRadius: 3,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: true } },
                scales: { y: { beginAtZero: false } }
            }
        });
    }

    // Auto-load the first metric if any exist (wait for App to be ready).
    // Guarded so it never throws if app.js hasn't executed yet.
    @if(!$latest->isEmpty())
        (function () {
            var load = () => loadHistory({{ $latest->first()->metric_id }}, @json($latest->first()->metric->name));
            if (window.App && window.App.ready) {
                window.App.ready(load);
            } else {
                document.addEventListener('App:ready', load, { once: true });
            }
        })();
    @endif
</script>
@endsection
