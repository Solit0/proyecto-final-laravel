@extends('layouts.app')

@section('title', 'Mantenimientos')

@section('content')
<div class="cabecera-pagina">
    <div class="cabecera-titulos">
        <h1>Historial de Mantenimientos</h1>
        <p>Registro de actividades preventivas y correctivas en los equipos de los laboratorios</p>
    </div>
    <div class="cabecera-acciones">
        <a class="boton" href="{{ route('mantenimientos.create') }}">+ Registrar mantenimiento</a>
    </div>
</div>

@if ($mantenimientos->isEmpty())
    <div class="vacio" role="region" aria-label="Sin mantenimientos">
        <p><strong>Todavía no hay mantenimientos registrados en el sistema.</strong></p>
        <p>Cuando se realicen mantenimientos preventivos o correctivos sobre los equipos, aparecerán listados aquí.</p>
        <p>
            <a class="boton" href="{{ route('mantenimientos.create') }}">+ Registrar el primer mantenimiento</a>
        </p>
    </div>
@else
    {{-- Contenedor con desplazamiento horizontal para tablas en pantallas estrechas --}}
    <div class="tabla-contenedor">
        <table>
            <thead>
                <tr>
                    <th scope="col">Fecha</th>
                    <th scope="col">Equipo</th>
                    <th scope="col">Laboratorio</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Descripción</th>
                    <th scope="col">Técnico responsable</th>
                    <th scope="col">Costo</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($mantenimientos as $mantenimiento)
                    <tr>
                        <td style="white-space: nowrap;">{{ $mantenimiento->fecha->format('d/m/Y') }}</td>
                        <td>
                            {{-- [SEGURIDAD] Enlace a la ficha del equipo con salida escapada --}}
                            <a href="{{ route('equipos.show', $mantenimiento->equipo) }}" style="font-weight: 600;">
                                {{ $mantenimiento->equipo->codigo_inventario }}
                            </a>
                            <span class="dato-secundario">
                                {{ $mantenimiento->equipo->marca }} {{ $mantenimiento->equipo->modelo }}
                            </span>
                        </td>
                        <td>{{ $mantenimiento->equipo->laboratorio->nombre }}</td>
                        <td>
                            <span class="badge badge-{{ $mantenimiento->tipo === 'correctivo' ? 'en_reparacion' : 'operativo' }}">
                                {{ ucfirst($mantenimiento->tipo) }}
                            </span>
                        </td>
                        {{-- [SEGURIDAD] Salida escapada con {{ }} para la descripción del usuario --}}
                        <td style="max-width: 320px;">{{ $mantenimiento->descripcion }}</td>
                        <td>{{ $mantenimiento->tecnico_responsable }}</td>
                        <td style="white-space: nowrap;">${{ number_format((float) $mantenimiento->costo, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Paginación funcional --}}
    <div class="paginacion" aria-label="Navegación de páginas de mantenimientos">
        {{ $mantenimientos->links() }}
    </div>
@endif
@endsection
