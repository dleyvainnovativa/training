{{--
    Tier 4: Recommendations panel.
    Add this include to resources/views/athletes/show.blade.php, e.g. as a new
    full-width row below the existing history card:

        <div class="row g-3 mt-1">
          <div class="col-12">
            @include('athletes.partials.recommendations', ['athlete' => $athlete])
          </div>
        </div>
--}}
<div class="card card-pad">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="card-title mb-0">Ejercicios recomendados</h2>
        <button class="btn btn-soft btn-sm" id="recBtn" onclick="loadRecommendations()">
            <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Calcular
        </button>
    </div>

    <div id="recEmpty" class="card-subtle">
        Pulsa «Calcular» para evaluar las reglas contra los valores actuales del atleta.
    </div>

    <div id="recTriggered" class="mb-3" style="display:none">
        <div class="card-subtle mb-2">Reglas activadas:</div>
        <div id="recTriggeredList" class="d-flex flex-wrap gap-2"></div>
    </div>

    <div id="recResults" style="display:none">
        <table class="table-clean">
            <thead><tr><th>Ejercicio</th><th>Categoría</th><th>Intensidad</th><th>Puntaje</th><th>Por qué</th></tr></thead>
            <tbody id="recBody"></tbody>
        </table>
    </div>

    <div id="recNone" class="card-subtle" style="display:none">
        Ninguna regla se activó con los valores actuales. No hay recomendaciones.
    </div>
</div>

<script>
    const recUrl = "{{ url('athletes/'.$athlete->id.'/recommendations') }}";

    async function loadRecommendations() {
        try {
            const data = await App.withLoading('#recBtn', () => App.http.get(recUrl));
            renderRecommendations(data);
        } catch (e) {
            App.toast('No se pudieron calcular las recomendaciones.', 'bad');
        }
    }

    function renderRecommendations(data) {
        document.getElementById('recEmpty').style.display = 'none';
        const trig = data.triggered || [];
        const recs = data.recommendations || [];

        // Triggered rules as pills.
        const tWrap = document.getElementById('recTriggered');
        const tList = document.getElementById('recTriggeredList');
        if (trig.length) {
            tList.innerHTML = trig.map(t =>
                `<span class="pill brand">${t.rule_name}: ${t.reason} (peso ${t.weight})</span>`
            ).join('');
            tWrap.style.display = '';
        } else {
            tWrap.style.display = 'none';
        }

        const results = document.getElementById('recResults');
        const none = document.getElementById('recNone');

        if (!recs.length) {
            results.style.display = 'none';
            none.style.display = '';
            return;
        }
        none.style.display = 'none';
        results.style.display = '';

        const intensityMap = { low: 'Baja', medium: 'Media', high: 'Alta' };
        document.getElementById('recBody').innerHTML = recs.map(r => `
            <tr>
                <td>${r.name}</td>
                <td><span class="pill brand">${r.category}</span></td>
                <td>${intensityMap[r.intensity] || r.intensity}</td>
                <td class="mono">${(+r.score).toFixed(1)}</td>
                <td class="card-subtle" style="font-size:.82rem">${(r.reasons || []).join('; ')}</td>
            </tr>
        `).join('');
    }
</script>
