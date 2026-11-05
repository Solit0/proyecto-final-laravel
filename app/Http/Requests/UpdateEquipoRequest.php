<?php

namespace App\Http\Requests;

use App\Models\Equipo;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEquipoRequest extends FormRequest
{
    /**
     * [SEGURIDAD] El alcance local de la aplicación no requiere autenticación en esta fase.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * [VALIDACION] [REGLA-NEGOCIO] Reglas de actualización del equipo.
     * Solo se ignora el ID del modelo enlazado por la ruta mediante Route Model Binding,
     * nunca un ID enviado arbitrariamente por el formulario.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $equipo = $this->route('equipo');
        $equipoId = $equipo instanceof Equipo ? $equipo->id : $equipo;

        return [
            'laboratorio_id' => ['required', 'integer', Rule::exists('laboratorios', 'id')],
            'codigo_inventario' => [
                'required',
                'string',
                'regex:/^LAB-[0-9]{4}$/',
                Rule::unique('equipos', 'codigo_inventario')->ignore($equipoId),
            ],
            'tipo' => ['required', 'string', Rule::in(Equipo::TIPOS)],
            'marca' => ['required', 'string', 'max:100'],
            'modelo' => ['required', 'string', 'max:100'],
            'numero_serie' => [
                'required',
                'string',
                'max:100',
                Rule::unique('equipos', 'numero_serie')->ignore($equipoId),
            ],
            'fecha_adquisicion' => ['required', 'date', 'before_or_equal:today'],
            'costo' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'estado' => ['required', 'string', Rule::in(Equipo::ESTADOS)],
            'imagen' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];
    }

    /**
     * [VALIDACION] Nombres amigables para los campos.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'laboratorio_id' => 'laboratorio',
            'codigo_inventario' => 'código de inventario',
            'tipo' => 'tipo de equipo',
            'marca' => 'marca',
            'modelo' => 'modelo',
            'numero_serie' => 'número de serie',
            'fecha_adquisicion' => 'fecha de adquisición',
            'costo' => 'costo',
            'estado' => 'estado',
            'imagen' => 'fotografía',
        ];
    }

    /**
     * [VALIDACION] Mensajes personalizados en español.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'laboratorio_id.required' => 'Seleccione el laboratorio al que pertenece el equipo.',
            'laboratorio_id.integer' => 'El laboratorio seleccionado no es válido.',
            'laboratorio_id.exists' => 'El laboratorio seleccionado no existe.',
            'codigo_inventario.required' => 'Ingrese el código de inventario.',
            'codigo_inventario.regex' => 'El código de inventario debe tener el formato LAB-0000 (ejemplo: LAB-0001).',
            'codigo_inventario.unique' => 'El código de inventario ya está registrado en otro equipo.',
            'tipo.required' => 'Seleccione el tipo de equipo.',
            'tipo.in' => 'El tipo de equipo debe ser computadora, proyector, impresora o switch.',
            'marca.required' => 'Ingrese la marca del equipo.',
            'marca.max' => 'La marca no debe superar los 100 caracteres.',
            'modelo.required' => 'Ingrese el modelo del equipo.',
            'modelo.max' => 'El modelo no debe superar los 100 caracteres.',
            'numero_serie.required' => 'Ingrese el número de serie.',
            'numero_serie.max' => 'El número de serie no debe superar los 100 caracteres.',
            'numero_serie.unique' => 'El número de serie ya está registrado en otro equipo.',
            'fecha_adquisicion.required' => 'Ingrese la fecha de adquisición.',
            'fecha_adquisicion.date' => 'La fecha de adquisición no es válida.',
            'fecha_adquisicion.before_or_equal' => 'La fecha de adquisición no puede ser posterior al día de hoy.',
            'costo.required' => 'Ingrese el costo del equipo.',
            'costo.numeric' => 'El costo debe ser un número.',
            'costo.decimal' => 'El costo debe tener como máximo dos decimales.',
            'costo.min' => 'El costo no puede ser negativo.',
            'costo.max' => 'El costo supera el valor máximo permitido.',
            'estado.required' => 'Seleccione el estado del equipo.',
            'estado.in' => 'El estado debe ser operativo, en reparación o de baja.',
            'imagen.image' => 'El archivo adjunto debe ser una imagen real.',
            'imagen.mimes' => 'La fotografía debe estar en formato JPG, PNG o WebP.',
            'imagen.max' => 'La fotografía no debe superar los 2048 KB (2 MB).',
        ];
    }
}
