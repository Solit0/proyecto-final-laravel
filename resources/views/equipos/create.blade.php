<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrar equipo | {{ config('app.name') }}</title>
    <style>
        :root { color-scheme: light; }
        body { margin: 0; background: #f4f6f8; color: #1f2933; font-family: system-ui, -apple-system, "Segoe UI", sans-serif; }
        main { max-width: 780px; margin: 0 auto; padding: 2rem 1rem 3rem; }
        header { display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
        h1 { margin: 0; font-size: 1.65rem; }
        a { color: #0b5cad; }
        form { background: #fff; padding: 1.6rem; border-radius: .5rem; box-shadow: 0 1px 4px rgb(15 23 42 / 10%); }
        .campo { margin-bottom: 1.1rem; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        @media (max-width: 600px) { .grid-2 { grid-template-columns: 1fr; gap: 0; } }
        label { display: block; margin-bottom: .35rem; font-weight: 600; font-size: .93rem; }
        input, select, textarea { width: 100%; box-sizing: border-box; padding: .55rem .65rem; border: 1px solid #b8c4d0; border-radius: .35rem; font: inherit; background: #fff; font-size: .95rem; }
        input:focus, select:focus { outline: none; border-color: #0b5cad; box-shadow: 0 0 0 3px rgba(11, 92, 173, .15); }
        .error { display: block; margin-top: .3rem; color: #b42318; font-size: .88rem; font-weight: 500; }
        .boton { display: inline-block; padding: .65rem 1.1rem; border: 0; border-radius: .45rem; background: #0b5cad; color: #fff; text-decoration: none; font: inherit; font-weight: 600; cursor: pointer; }
        .boton:hover { background: #094a8f; }
        .acciones { display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; margin-top: 1.5rem; }
    </style>
</head>
<body>
<main>
    <header>
        <h1>Registrar nuevo equipo</h1>
        <a href="{{ route('equipos.index') }}">Volver al inventario</a>
    </header>

    <form action="{{ route('equipos.store') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf

        @include('equipos._form')

        <div class="acciones">
            <button class="boton" type="submit">Guardar equipo</button>
            <a href="{{ route('equipos.index') }}">Cancelar</a>
        </div>
    </form>
</main>
</body>
</html>
