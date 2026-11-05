{{-- [SEGURIDAD] Parcial reutilizable de formulario con salida escapada contra XSS --}}

<div class="campo">
    <label for="laboratorio_id">Laboratorio asignado <span aria-hidden="true">*</span></label>
    <select id="laboratorio_id" name="laboratorio_id" required aria-describedby="@error('laboratorio_id') error-laboratorio_id @enderror">
        <option value="">Seleccione un laboratorio</option>
        @foreach ($laboratorios as $laboratorio)
            <option value="{{ $laboratorio->id }}" @selected((string) old('laboratorio_id', $equipo->laboratorio_id) === (string) $laboratorio->id)>
                {{-- [SEGURIDAD] Salida escapada --}}
                {{ $laboratorio->nombre }} ({{ $laboratorio->ubicacion }})
            </option>
        @endforeach
    </select>
    @error('laboratorio_id')
        <span id="error-laboratorio_id" class="mensaje-error error" role="alert">{{ $message }}</span>
    @enderror
</div>

<div class="grid-2">
    <div class="campo">
        <label for="codigo_inventario">Código de inventario <span aria-hidden="true">*</span></label>
        <input id="codigo_inventario"
               name="codigo_inventario"
               type="text"
               placeholder="LAB-0001"
               maxlength="8"
               value="{{ old('codigo_inventario', $equipo->codigo_inventario) }}"
               required
               aria-describedby="ayuda-codigo @error('codigo_inventario') error-codigo_inventario @enderror">
        <small id="ayuda-codigo" class="ayuda">Formato requerido: LAB-0000 (3 letras, guion y 4 dígitos)</small>
        @error('codigo_inventario')
            <span id="error-codigo_inventario" class="mensaje-error error" role="alert">{{ $message }}</span>
        @enderror
    </div>

    <div class="campo">
        <label for="tipo">Tipo de equipo <span aria-hidden="true">*</span></label>
        <select id="tipo" name="tipo" required aria-describedby="@error('tipo') error-tipo @enderror">
            <option value="">Seleccione el tipo</option>
            @foreach (\App\Models\Equipo::TIPOS as $tipo)
                <option value="{{ $tipo }}" @selected(old('tipo', $equipo->tipo) === $tipo)>
                    {{ ucfirst($tipo) }}
                </option>
            @endforeach
        </select>
        @error('tipo')
            <span id="error-tipo" class="mensaje-error error" role="alert">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="grid-2">
    <div class="campo">
        <label for="marca">Marca <span aria-hidden="true">*</span></label>
        <input id="marca"
               name="marca"
               type="text"
               maxlength="100"
               placeholder="Ej: Lenovo, Dell, HP..."
               value="{{ old('marca', $equipo->marca) }}"
               required
               aria-describedby="@error('marca') error-marca @enderror">
        @error('marca')
            <span id="error-marca" class="mensaje-error error" role="alert">{{ $message }}</span>
        @enderror
    </div>

    <div class="campo">
        <label for="modelo">Modelo <span aria-hidden="true">*</span></label>
        <input id="modelo"
               name="modelo"
               type="text"
               maxlength="100"
               placeholder="Ej: ThinkCentre M70q, OptiPlex..."
               value="{{ old('modelo', $equipo->modelo) }}"
               required
               aria-describedby="@error('modelo') error-modelo @enderror">
        @error('modelo')
            <span id="error-modelo" class="mensaje-error error" role="alert">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="grid-2">
    <div class="campo">
        <label for="numero_serie">Número de serie <span aria-hidden="true">*</span></label>
        <input id="numero_serie"
               name="numero_serie"
               type="text"
               maxlength="100"
               placeholder="Ej: SER-XYZ12345"
               value="{{ old('numero_serie', $equipo->numero_serie) }}"
               required
               aria-describedby="@error('numero_serie') error-numero_serie @enderror">
        @error('numero_serie')
            <span id="error-numero_serie" class="mensaje-error error" role="alert">{{ $message }}</span>
        @enderror
    </div>

    <div class="campo">
        <label for="fecha_adquisicion">Fecha de adquisición <span aria-hidden="true">*</span></label>
        <input id="fecha_adquisicion"
               name="fecha_adquisicion"
               type="date"
               max="{{ now()->toDateString() }}"
               value="{{ old('fecha_adquisicion', optional($equipo->fecha_adquisicion)->format('Y-m-d') ?? now()->toDateString()) }}"
               required
               aria-describedby="@error('fecha_adquisicion') error-fecha_adquisicion @enderror">
        @error('fecha_adquisicion')
            <span id="error-fecha_adquisicion" class="mensaje-error error" role="alert">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="grid-2">
    <div class="campo">
        <label for="costo">Costo ($ USD) <span aria-hidden="true">*</span></label>
        <input id="costo"
               name="costo"
               type="number"
               min="0"
               max="99999999.99"
               step="0.01"
               placeholder="0.00"
               value="{{ old('costo', $equipo->costo !== null ? (float) $equipo->costo : '') }}"
               required
               aria-describedby="@error('costo') error-costo @enderror">
        @error('costo')
            <span id="error-costo" class="mensaje-error error" role="alert">{{ $message }}</span>
        @enderror
    </div>

    <div class="campo">
        <label for="estado">Estado inicial / actual <span aria-hidden="true">*</span></label>
        <select id="estado" name="estado" required aria-describedby="@error('estado') error-estado @enderror">
            <option value="">Seleccione el estado</option>
            <option value="operativo" @selected(old('estado', $equipo->estado ?? 'operativo') === 'operativo')>Operativo</option>
            <option value="en_reparacion" @selected(old('estado', $equipo->estado) === 'en_reparacion')>En reparación</option>
            <option value="de_baja" @selected(old('estado', $equipo->estado) === 'de_baja')>De baja</option>
        </select>
        @error('estado')
            <span id="error-estado" class="mensaje-error error" role="alert">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="campo">
    <label for="imagen">Fotografía del equipo (opcional)</label>
    @if ($equipo->imagen)
        <div class="preview-imagen">
            <img src="{{ asset('storage/' . $equipo->imagen) }}" alt="Fotografía actual de {{ $equipo->codigo_inventario }}">
            <small class="ayuda">Fotografía actual. Subir un nuevo archivo la sustituirá. Si no se selecciona ningún archivo, se conservará la imagen existente.</small>
        </div>
    @endif
    <input id="imagen"
           name="imagen"
           type="file"
           accept="image/jpeg,image/png,image/webp"
           aria-describedby="ayuda-imagen @error('imagen') error-imagen @enderror">
    <small id="ayuda-imagen" class="ayuda">Formatos permitidos: JPG, PNG o WebP (máximo 2 MB). Por seguridad, el navegador no permite conservar el archivo seleccionado si ocurre un error de validación en otros campos.</small>
    @error('imagen')
        <span id="error-imagen" class="mensaje-error error" role="alert">{{ $message }}</span>
    @enderror
</div>
