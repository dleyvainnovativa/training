<!DOCTYPE html>
<html lang="es" data-theme="{{ request()->cookie('theme', 'light') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar sesión · Centro Deportivo</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/theme.css', 'resources/js/app.js'])
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-brand">
            <span class="brand-mark" style="width:32px;height:32px;border-radius:6px;background:var(--brand-500);color:#fff;display:grid;place-items:center">
                <i class="fa-solid fa-heart-pulse"></i>
            </span>
            Centro Deportivo
        </div>

        <h1 style="font-size:1.25rem;margin-bottom:.25rem">Iniciar sesión</h1>
        <p class="card-subtle" style="margin-bottom:1.4rem">Accede al panel de gestión del centro.</p>

        <div class="mb-3">
            <label class="form-label" for="email">Correo</label>
            <input type="email" id="email" class="form-control" placeholder="tu@correo.com" autocomplete="username">
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Contraseña</label>
            <input type="password" id="password" class="form-control" placeholder="••••••••" autocomplete="current-password">
        </div>

        <button id="loginBtn" class="btn btn-brand w-100 mt-2">Entrar</button>

        <p class="card-subtle mt-3 mb-0" style="font-size:.8rem">
            Acceso restringido al administrador del centro.
        </p>
    </div>
</div>

<!-- Firebase Web SDK (compat build keeps this drop-in simple) -->
<script type="module">
    import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js';
    import { getAuth, signInWithEmailAndPassword }
        from 'https://www.gstatic.com/firebasejs/10.12.0/firebase-auth.js';

    const firebaseConfig = {
        apiKey:      "{{ config('services.firebase.api_key') }}",
        authDomain:  "{{ config('services.firebase.auth_domain') }}",
        projectId:   "{{ config('services.firebase.project_id') }}",
    };

    const app  = initializeApp(firebaseConfig);
    const auth = getAuth(app);

    const btn = document.getElementById('loginBtn');
    btn.addEventListener('click', () => {
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        if (!email || !password) {
            App.toast('Ingresa correo y contraseña.', 'warn');
            return;
        }

        App.withLoading(btn, async () => {
            try {
                const cred  = await signInWithEmailAndPassword(auth, email, password);
                const token = await cred.user.getIdToken();
                const res   = await App.http.post("{{ route('auth.session') }}", { id_token: token });
                window.location.href = res.redirect;
            } catch (e) {
                const msg = e.message?.includes('auth/')
                    ? 'Credenciales incorrectas.'
                    : (e.message || 'No se pudo iniciar sesión.');
                App.toast(msg, 'bad');
            }
        });
    });
</script>
</body>
</html>
