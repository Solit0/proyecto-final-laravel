<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrar mantenimiento | {{ config('app.name') }}</title>
    <style>
        :root { color-scheme: light; }
        body { margin: 0; background: #f4f6f8; color: #1f2933; font-family: system-ui, -apple-system, "Segoe UI", sans-serif; }
        main { max-width: 720px; margin: 0 auto; padding: 2rem 1rem 3rem; }
        header { display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
        h1 { margin: 0; font-size: 1.65rem; }
        a { color: #0b5cad; }
        form { background: #fff; padding: 1.4rem; border-radius: .5rem; box-shadow: 0 1px 4px rgb(15 23 42 / 10%); }
        .campo { margin-bottom: 1.05rem; }
        label { display: block; margin-bottom: .35rem; font-weight: 600; }
        input, select, textarea { width: 100%; box-sizing: border-box; padding: .55rem .6rem; border: 1px solid #b8c4d0; border-radius: .35rem; font: inherit; background: #fff; }
        textarea { min-height: 6rem; resize: vertical; }
        .error { display: block; margin-top: .3rem; color: #b42318; font-size: .88rem; }
        .boton { display: inline-block; padding: .65rem 1rem; border: 0; border-radius: .45rem; background: #0b5cad; color: #fff; text-decoration: none; font: inherit; font-weight: 600; cursor: pointer; }
        .acciones { display: flex; flex-wrap: wrap; gap: .8rem; align-items: center; margin-top: 1.2rem; }
    </style>
</head>
<body>
<main>
    <header>
        <h1>Registrar mantenimiento</h1>
        <a href="{{ route('mantenimientos.index') }}">Volver al listado</a>
    </header>

    <form action="{{ route('mantenimientos.store') }}" method="POST" novalidate>
        @csrf

        <div class="campo">
            <label for="equipo_id">Equipo</label>
            <select id="equipo_id" name="equipo_id" required>
                <option value="">Seleccione un equipo</option>
                @foreach ($equipos as $equipo)
                    <option value="{{ $equipo->id }}" @selected((string) old('equipo_id') === (string) $equipo->id)>
                        {{ $equipo->codigo_inventario }} — {{ $equipo->marca }} {{ $equipo->modelo }} ({{ $equipo->laboratorio->nombre }})
                    </option>
                @endforeach
            </select>
            @error('equipo_id')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="campo">
            <label for="fecha">Fecha</label>
            <input id="fecha" name="fecha" type="date" value="{{ old('fecha', now()->toDateString()) }}" required>
            @error('fecha')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="campo">
            <label for="tipo">Tipo</label>
            <select id="tipo" name="tipo" required>
                <option value="">Seleccione el tipo</option>
                <option value="preventivo" @selected(old('tipo') === 'preventivo')>Preventivo</option>
                <option value="correctivo" @selected(old('tipo') === 'correctivo')>Correctivo</option>
            </select>
            @error('tipo')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="campo">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" required>{{ old('descripcion') }}</textarea>
            @error('descripcion')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="campo">
            <label for="tecnico_responsable">Técnico responsable</label>
            <input id="tecnico_responsable" name="tecnico_responsable" type="text" maxlength="150" value="{{ old('tecnico_responsable') }}" required>
            @error('tecnico_responsable')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="campo">
            <label for="costo">Costo</label>
            <input id="costo" name="costo" type="number" min="0" max="99999999.99" step="0.01" value="{{ old('costo') }}" required>
            @error('costo')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="acciones">
            <button class="boton" type="submit">Guardar mantenimiento</button>
            <a href="{{ route('mantenimientos.index') }}">Cancelar</a>
        </div>
    </form>
</main>
</body>
</html>
