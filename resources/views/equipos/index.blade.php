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
        .exito { padding: .75rem 1rem; border-radius: .45rem; background: #e3f6e8; color: #17633a; border: 1px solid #a6dbb5; margin-bottom: 1.2rem; }
        .error { padding: .75rem 1rem; border-radius: .45rem; background: #fde8e8; color: #9b1c1c; border: 1px solid #f8b4b4; margin-bottom: 1.2rem; }
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
        </div>
    </header>

    @if (session('exito'))
        <p class="exito" role="status">{{ session('exito') }}</p>
    @endif

    @if (session('error'))
        <p class="error" role="alert">{{ session('error') }}</p>
    @endif

    @if ($equipos->isEmpty())
        <div class="vacio">
            <p style="font-size: 1.05rem; margin-bottom: .5rem;">No se encontraron equipos registrados.</p>
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
                            <a href="{{ route('equipos.show', $equipo) }}">Ver detalle</a>
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
