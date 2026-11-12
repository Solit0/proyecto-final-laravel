@extends('layouts.app')

@section('title', 'Inventario de Equipos')

@section('content')
<div class="cabecera-pagina">
    <div class="cabecera-titulos">
        <h1>Inventario de Equipos</h1>
        <p>Control de bienes y equipos de cómputo en laboratorios universitarios</p>
    </div>
    <div class="cabecera-acciones">
        <a class="boton boton-secundario" href="{{ route('mantenimientos.index') }}">Ver mantenimientos</a>
        <a class="boton" href="{{ route('equipos.create') }}">+ Registrar equipo</a>
    </div>
</div>

{{-- Formulario GET de búsqueda y filtros acumulativos --}}
<div class="filtros">
    <form method="GET" action="{{ route('equipos.index') }}" role="search" aria-label="Filtros del inventario">
        <div class="campo">
            <label for="q">Buscar (código, marca o modelo)</label>
            {{-- [SEGURIDAD] Salida escapada con {{ }} para prevenir XSS --}}
            <input id="q" name="q" type="text" placeholder="Ej: LAB-0001, Dell, ThinkCentre..." value="{{ request('q') }}">
        </div>

        <div class="campo">
            <label for="filtro_laboratorio">Laboratorio</label>
            <select id="filtro_laboratorio" name="laboratorio_id">
                <option value="">Todos los laboratorios</option>
                @foreach ($laboratorios as $lab)
                    <option value="{{ $lab->id }}" @selected((string) request('laboratorio_id') === (string) $lab->id)>
                        {{ $lab->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="campo">
            <label for="filtro_estado">Estado</label>
            <select id="filtro_estado" name="estado">
                <option value="">Todos los estados</option>
                <option value="operativo" @selected(request('estado') === 'operativo')>Operativo</option>
                <option value="en_reparacion" @selected(request('estado') === 'en_reparacion')>En reparación</option>
                <option value="de_baja" @selected(request('estado') === 'de_baja')>De baja</option>
            </select>
        </div>

        <div class="acciones-filtro">
            <button class="boton" type="submit">Filtrar</button>
            @if (request()->filled('q') || request()->filled('laboratorio_id') || request()->filled('estado'))
                <a class="boton boton-secundario" href="{{ route('equipos.index') }}">Limpiar</a>
            @endif
        </div>
    </form>
</div>

@if ($equipos->isEmpty())
    {{-- Diferenciación de estados vacíos según si hay filtros aplicados o no --}}
    @if (request()->filled('q') || request()->filled('laboratorio_id') || request()->filled('estado'))
        <div class="vacio" role="region" aria-label="Sin resultados de búsqueda">
            <p><strong>Sin resultados para estos filtros</strong></p>
            <p>No se encontraron equipos que coincidan con los criterios especificados.</p>
            <p><a class="boton boton-secundario" href="{{ route('equipos.index') }}">Restablecer filtros</a></p>
        </div>
    @else
        <div class="vacio" role="region" aria-label="Inventario vacío">
            <p><strong>No hay equipos registrados</strong></p>
            <p>Comienza registrando un nuevo equipo para asignar al laboratorio.</p>
            <p><a class="boton" href="{{ route('equipos.create') }}">+ Registrar el primer equipo</a></p>
        </div>
    @endif
@else
    {{-- Contenedor con desplazamiento horizontal para tablas amplias en móviles --}}
    <div class="tabla-contenedor">
        <table>
            <thead>
                <tr>
                    <th scope="col">Foto</th>
                    <th scope="col">Código</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Marca / Modelo</th>
                    <th scope="col">N° Serie</th>
                    <th scope="col">Laboratorio</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Costo</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($equipos as $equipo)
                    <tr>
                        <td>
                            @php
                                $tieneImagenLocal = !empty($equipo->imagen) && \Illuminate\Support\Facades\Storage::disk('public')->exists($equipo->imagen);
                            @endphp
                            <img class="foto-thumb"
                                 src="{{ $tieneImagenLocal ? asset('storage/' . $equipo->imagen) : asset('images/equipo-default.svg') }}"
                                 alt="Miniatura de {{ $equipo->codigo_inventario }}"
                                 width="44"
                                 height="44"
                                 onerror="this.onerror=null;this.src='{{ asset('images/equipo-default.svg') }}';">
                        </td>
                        <td>
                            {{-- [SEGURIDAD] Salida escapada --}}
                            <a href="{{ route('equipos.show', $equipo) }}" style="font-weight: 600;">
                                {{ $equipo->codigo_inventario }}
                            </a>
                        </td>
                        <td>{{ ucfirst($equipo->tipo) }}</td>
                        <td>{{ $equipo->marca }} <span class="dato-secundario" style="display:inline;">{{ $equipo->modelo }}</span></td>
                        <td><code>{{ $equipo->numero_serie }}</code></td>
                        <td>{{ $equipo->laboratorio->nombre }}</td>
                        <td>
                            <span class="badge badge-{{ $equipo->estado }}">
                                {{ str_replace('_', ' ', $equipo->estado) }}
                            </span>
                        </td>
                        <td>${{ number_format((float) $equipo->costo, 2) }}</td>
                        <td>
                            <div class="acciones-tabla">
                                <a href="{{ route('equipos.show', $equipo) }}">Ver</a>
                                <span>·</span>
                                <a href="{{ route('equipos.edit', $equipo) }}">Editar</a>
                                <span>·</span>
                                {{-- Formulario seguro para eliminación con confirmación nativa del navegador --}}
                                <form action="{{ route('equipos.destroy', $equipo) }}" method="POST" class="form-eliminar" onsubmit="return confirm('¿Seguro que deseas eliminar el equipo {{ addslashes($equipo->codigo_inventario) }}?');">
                                    {{-- [SEGURIDAD] Token CSRF contra falsificación de peticiones cruzadas --}}
                                    @csrf
                                    {{-- [SEGURIDAD] Método HTTP DELETE para eliminación segura --}}
                                    @method('DELETE')
                                    <button class="boton-peligro boton-sm" type="submit">Borrar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Paginación funcional con conservación de parámetros de búsqueda --}}
    <div class="paginacion" aria-label="Navegación de páginas">
        {{ $equipos->links() }}
    </div>
@endif
@endsection
