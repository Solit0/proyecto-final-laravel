@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
<div class="cabecera-pagina">
    <div class="cabecera-titulos">
        <h1>Sistema de Inventario de Laboratorios de Cómputo</h1>
        <p>Control y administración centralizada de bienes tecnológicos y registro de mantenimientos</p>
    </div>
</div>

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
