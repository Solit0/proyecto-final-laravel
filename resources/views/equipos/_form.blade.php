<div class="campo">
    <label for="laboratorio_id">Laboratorio</label>
    <select id="laboratorio_id" name="laboratorio_id" required>
        <option value="">Seleccione un laboratorio</option>
        @foreach ($laboratorios as $laboratorio)
            <option value="{{ $laboratorio->id }}" @selected((string) old('laboratorio_id', $equipo->laboratorio_id) === (string) $laboratorio->id)>
                {{ $laboratorio->nombre }} ({{ $laboratorio->ubicacion }})
            </option>
        @endforeach
    </select>
    @error('laboratorio_id')
        <span class="error">{{ $message }}</span>
    @enderror
</div>

<div class="grid-2">
    <div class="campo">
        <label for="codigo_inventario">Código de inventario</label>
        <input id="codigo_inventario" name="codigo_inventario" type="text" placeholder="LAB-0001" maxlength="8" value="{{ old('codigo_inventario', $equipo->codigo_inventario) }}" required>
        @error('codigo_inventario')
            <span class="error">{{ $message }}</span>
        @enderror
    </div>

    <div class="campo">
        <label for="tipo">Tipo de equipo</label>
        <select id="tipo" name="tipo" required>
            <option value="">Seleccione el tipo</option>
            @foreach (\App\Models\Equipo::TIPOS as $tipo)
                <option value="{{ $tipo }}" @selected(old('tipo', $equipo->tipo) === $tipo)>
                    {{ ucfirst($tipo) }}
                </option>
            @endforeach
        </select>
        @error('tipo')
            <span class="error">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="grid-2">
    <div class="campo">
        <label for="marca">Marca</label>
        <input id="marca" name="marca" type="text" maxlength="100" value="{{ old('marca', $equipo->marca) }}" required>
        @error('marca')
            <span class="error">{{ $message }}</span>
        @enderror
    </div>

    <div class="campo">
        <label for="modelo">Modelo</label>
        <input id="modelo" name="modelo" type="text" maxlength="100" value="{{ old('modelo', $equipo->modelo) }}" required>
        @error('modelo')
            <span class="error">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="grid-2">
    <div class="campo">
        <label for="numero_serie">Número de serie</label>
        <input id="numero_serie" name="numero_serie" type="text" maxlength="100" value="{{ old('numero_serie', $equipo->numero_serie) }}" required>
        @error('numero_serie')
            <span class="error">{{ $message }}</span>
        @enderror
    </div>

    <div class="campo">
        <label for="fecha_adquisicion">Fecha de adquisición</label>
        <input id="fecha_adquisicion" name="fecha_adquisicion" type="date" max="{{ now()->toDateString() }}" value="{{ old('fecha_adquisicion', optional($equipo->fecha_adquisicion)->format('Y-m-d') ?? now()->toDateString()) }}" required>
        @error('fecha_adquisicion')
            <span class="error">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="grid-2">
    <div class="campo">
        <label for="costo">Costo ($ USD)</label>
        <input id="costo" name="costo" type="number" min="0" max="99999999.99" step="0.01" value="{{ old('costo', $equipo->costo !== null ? (float) $equipo->costo : '') }}" required>
        @error('costo')
            <span class="error">{{ $message }}</span>
        @enderror
    </div>

    <div class="campo">
        <label for="estado">Estado</label>
        <select id="estado" name="estado" required>
            <option value="">Seleccione el estado</option>
            <option value="operativo" @selected(old('estado', $equipo->estado ?? 'operativo') === 'operativo')>Operativo</option>
            <option value="en_reparacion" @selected(old('estado', $equipo->estado) === 'en_reparacion')>En reparación</option>
            <option value="de_baja" @selected(old('estado', $equipo->estado) === 'de_baja')>De baja</option>
        </select>
        @error('estado')
            <span class="error">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="campo">
    <label for="imagen">Fotografía del equipo (opcional, JPG, PNG o WebP, máx. 2 MB)</label>
    @if ($equipo->imagen)
        <div class="preview-imagen" style="margin-bottom: .6rem;">
            <img src="{{ asset('storage/' . $equipo->imagen) }}" alt="Fotografía actual de {{ $equipo->codigo_inventario }}" style="max-height: 120px; border-radius: .35rem; border: 1px solid #d0d7de;">
            <p style="margin: .25rem 0 0; font-size: .85rem; color: #57606a;">Fotografía actual. Seleccione un nuevo archivo para sustituirla.</p>
        </div>
    @endif
    <input id="imagen" name="imagen" type="file" accept="image/jpeg,image/png,image/webp">
    @error('imagen')
        <span class="error">{{ $message }}</span>
    @enderror
</div>
