<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Apply saved theme before first paint to avoid a light→dark flash. --}}
    <script>
        (function () {
            try {
                var t = localStorage.getItem('theme');
                if (t === 'dark' || t === 'light') {
                    document.documentElement.setAttribute('data-theme', t);
                }
            } catch (e) {}
        })();
    </script>
    <title>@yield('title', 'Panel') · Centro Deportivo</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite(['resources/css/theme.css', 'resources/js/app.js'])
</head>
<body>
<div class="app-shell">
    @include('layouts.partials.sidebar')
    <div class="sidebar-backdrop"></div>

    <div class="main-area">
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="theme-toggle menu-btn" data-menu-toggle aria-label="Abrir menú">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h1 class="page-title">@yield('title', 'Panel')</h1>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="theme-toggle" data-theme-toggle aria-label="Cambiar tema">
                    <i class="fa-solid fa-circle-half-stroke"></i>
                </button>
                <div class="dropdown">
                    <button class="btn btn-soft btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-user me-1"></i>{{ $user->name ?? 'Administrador' }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item" type="submit">
                                    <i class="fa-solid fa-arrow-right-from-bracket me-2"></i>Cerrar sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="content">
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
