<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Inventario') | {{ config('app.name', 'Inventario de Cómputo') }}</title>
    {{-- Enlace a la hoja de estilos propios sin compiladores externos innecesarios --}}
    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
</head>
<body>
    {{-- Encabezado y Barra de navegación superior --}}
    <header class="encabezado-sistema">
        <div class="barra-superior">
            <a href="{{ url('/') }}" class="marca-sistema" aria-label="Ir al inicio del sistema">
                <svg class="icono-marca" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                    <line x1="8" y1="21" x2="16" y2="21"></line>
                    <line x1="12" y1="17" x2="12" y2="21"></line>
                </svg>
                <span>Inventario de Laboratorios</span>
            </a>

            <nav class="navegacion-principal" aria-label="Navegación principal del sistema">
                <a href="{{ url('/') }}" class="enlace-nav {{ request()->is('/') ? 'activo' : '' }}">
                    Inicio
                </a>
                <a href="{{ route('equipos.index') }}" class="enlace-nav {{ request()->routeIs('equipos.*') ? 'activo' : '' }}">
                    Equipos
                </a>
                <a href="{{ route('mantenimientos.index') }}" class="enlace-nav {{ request()->routeIs('mantenimientos.*') ? 'activo' : '' }}">
                    Mantenimientos
                </a>
            </nav>
        </div>
    </header>

    {{-- Contenedor principal con mensajes flash centralizados --}}
    <main class="contenedor-principal">
        {{-- Mensajes flash de sesión centralizados para evitar duplicación en vistas --}}
        @if (session('exito'))
            <div class="alerta alerta-exito exito" role="status">
                <strong>Éxito:</strong> {{-- [SEGURIDAD] Salida escapada --}}{{ session('exito') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alerta alerta-error error" role="alert">
                <strong>Aviso:</strong> {{-- [SEGURIDAD] Salida escapada --}}{{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    {{-- Pie de página accesible --}}
    <footer class="pie-pagina">
        <p>Sistema de Inventario de Laboratorios de Cómputo &copy; {{ date('Y') }} — Caso 4</p>
    </footer>
</body>
</html>
