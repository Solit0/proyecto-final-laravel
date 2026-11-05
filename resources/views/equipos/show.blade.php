<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Equipo {{ $equipo->codigo_inventario }} | {{ config('app.name') }}</title>
    <style>
        :root { color-scheme: light; }
        body { margin: 0; background: #f4f6f8; color: #1f2933; font-family: system-ui, -apple-system, "Segoe UI", sans-serif; }
        main { max-width: 1000px; margin: 0 auto; padding: 2rem 1rem 3rem; }
        header { display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
        h1 { margin: 0; font-size: 1.65rem; }
        .cabecera-acciones { display: flex; gap: .6rem; align-items: center; flex-wrap: wrap; }
        a { color: #0b5cad; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .boton { display: inline-block; padding: .55rem .9rem; border-radius: .45rem; background: #0b5cad; color: #fff; text-decoration: none; font-weight: 600; font-size: .9rem; border: 0; cursor: pointer; }
        .boton:hover { background: #094a8f; text-decoration: none; }
        .boton-secundario { background: #e2e8f0; color: #334155; }
        .boton-secundario:hover { background: #cbd5e1; }
        .boton-peligro { background: #dc2626; color: #fff; }
        .boton-peligro:hover { background: #b91c1c; }
        .exito { padding: .75rem 1rem; border-radius: .45rem; background: #e3f6e8; color: #17633a; border: 1px solid #a6dbb5; margin-bottom: 1.2rem; }
        .error { padding: .75rem 1rem; border-radius: .45rem; background: #fde8e8; color: #9b1c1c; border: 1px solid #f8b4b4; margin-bottom: 1.2rem; }
        .tarjeta { background: #fff; border-radius: .5rem; padding: 1.6rem; box-shadow: 0 1px 4px rgb(15 23 42 / 10%); margin-bottom: 2rem; }
        .tarjeta-header { display: flex; gap: 1.5rem; flex-wrap: wrap; align-items: flex-start; margin-bottom: 1.5rem; padding-bottom: 1.2rem; border-bottom: 1px solid #eef2f6; }
        .foto-detalle { width: 180px; height: 180px; border-radius: .45rem; object-fit: cover; border: 1px solid #e2e8f0; background: #f8fafc; }
        .foto-detalle-placeholder { width: 180px; height: 180px; border-radius: .45rem; background: #f1f5f9; border: 1px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; color: #64748b; font-size: .9rem; text-align: center; padding: 1rem; box-sizing: border-box; }
        .grid-datos { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.2rem; flex: 1; }
        .dato-item { font-size: .92rem; }
        .dato-label { color: #64748b; font-size: .8rem; text-transform: uppercase; letter-spacing: .03em; margin-bottom: .25rem; font-weight: 600; }
        .dato-valor { font-weight: 500; font-size: 1rem; color: #0f172a; }
        .badge { display: inline-block; padding: .25rem .65rem; border-radius: 9999px; font-size: .82rem; font-weight: 600; text-transform: capitalize; }
        .badge-operativo { background: #dcfce7; color: #15803d; }
        .badge-en_reparacion { background: #fef3c7; color: #b45309; }
        .badge-de_baja { background: #fee2e2; color: #b91c1c; }
        .seccion-historial h2 { margin: 0 0 1rem; font-size: 1.3rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; }
        table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 1px 4px rgb(15 23 42 / 10%); border-radius: .45rem; overflow: hidden; }
        th, td { padding: .75rem .85rem; border-bottom: 1px solid #e4e9ee; text-align: left; vertical-align: top; font-size: .92rem; }
        th { background: #eef3f8; font-size: .8rem; text-transform: uppercase; letter-spacing: .04em; color: #475569; }
        tr:last-child td { border-bottom: 0; }
        .vacio { padding: 1.5rem; background: #fff; border-radius: .45rem; text-align: center; color: #64748b; box-shadow: 0 1px 3px rgb(15 23 42 / 8%); }
    </style>
</head>
<body>
<main>
    <header>
        <div>
            <a href="{{ route('equipos.index') }}">← Volver al inventario</a>
            <h1 style="margin-top: .4rem;">Ficha del equipo: {{ $equipo->codigo_inventario }}</h1>
        </div>
        <div class="cabecera-acciones">
            <a class="boton boton-secundario" href="{{ route('equipos.edit', $equipo) }}">Editar equipo</a>
            <form action="{{ route('equipos.destroy', $equipo) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este equipo?');" style="display: inline;">
                @csrf
                @method('DELETE')
                <button class="boton boton-peligro" type="submit">Eliminar</button>
            </form>
        </div>
    </header>

    @if (session('exito'))
        <p class="exito" role="status">{{ session('exito') }}</p>
    @endif

    @if (session('error'))
        <p class="error" role="alert">{{ session('error') }}</p>
    @endif

    <div class="tarjeta">
        <div class="tarjeta-header">
            <div>
                @if ($equipo->imagen)
                    <img class="foto-detalle" src="{{ asset('storage/' . $equipo->imagen) }}" alt="Fotografía de {{ $equipo->codigo_inventario }}">
                @else
                    <div class="foto-detalle-placeholder">Sin fotografía registrada</div>
                @endif
            </div>

            <div class="grid-datos">
                <div class="dato-item">
                    <div class="dato-label">Código de inventario</div>
                    <div class="dato-valor"><code>{{ $equipo->codigo_inventario }}</code></div>
                </div>

                <div class="dato-item">
                    <div class="dato-label">Estado</div>
                    <div class="dato-valor">
                        <span class="badge badge-{{ $equipo->estado }}">
                            {{ str_replace('_', ' ', $equipo->estado) }}
                        </span>
                    </div>
                </div>

                <div class="dato-item">
                    <div class="dato-label">Tipo</div>
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
                        <span style="display: block; font-size: .83rem; color: #64748b; font-weight: normal;">
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
        <h2>
            <span>Historial de mantenimientos ({{ $equipo->mantenimientos->count() }})</span>
            <a class="boton" href="{{ route('mantenimientos.create') }}" style="font-size: .85rem; font-weight: 500;">
                + Registrar mantenimiento
            </a>
        </h2>

        @if ($equipo->mantenimientos->isEmpty())
            <div class="vacio">
                <p style="margin: 0;">Este equipo todavía no tiene mantenimientos registrados en el sistema.</p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>Técnico responsable</th>
                        <th>Costo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($equipo->mantenimientos as $mantenimiento)
                        <tr>
                            <td>{{ $mantenimiento->fecha->format('d/m/Y') }}</td>
                            <td>
                                <strong>{{ ucfirst($mantenimiento->tipo) }}</strong>
                            </td>
                            <td>{{ $mantenimiento->descripcion }}</td>
                            <td>{{ $mantenimiento->tecnico_responsable }}</td>
                            <td>${{ number_format((float) $mantenimiento->costo, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</main>
</body>
</html>
