@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
<div class="cabecera-pagina">
    <div class="cabecera-titulos">
        <h1>Sistema de Inventario de Laboratorios de Cómputo</h1>
        <p>Control y administración centralizada de bienes tecnológicos y registro de mantenimientos</p>
    </div>
    <div class="cabecera-acciones">
        <a href="{{ route('reportes.index') }}" class="boton boton-secundario">Ver Reportes</a>
        <a href="{{ route('equipos.create') }}" class="boton">+ Registrar Equipo</a>
    </div>
</div>

{{-- [CRUD-READ] Resumen e indicadores del inventario en tiempo real --}}
<section class="tarjeta" aria-label="Indicadores del inventario">
    <h2 style="font-size: 1.15rem; margin-top: 0; margin-bottom: 1.25rem; color: var(--color-texto);">
        Estado General del Inventario
    </h2>
    <div class="grid-datos">
        <div class="dato-item">
            <div class="dato-label">Total de Equipos</div>
            <div class="dato-valor" style="font-size: 1.6rem; color: var(--color-primario);">
                {{ $totalEquipos }}
            </div>
            <span class="dato-secundario">Bienes registrados</span>
        </div>

        <div class="dato-item">
            <div class="dato-label">Operativos</div>
            <div class="dato-valor" style="font-size: 1.6rem; color: #166534;">
                {{ $totalOperativos }}
            </div>
            <span class="badge badge-operativo">operativo</span>
        </div>

        <div class="dato-item">
            <div class="dato-label">En Reparación</div>
            <div class="dato-valor" style="font-size: 1.6rem; color: #9a3412;">
                {{ $totalEnReparacion }}
            </div>
            <span class="badge badge-en_reparacion">en reparación</span>
        </div>

        <div class="dato-item">
            <div class="dato-label">De Baja</div>
            <div class="dato-valor" style="font-size: 1.6rem; color: #991b1b;">
                {{ $totalDeBaja }}
            </div>
            <span class="badge badge-de_baja">de baja</span>
        </div>

        <div class="dato-item">
            <div class="dato-label">Laboratorios</div>
            <div class="dato-valor" style="font-size: 1.6rem;">
                {{ $totalLaboratorios }}
            </div>
            <span class="dato-secundario">Espacios físicos</span>
        </div>

        <div class="dato-item">
            <div class="dato-label">Mantenimientos</div>
            <div class="dato-valor" style="font-size: 1.6rem;">
                {{ $totalMantenimientos }}
            </div>
            <span class="dato-secundario">Intervenciones totales</span>
        </div>
    </div>
</section>

{{-- Accesos rápidos a los módulos del sistema --}}
<div class="grid-panel">
    {{-- Tarjeta de Inventario de Equipos --}}
    <div class="tarjeta-panel">
        <div>
            <h2>Inventario de Equipos</h2>
            <p>Consulta el catálogo general de equipos de cómputo, proyectores, impresoras y switches distribuidos en los laboratorios. Filtra por estado o ubicación.</p>
        </div>
        <div class="acciones-panel">
            <a href="{{ route('equipos.index') }}" class="boton">Ver Inventario</a>
            <a href="{{ route('equipos.create') }}" class="boton boton-secundario">+ Registrar Equipo</a>
        </div>
    </div>

    {{-- Tarjeta de Registro de Mantenimientos --}}
    <div class="tarjeta-panel">
        <div>
            <h2>Mantenimientos</h2>
            <p>Historial detallado de intervenciones preventivas y correctivas realizadas en los equipos asignados a los laboratorios de la institución.</p>
        </div>
        <div class="acciones-panel">
            <a href="{{ route('mantenimientos.index') }}" class="boton">Ver Mantenimientos</a>
            <a href="{{ route('mantenimientos.create') }}" class="boton boton-secundario">+ Registrar Mantenimiento</a>
        </div>
    </div>

    {{-- Tarjeta de Reportes y Estadísticas --}}
    <div class="tarjeta-panel">
        <div>
            <h2>Reportes de Gestión</h2>
            <p>Reportes consolidados de distribución de equipos por laboratorio y estado, costos acumulados de mantenimiento y equipos sin mantenimiento reciente.</p>
        </div>
        <div class="acciones-panel">
            <a href="{{ route('reportes.index') }}" class="boton">Consultar Reportes</a>
        </div>
    </div>

    {{-- Tarjeta informativa de Laboratorios --}}
    <div class="tarjeta-panel">
        <div>
            <h2>Laboratorios Registrados</h2>
            <p>Monitoreo de espacios académicos y capacidad instalada asignada para prácticas de computación y redes especializadas.</p>
        </div>
        <div class="acciones-panel">
            <a href="{{ route('equipos.index') }}" class="boton boton-secundario">Explorar por Laboratorio</a>
        </div>
    </div>
</div>
@endsection
