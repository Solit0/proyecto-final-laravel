@extends('layouts.app')

@section('title', 'Registrar mantenimiento')

@section('content')
<div class="cabecera-pagina">
    <div class="cabecera-titulos">
        <p style="margin-bottom: 0.35rem;">
            <a href="{{ route('mantenimientos.index') }}">← Volver al listado</a>
        </p>
        <h1>Registrar mantenimiento</h1>
        <p>Registra intervenciones técnicas. Los mantenimientos correctivos cambiarán automáticamente el equipo a "En reparación".</p>
    </div>
</div>

<form class="formulario" action="{{ route('mantenimientos.store') }}" method="POST" novalidate>
    {{-- [SEGURIDAD] Token de protección contra ataques CSRF --}}
    @csrf

    @if ($equipos->isEmpty())
        <div class="alerta alerta-error error" role="alert" style="margin-bottom: 1.25rem;">
            <p style="margin: 0;"><strong>Aviso:</strong> No hay equipos habilitados para mantenimiento en este momento (los equipos dados de baja no admiten registro).</p>
        </div>
    @endif

    <div class="campo">
        <label for="equipo_id">Equipo a intervenir <span aria-hidden="true">*</span></label>
        <select id="equipo_id" name="equipo_id" required aria-describedby="@error('equipo_id') error-equipo_id @enderror">
            <option value="">Seleccione un equipo</option>
            @foreach ($equipos as $equipo)
                <option value="{{ $equipo->id }}" @selected((string) old('equipo_id') === (string) $equipo->id)>
                    {{-- [SEGURIDAD] Salida escapada --}}
                    {{ $equipo->codigo_inventario }} — {{ $equipo->marca }} {{ $equipo->modelo }} ({{ $equipo->laboratorio->nombre }})
                </option>
            @endforeach
        </select>
        @error('equipo_id')
            <span id="error-equipo_id" class="mensaje-error error" role="alert">{{ $message }}</span>
        @enderror
    </div>

    <div class="grid-2">
        <div class="campo">
            <label for="fecha">Fecha del mantenimiento <span aria-hidden="true">*</span></label>
            <input id="fecha"
                   name="fecha"
                   type="date"
                   value="{{ old('fecha', now()->toDateString()) }}"
                   required
                   aria-describedby="@error('fecha') error-fecha @enderror">
            @error('fecha')
                <span id="error-fecha" class="mensaje-error error" role="alert">{{ $message }}</span>
            @enderror
        </div>

        <div class="campo">
            <label for="tipo">Tipo de mantenimiento <span aria-hidden="true">*</span></label>
            <select id="tipo" name="tipo" required aria-describedby="ayuda-tipo @error('tipo') error-tipo @enderror">
                <option value="">Seleccione el tipo</option>
                <option value="preventivo" @selected(old('tipo') === 'preventivo')>Preventivo</option>
                <option value="correctivo" @selected(old('tipo') === 'correctivo')>Correctivo</option>
            </select>
            <small id="ayuda-tipo" class="ayuda">Nota: El mantenimiento correctivo dejará automáticamente el equipo en estado "En reparación".</small>
            @error('tipo')
                <span id="error-tipo" class="mensaje-error error" role="alert">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="campo">
        <label for="descripcion">Descripción del trabajo realizado <span aria-hidden="true">*</span></label>
        <textarea id="descripcion"
                  name="descripcion"
                  placeholder="Detalla las actividades realizadas, repuestos cambiados o diagnóstico..."
                  required
                  aria-describedby="@error('descripcion') error-descripcion @enderror">{{ old('descripcion') }}</textarea>
        @error('descripcion')
            <span id="error-descripcion" class="mensaje-error error" role="alert">{{ $message }}</span>
        @enderror
    </div>

    <div class="grid-2">
        <div class="campo">
            <label for="tecnico_responsable">Técnico responsable <span aria-hidden="true">*</span></label>
            <input id="tecnico_responsable"
                   name="tecnico_responsable"
                   type="text"
                   maxlength="150"
                   placeholder="Nombre del técnico o encargado"
                   value="{{ old('tecnico_responsable') }}"
                   required
                   aria-describedby="@error('tecnico_responsable') error-tecnico_responsable @enderror">
            @error('tecnico_responsable')
                <span id="error-tecnico_responsable" class="mensaje-error error" role="alert">{{ $message }}</span>
            @enderror
        </div>

        <div class="campo">
            <label for="costo">Costo ($ USD) <span aria-hidden="true">*</span></label>
            <input id="costo"
                   name="costo"
                   type="number"
                   min="0"
                   max="99999999.99"
                   step="0.01"
                   placeholder="0.00"
                   value="{{ old('costo') }}"
                   required
                   aria-describedby="@error('costo') error-costo @enderror">
            @error('costo')
                <span id="error-costo" class="mensaje-error error" role="alert">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="acciones-formulario">
        <button class="boton" type="submit" @disabled($equipos->isEmpty())>Guardar mantenimiento</button>
        <a class="boton boton-secundario" href="{{ route('mantenimientos.index') }}">Cancelar</a>
    </div>
</form>
@endsection
