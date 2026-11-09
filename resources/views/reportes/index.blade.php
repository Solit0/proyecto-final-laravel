@extends('layouts.app')

@section('title', 'Reportes de Gestión del Inventario')

@section('content')
<div class="cabecera-pagina">
    <div class="cabecera-titulos">
        <h1>Reportes de Gestión del Inventario</h1>
        <p>Consolidado de distribución de equipos, costos acumulados de mantenimiento y equipos sin atención reciente</p>
    </div>
    <div class="cabecera-acciones">
        <a href="{{ route('panel.index') }}" class="boton boton-secundario">Volver al Inicio</a>
        <a href="{{ route('equipos.index') }}" class="boton">Ver Inventario Completo</a>
    </div>
</div>

{{-- =========================================================================
     REPORTE 1: Distribución de equipos por laboratorio y estado
     ========================================================================= --}}
<section class="tarjeta" aria-labelledby="titulo-reporte-distribucion">
    <div class="tarjeta-header">
        <div>
            <h2 id="titulo-reporte-distribucion" style="font-size: 1.25rem; margin: 0 0 0.35rem; color: var(--color-texto);">
                1. Distribución de Equipos por Laboratorio y Estado
            </h2>
            <p style="margin: 0; font-size: 0.9rem; color: var(--color-texto-secundario);">
                Resumen de equipos clasificados por su ubicación física y condición operativa actual (incluye laboratorios con conteo en cero).
            </p>
        </div>
    </div>

    @if ($distribucionLaboratorios->isEmpty())
        <div class="vacio" role="region" aria-label="Sin laboratorios registrados">
            <p><strong>No hay laboratorios registrados en el sistema.</strong></p>
        </div>
    @else
        <div class="tabla-contenedor">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Laboratorio</th>
                        <th scope="col">Ubicación</th>
                        <th scope="col" style="text-align: center;">Capacidad</th>
                        <th scope="col" style="text-align: center;">Operativos</th>
                        <th scope="col" style="text-align: center;">En Reparación</th>
                        <th scope="col" style="text-align: center;">De Baja</th>
                        <th scope="col" style="text-align: center;">Total Equipos</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $acumOperativos = 0;
                        $acumReparacion = 0;
                        $acumBaja = 0;
                        $acumTotal = 0;
                    @endphp
                    @foreach ($distribucionLaboratorios as $lab)
                        @php
                            $acumOperativos += $lab->equipos_operativos_count;
                            $acumReparacion += $lab->equipos_en_reparacion_count;
                            $acumBaja += $lab->equipos_de_baja_count;
                            $acumTotal += $lab->equipos_count;
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $lab->nombre }}</strong>
                            </td>
                            <td>{{ $lab->ubicacion }}</td>
                            <td style="text-align: center;">{{ $lab->capacidad }} puestos</td>
                            <td style="text-align: center;">
                                @if ($lab->equipos_operativos_count > 0)
                                    <span class="badge badge-operativo">{{ $lab->equipos_operativos_count }}</span>
                                @else
                                    <span class="dato-secundario">0</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if ($lab->equipos_en_reparacion_count > 0)
                                    <span class="badge badge-en_reparacion">{{ $lab->equipos_en_reparacion_count }}</span>
                                @else
                                    <span class="dato-secundario">0</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if ($lab->equipos_de_baja_count > 0)
                                    <span class="badge badge-de_baja">{{ $lab->equipos_de_baja_count }}</span>
                                @else
                                    <span class="dato-secundario">0</span>
                                @endif
                            </td>
                            <td style="text-align: center; font-weight: 700;">
                                {{ $lab->equipos_count }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #f1f5f9; font-weight: 700;">
                        <td colspan="3" style="text-align: right; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.05em;">
                            Totales Consolidados:
                        </td>
                        <td style="text-align: center; color: #166534;">{{ $acumOperativos }}</td>
                        <td style="text-align: center; color: #9a3412;">{{ $acumReparacion }}</td>
                        <td style="text-align: center; color: #991b1b;">{{ $acumBaja }}</td>
                        <td style="text-align: center; font-size: 1.05rem; color: var(--color-primario);">{{ $acumTotal }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</section>

{{-- =========================================================================
     REPORTE 2: Costo total de mantenimiento acumulado por equipo
     ========================================================================= --}}
<section class="tarjeta" aria-labelledby="titulo-reporte-costos">
    <div class="tarjeta-header">
        <div>
            <h2 id="titulo-reporte-costos" style="font-size: 1.25rem; margin: 0 0 0.35rem; color: var(--color-texto);">
                2. Costo Total de Mantenimiento por Equipo
            </h2>
            <p style="margin: 0; font-size: 0.9rem; color: var(--color-texto-secundario);">
                Cálculo acumulado mediante agregados Eloquent (<kbd>withSum</kbd>). Equipos sin historial muestran importe en cero ($0.00).
            </p>
        </div>
    </div>

    @if ($equiposConCostos->isEmpty())
        <div class="vacio" role="region" aria-label="Sin equipos registrados">
            <p><strong>No hay equipos registrados en el inventario.</strong></p>
        </div>
    @else
        <div class="tabla-contenedor">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Código</th>
                        <th scope="col">Equipo</th>
                        <th scope="col">Laboratorio</th>
                        <th scope="col">Estado</th>
                        <th scope="col" style="text-align: right;">Costo de Adquisición</th>
                        <th scope="col" style="text-align: center;">Mantenimientos</th>
                        <th scope="col" style="text-align: right;">Costo Acumulado</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $granTotalMantenimientos = 0;
                    @endphp
                    @foreach ($equiposConCostos as $equipo)
                        @php
                            $costoAcumulado = (float) ($equipo->mantenimientos_sum_costo ?? 0);
                            $granTotalMantenimientos += $costoAcumulado;
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('equipos.show', $equipo) }}" style="font-weight: 600;">
                                    {{ $equipo->codigo_inventario }}
                                </a>
                            </td>
                            <td>
                                {{ ucfirst($equipo->tipo) }} — {{ $equipo->marca }}
                                <span class="dato-secundario" style="display:inline;">{{ $equipo->modelo }}</span>
                            </td>
                            <td>{{ $equipo->laboratorio->nombre }}</td>
                            <td>
                                <span class="badge badge-{{ $equipo->estado }}">
                                    {{ str_replace('_', ' ', $equipo->estado) }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                ${{ number_format((float) $equipo->costo, 2) }}
                            </td>
                            <td style="text-align: center;">
                                @if ($equipo->mantenimientos_count > 0)
                                    <span style="font-weight: 600;">{{ $equipo->mantenimientos_count }}</span>
                                @else
                                    <span class="dato-secundario">0</span>
                                @endif
                            </td>
                            <td style="text-align: right; font-weight: 700; color: {{ $costoAcumulado > 0 ? 'var(--color-primario)' : 'var(--color-texto-secundario)' }};">
                                ${{ number_format($costoAcumulado, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #f1f5f9; font-weight: 700;">
                        <td colspan="6" style="text-align: right; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.05em;">
                            Total General Invertido en Mantenimientos:
                        </td>
                        <td style="text-align: right; font-size: 1.05rem; color: var(--color-primario);">
                            ${{ number_format($granTotalMantenimientos, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</section>

{{-- =========================================================================
     REPORTE 3: Equipos sin mantenimiento en el último año
     ========================================================================= --}}
<section class="tarjeta" aria-labelledby="titulo-reporte-anual">
    <div class="tarjeta-header">
        <div>
            <h2 id="titulo-reporte-anual" style="font-size: 1.25rem; margin: 0 0 0.35rem; color: var(--color-texto);">
                3. Equipos sin Mantenimiento en el Último Año
            </h2>
            <p style="margin: 0; font-size: 0.9rem; color: var(--color-texto-secundario);">
                Equipos que no registran ninguna intervención desde el <strong>{{ $limiteUnAno->format('d/m/Y') }}</strong> hasta hoy (incluye equipos sin ningún mantenimiento previo).
            </p>
        </div>
    </div>

    @if ($equiposSinMantenimientoAnual->isEmpty())
        <div class="vacio" role="region" aria-label="Sin equipos pendientes de mantenimiento">
            <p><strong>Excelente: todos los equipos cuentan con al menos un mantenimiento en el último año.</strong></p>
        </div>
    @else
        <div class="tabla-contenedor">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Código</th>
                        <th scope="col">Tipo y Modelo</th>
                        <th scope="col">Laboratorio</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Fecha Adquisición</th>
                        <th scope="col">Último Mantenimiento</th>
                        <th scope="col">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($equiposSinMantenimientoAnual as $equipo)
                        @php
                            $ultimoMantenimiento = $equipo->mantenimientos->first();
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('equipos.show', $equipo) }}" style="font-weight: 600;">
                                    {{ $equipo->codigo_inventario }}
                                </a>
                            </td>
                            <td>
                                {{ ucfirst($equipo->tipo) }} — {{ $equipo->marca }} {{ $equipo->modelo }}
                                <span class="dato-secundario">Serie: {{ $equipo->numero_serie }}</span>
                            </td>
                            <td>{{ $equipo->laboratorio->nombre }}</td>
                            <td>
                                <span class="badge badge-{{ $equipo->estado }}">
                                    {{ str_replace('_', ' ', $equipo->estado) }}
                                </span>
                            </td>
                            <td>{{ $equipo->fecha_adquisicion->format('d/m/Y') }}</td>
                            <td>
                                @if ($ultimoMantenimiento !== null)
                                    <strong>{{ $ultimoMantenimiento->fecha->format('d/m/Y') }}</strong>
                                    <span class="dato-secundario">
                                        {{ ucfirst($ultimoMantenimiento->tipo) }} — {{ $ultimoMantenimiento->tecnico_responsable }}
                                    </span>
                                @else
                                    <span class="dato-secundario" style="color: #b91c1c; font-weight: 600;">
                                        Sin mantenimientos registrados
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if ($equipo->estado !== \App\Models\Equipo::ESTADO_DE_BAJA)
                                    <a href="{{ route('mantenimientos.create', ['equipo_id' => $equipo->id]) }}" class="boton boton-secundario boton-sm">
                                        Programar
                                    </a>
                                @else
                                    <span class="dato-secundario">De baja</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection
