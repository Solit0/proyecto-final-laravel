<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventario de Equipos | {{ config('app.name') }}</title>
    <style>
        :root { color-scheme: light; }
        body { margin: 0; background: #f4f6f8; color: #1f2933; font-family: system-ui, -apple-system, "Segoe UI", sans-serif; }
        main { max-width: 1200px; margin: 0 auto; padding: 2rem 1rem 3rem; }
        header { display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
        h1 { margin: 0; font-size: 1.65rem; }
        .cabecera-acciones { display: flex; gap: .75rem; align-items: center; flex-wrap: wrap; }
        a { color: #0b5cad; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .boton { display: inline-block; padding: .6rem .95rem; border-radius: .45rem; background: #0b5cad; color: #fff; text-decoration: none; font-weight: 600; font-size: .92rem; border: 0; cursor: pointer; }
        .boton:hover { background: #094a8f; text-decoration: none; }
        .boton-secundario { background: #e2e8f0; color: #334155; }
        .boton-secundario:hover { background: #cbd5e1; }
        .boton-peligro { background: #dc2626; color: #fff; padding: .35rem .6rem; font-size: .82rem; border-radius: .35rem; }
        .boton-peligro:hover { background: #b91c1c; }
        .exito { padding: .75rem 1rem; border-radius: .45rem; background: #e3f6e8; color: #17633a; border: 1px solid #a6dbb5; margin-bottom: 1.2rem; }
        .error { padding: .75rem 1rem; border-radius: .45rem; background: #fde8e8; color: #9b1c1c; border: 1px solid #f8b4b4; margin-bottom: 1.2rem; }
        .filtros { background: #fff; padding: 1rem 1.2rem; border-radius: .5rem; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgb(15 23 42 / 8%); }
        .filtros form { display: flex; flex-wrap: wrap; gap: .8rem; align-items: flex-end; }
        .filtros .campo { flex: 1 1 200px; }
        .filtros label { display: block; font-size: .82rem; font-weight: 600; margin-bottom: .25rem; color: #475569; }
        .filtros input, .filtros select { width: 100%; box-sizing: border-box; padding: .45rem .6rem; border: 1px solid #cbd5e1; border-radius: .35rem; font: inherit; font-size: .9rem; background: #fff; }
        .filtros .acciones-filtro { display: flex; gap: .5rem; align-items: center; }
        table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 1px 4px rgb(15 23 42 / 10%); border-radius: .45rem; overflow: hidden; }
        th, td { padding: .75rem .85rem; border-bottom: 1px solid #e4e9ee; text-align: left; vertical-align: middle; font-size: .92rem; }
        th { background: #eef3f8; font-size: .8rem; text-transform: uppercase; letter-spacing: .04em; color: #475569; }
        tr:last-child td { border-bottom: 0; }
        .badge { display: inline-block; padding: .2rem .55rem; border-radius: 9999px; font-size: .78rem; font-weight: 600; text-transform: capitalize; }
        .badge-operativo { background: #dcfce7; color: #15803d; }
        .badge-en_reparacion { background: #fef3c7; color: #b45309; }
        .badge-de_baja { background: #fee2e2; color: #b91c1c; }
        .foto-thumb { width: 44px; height: 44px; border-radius: .35rem; object-fit: cover; border: 1px solid #e2e8f0; vertical-align: middle; }
        .foto-placeholder { width: 44px; height: 44px; border-radius: .35rem; background: #e2e8f0; display: inline-flex; align-items: center; justify-content: center; font-size: .75rem; color: #64748b; font-weight: 600; }
        .acciones-tabla { display: flex; gap: .45rem; align-items: center; }
        .vacio { padding: 2rem; background: #fff; border-radius: .45rem; text-align: center; color: #64748b; }
        .paginacion { margin-top: 1.25rem; }
        .paginacion nav { display: flex; flex-wrap: wrap; gap: .35rem; align-items: center; }
        .paginacion a, .paginacion span { padding: .35rem .6rem; border: 1px solid #cbd5e1; border-radius: .35rem; background: #fff; text-decoration: none; }
    </style>
</head>
<body>
<main>
    <header>
        <div>
            <h1>Inventario de equipos</h1>
            <p style="margin: .3rem 0 0; color: #64748b; font-size: .93rem;">Control de bienes y equipos de cómputo en laboratorios universitarios</p>
        </div>
        <div class="cabecera-acciones">
            <a class="boton boton-secundario" href="{{ route('mantenimientos.index') }}">Ver mantenimientos</a>
            <a class="boton" href="{{ route('equipos.create') }}">+ Registrar equipo</a>
        </div>
    </header>

    @if (session('exito'))
        <p class="exito" role="status">{{ session('exito') }}</p>
    @endif

    @if (session('error'))
        <p class="error" role="alert">{{ session('error') }}</p>
    @endif

    <div class="filtros">
        <form method="GET" action="{{ route('equipos.index') }}">
            <div class="campo">
                <label for="q">Buscar (código, marca o modelo)</label>
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
                @if (request()->hasAny(['q', 'laboratorio_id', 'estado']))
                    <a class="boton boton-secundario" href="{{ route('equipos.index') }}">Limpiar</a>
                @endif
            </div>
        </form>
    </div>

    @if ($equipos->isEmpty())
        <div class="vacio">
            <p style="font-size: 1.05rem; margin-bottom: .5rem;">No se encontraron equipos registrados.</p>
            <p style="font-size: .9rem; margin: 0;">Prueba modificando los criterios de búsqueda o <a href="{{ route('equipos.create') }}">registra un nuevo equipo</a>.</p>
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Código</th>
                    <th>Tipo</th>
                    <th>Marca / Modelo</th>
                    <th>N° Serie</th>
                    <th>Laboratorio</th>
                    <th>Estado</th>
                    <th>Costo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($equipos as $equipo)
                    <tr>
                        <td>
                            @if ($equipo->imagen)
                                <img class="foto-thumb" src="{{ asset('storage/' . $equipo->imagen) }}" alt="{{ $equipo->codigo_inventario }}">
                            @else
                                <span class="foto-placeholder">S/F</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('equipos.show', $equipo) }}" style="font-weight: 600;">
                                {{ $equipo->codigo_inventario }}
                            </a>
                        </td>
                        <td>{{ ucfirst($equipo->tipo) }}</td>
                        <td>{{ $equipo->marca }} <span style="color: #64748b;">{{ $equipo->modelo }}</span></td>
                        <td><code style="font-size: .85rem;">{{ $equipo->numero_serie }}</code></td>
                        <td>{{ $equipo->laboratorio->nombre }}</td>
                        <td>
                            <span class="badge badge-{{ $equipo->estado }}">
                                {{ str_replace('_', ' ', $equipo->estado) }}
                            </span>
                        </td>
                        <td>${{ number_format((float) $equipo->costo, 2) }}</td>
                        <td>
                            <div class="acciones-tabla">
                                <a href="{{ route('equipos.show', $equipo) }}">Detalle</a>
                                <span>·</span>
                                <a href="{{ route('equipos.edit', $equipo) }}">Editar</a>
                                <span>·</span>
                                <form action="{{ route('equipos.destroy', $equipo) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este equipo?');" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="boton-peligro" type="submit">Borrar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="paginacion">
            {{ $equipos->links() }}
        </div>
    @endif
</main>
</body>
</html>
