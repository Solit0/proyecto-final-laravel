@extends('layouts.app')

@section('title', 'Equipo ' . $equipo->codigo_inventario)

@section('content')
<div class="cabecera-pagina">
    <div class="cabecera-titulos">
        <p style="margin-bottom: 0.35rem;">
            <a href="{{ route('equipos.index') }}">← Volver al inventario</a>
        </p>
        {{-- [SEGURIDAD] Salida escapada con {{ }} para prevenir inyecciones XSS --}}
        <h1>Ficha del equipo: {{ $equipo->codigo_inventario }}</h1>
    </div>
    <div class="cabecera-acciones">
        <a class="boton boton-secundario" href="{{ route('equipos.edit', $equipo) }}">Editar equipo</a>
        <form action="{{ route('equipos.destroy', $equipo) }}" method="POST" class="form-eliminar" onsubmit="return confirm('¿Seguro que deseas eliminar el equipo {{ $equipo->codigo_inventario }}?');">
            {{-- [SEGURIDAD] Token de protección contra ataques CSRF --}}
            @csrf
            {{-- [SEGURIDAD] Método HTTP DELETE para eliminación segura --}}
            @method('DELETE')
            <button class="boton boton-peligro" type="submit">Eliminar</button>
        </form>
    </div>
</div>

<div class="tarjeta">
    <div class="tarjeta-header">
        <div>
            @php
                $tieneImagenLocal = !empty($equipo->imagen) && \Illuminate\Support\Facades\Storage::disk('public')->exists($equipo->imagen);
            @endphp
            <img class="foto-detalle"
                 src="{{ $tieneImagenLocal ? asset('storage/' . $equipo->imagen) : asset('images/equipo-default.svg') }}"
                 alt="Fotografía de {{ $equipo->codigo_inventario }}"
                 onerror="this.onerror=null;this.src='{{ asset('images/equipo-default.svg') }}';">
        </div>

        <div class="grid-datos">
            <div class="dato-item">
                <div class="dato-label">Código de inventario</div>
                <div class="dato-valor"><code>{{ $equipo->codigo_inventario }}</code></div>
            </div>

            <div class="dato-item">
                <div class="dato-label">Estado actual</div>
                <div class="dato-valor">
                    <span class="badge badge-{{ $equipo->estado }}">
                        {{ str_replace('_', ' ', $equipo->estado) }}
                    </span>
                </div>
            </div>

            <div class="dato-item">
                <div class="dato-label">Tipo de equipo</div>
                <div class="dato-valor">{{ ucfirst($equipo->tipo) }}</div>
            </div>

            <div class="dato-item">
                <div class="dato-label">Marca y Modelo</div>
                <div class="dato-valor">{{ $equipo->marca }} — {{ $equipo->modelo }}</div>
            </div>

            <div class="dato-item">
                <div class="dato-label">Número de serie</div>
                <div class="dato-valor"><code>{{ $equipo->numero_serie }}</code></div>
            </div>

            <div class="dato-item">
                <div class="dato-label">Laboratorio asignado</div>
                <div class="dato-valor">
                    {{ $equipo->laboratorio->nombre }}
                    <span class="dato-secundario">
                        {{ $equipo->laboratorio->ubicacion }} (Capacidad: {{ $equipo->laboratorio->capacidad }} puestos)
                    </span>
                </div>
            </div>

            <div class="dato-item">
                <div class="dato-label">Fecha de adquisición</div>
                <div class="dato-valor">{{ optional($equipo->fecha_adquisicion)->format('d/m/Y') }}</div>
            </div>

            <div class="dato-item">
                <div class="dato-label">Costo de compra</div>
                <div class="dato-valor">${{ number_format((float) $equipo->costo, 2) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="seccion-historial">
    <div class="cabecera-pagina">
        <div class="cabecera-titulos">
            <h2 style="margin: 0; font-size: 1.35rem;">Historial de mantenimientos ({{ $equipo->mantenimientos->count() }})</h2>
        </div>
        <div class="cabecera-acciones">
            <a class="boton" href="{{ route('mantenimientos.create') }}" style="font-size: 0.88rem;">
                + Registrar mantenimiento
            </a>
        </div>
    </div>

    @if ($equipo->mantenimientos->isEmpty())
        <div class="vacio">
            <p>Este equipo aún no tiene mantenimientos.</p>
        </div>
    @else
        <div class="tabla-contenedor">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Fecha</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Descripción</th>
                        <th scope="col">Técnico responsable</th>
                        <th scope="col">Costo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($equipo->mantenimientos as $mantenimiento)
                        <tr>
                            <td>{{ $mantenimiento->fecha->format('d/m/Y') }}</td>
                            <td>
                                <strong>{{ ucfirst($mantenimiento->tipo) }}</strong>
                            </td>
                            {{-- [SEGURIDAD] Salida escapada --}}
                            <td>{{ $mantenimiento->descripcion }}</td>
                            <td>{{ $mantenimiento->tecnico_responsable }}</td>
                            <td>${{ number_format((float) $mantenimiento->costo, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
