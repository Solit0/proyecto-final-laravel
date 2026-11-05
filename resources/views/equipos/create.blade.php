@extends('layouts.app')

@section('title', 'Registrar nuevo equipo')

@section('content')
<div class="cabecera-pagina">
    <div class="cabecera-titulos">
        <p style="margin-bottom: 0.35rem;">
            <a href="{{ route('equipos.index') }}">← Volver al inventario</a>
        </p>
        <h1>Registrar nuevo equipo</h1>
        <p>Completa los datos técnicos del bien para asignarlo al laboratorio correspondiente</p>
    </div>
</div>

<form class="formulario" action="{{ route('equipos.store') }}" method="POST" enctype="multipart/form-data" novalidate>
    {{-- [SEGURIDAD] Token de protección contra ataques CSRF --}}
    @csrf

    @include('equipos._form')

    <div class="acciones-formulario">
        <button class="boton" type="submit">Guardar equipo</button>
        <a class="boton boton-secundario" href="{{ route('equipos.index') }}">Cancelar</a>
    </div>
</form>
@endsection
