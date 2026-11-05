<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mantenimientos | {{ config('app.name') }}</title>
    <style>
        :root { color-scheme: light; }
        body { margin: 0; background: #f4f6f8; color: #1f2933; font-family: system-ui, -apple-system, "Segoe UI", sans-serif; }
        main { max-width: 1100px; margin: 0 auto; padding: 2rem 1rem 3rem; }
        header { display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
        h1 { margin: 0; font-size: 1.65rem; }
        a { color: #0b5cad; }
        .boton { display: inline-block; padding: .6rem .9rem; border-radius: .45rem; background: #0b5cad; color: #fff; text-decoration: none; font-weight: 600; }
        .exito { padding: .75rem 1rem; border-radius: .45rem; background: #e3f6e8; color: #17633a; border: 1px solid #a6dbb5; }
        table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 1px 4px rgb(15 23 42 / 10%); }
        th, td { padding: .7rem .75rem; border-bottom: 1px solid #e4e9ee; text-align: left; vertical-align: top; font-size: .93rem; }
        th { background: #eef3f8; font-size: .8rem; text-transform: uppercase; letter-spacing: .04em; }
        tr:last-child td { border-bottom: 0; }
        .vacio { padding: 1.25rem; background: #fff; border-radius: .45rem; }
        .paginacion { margin-top: 1rem; }
        .paginacion nav { display: flex; flex-wrap: wrap; gap: .35rem; align-items: center; }
        .paginacion a, .paginacion span { padding: .35rem .6rem; border: 1px solid #cbd5e1; border-radius: .35rem; background: #fff; text-decoration: none; }
    </style>
</head>
<body>
<main>
    <header>
        <h1>Mantenimientos registrados</h1>
        <a class="boton" href="{{ route('mantenimientos.create') }}">Registrar mantenimiento</a>
    </header>

    @if (session('exito'))
        <p class="exito" role="status">{{ session('exito') }}</p>
    @endif

    @if ($mantenimientos->isEmpty())
        <p class="vacio">Todavía no hay mantenimientos registrados.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Equipo</th>
                    <th>Laboratorio</th>
                    <th>Tipo</th>
                    <th>Descripción</th>
                    <th>Técnico responsable</th>
                    <th>Costo</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($mantenimientos as $mantenimiento)
                    <tr>
                        <td>{{ $mantenimiento->fecha->format('d/m/Y') }}</td>
                        <td>{{ $mantenimiento->equipo->codigo_inventario }} — {{ $mantenimiento->equipo->marca }} {{ $mantenimiento->equipo->modelo }}</td>
                        <td>{{ $mantenimiento->equipo->laboratorio->nombre }}</td>
                        <td>{{ ucfirst($mantenimiento->tipo) }}</td>
                        <td>{{ $mantenimiento->descripcion }}</td>
                        <td>{{ $mantenimiento->tecnico_responsable }}</td>
                        <td>${{ number_format((float) $mantenimiento->costo, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="paginacion">
            {{ $mantenimientos->links() }}
        </div>
    @endif
</main>
</body>
</html>
