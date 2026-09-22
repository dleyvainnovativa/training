<aside class="sidebar">
    <div class="sidebar-brand">
        <span class="brand-mark"><i class="fa-solid fa-heart-pulse"></i></span>
        <span>Centro Deportivo</span>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">General</div>
        <a href="{{ route('dashboard') }}"
           class="nav-link-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fa-solid fa-gauge-high"></i> Panel
        </a>

        <div class="nav-section-label">Gestión</div>
        <a href="{{ route('athletes.index') }}"
           class="nav-link-item {{ request()->routeIs('athletes.*') ? 'active' : '' }}">
            <i class="fa-solid fa-users"></i> Atletas
        </a>
        <a href="{{ route('metrics.index') }}"
           class="nav-link-item {{ request()->routeIs('metrics.*') ? 'active' : '' }}">
            <i class="fa-solid fa-ruler-combined"></i> Métricas
        </a>
        <a href="{{ route('exercises.index') }}"
           class="nav-link-item {{ request()->routeIs('exercises.*') ? 'active' : '' }}">
            <i class="fa-solid fa-dumbbell"></i> Ejercicios
        </a>
        <a href="{{ route('rules.index') }}"
           class="nav-link-item {{ request()->routeIs('rules.*') ? 'active' : '' }}">
            <i class="fa-solid fa-sliders"></i> Reglas
        </a>
        <a href="{{ route('routines.index') }}"
           class="nav-link-item {{ request()->routeIs('routines.*') ? 'active' : '' }}">
            <i class="fa-solid fa-calendar-week"></i> Rutinas
        </a>
    </nav>
</aside>
