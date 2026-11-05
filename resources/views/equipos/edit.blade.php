@extends('layouts.app')

@section('title', 'Editar equipo ' . $equipo->codigo_inventario)

@section('content')
<div class="cabecera-pagina">
    <div class="cabecera-titulos">
        <p style="margin-bottom: 0.35rem;">
            <a href="{{ route('equipos.show', $equipo) }}">← Volver a la ficha</a>
        </p>
        {{-- [SEGURIDAD] Salida escapada --}}
        <h1>Editar equipo: {{ $equipo->codigo_inventario }}</h1>
        <p>Modifica los campos del equipo. La fecha de adquisición no puede ser posterior al mantenimiento más antiguo registrado.</p>
    </div>
</div>

<form class="formulario" action="{{ route('equipos.update', $equipo) }}" method="POST" enctype="multipart/form-data" novalidate>
    {{-- [SEGURIDAD] Token de protección contra ataques CSRF --}}
    @csrf
    {{-- [SEGURIDAD] Método HTTP PUT para actualización controlada del recurso --}}
    @method('PUT')

    @include('equipos._form')

    <div class="acciones-formulario">
        <button class="boton" type="submit">Actualizar equipo</button>
        <a class="boton boton-secundario" href="{{ route('equipos.show', $equipo) }}">Cancelar</a>
    </div>
</form>
@endsection
