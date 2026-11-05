<?php

namespace App\Http\Requests;

use App\Models\Equipo;
use App\Models\Mantenimiento;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreMantenimientoRequest extends FormRequest
{
    /**
     * [SEGURIDAD] El formulario local no requiere autenticación en este caso.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * [VALIDACION] Reglas del registro de mantenimientos.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'equipo_id' => ['required', 'integer', Rule::exists('equipos', 'id')],
            'fecha' => ['required', 'date'],
            'tipo' => ['required', Rule::in(Mantenimiento::TIPOS)],
            'descripcion' => ['required', 'string'],
            'tecnico_responsable' => ['required', 'string', 'max:150'],
            'costo' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
        ];
    }

    /**
     * [VALIDACION] [REGLA-NEGOCIO] La fecha no puede ser anterior a la adquisición y el
     * equipo dado de baja no admite mantenimientos.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $equipo = Equipo::query()->find($this->integer('equipo_id'));

            if ($equipo === null) {
                return;
            }

            if ($equipo->estado === Equipo::ESTADO_DE_BAJA) {
                $validator->errors()->add(
                    'equipo_id',
                    'No se puede registrar un mantenimiento para un equipo dado de baja.',
                );

                return;
            }

            $fecha = Carbon::parse((string) $this->input('fecha'))->startOfDay();

            if ($fecha->lt($equipo->fecha_adquisicion)) {
                $validator->errors()->add(
                    'fecha',
                    'La fecha del mantenimiento no puede ser anterior a la fecha de adquisición del equipo.',
                );
            }
        });
    }

    /**
     * [VALIDACION] Mensajes en español.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'equipo_id.required' => 'Seleccione el equipo que recibió el mantenimiento.',
            'equipo_id.integer' => 'El equipo seleccionado no es válido.',
            'equipo_id.exists' => 'El equipo seleccionado no existe.',
            'fecha.required' => 'Ingrese la fecha del mantenimiento.',
            'fecha.date' => 'La fecha del mantenimiento no es válida.',
            'tipo.required' => 'Seleccione el tipo de mantenimiento.',
            'tipo.in' => 'El tipo de mantenimiento debe ser preventivo o correctivo.',
            'descripcion.required' => 'Describa el trabajo realizado.',
            'tecnico_responsable.required' => 'Ingrese el nombre del técnico responsable.',
            'tecnico_responsable.max' => 'El nombre del técnico no debe superar los 150 caracteres.',
            'costo.required' => 'Ingrese el costo del mantenimiento.',
            'costo.numeric' => 'El costo debe ser un número.',
            'costo.decimal' => 'El costo debe tener como máximo dos decimales.',
            'costo.min' => 'El costo no puede ser negativo.',
            'costo.max' => 'El costo supera el máximo permitido.',
        ];
    }

    /**
     * [VALIDACION] Nombres legibles de los campos.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'equipo_id' => 'equipo',
            'fecha' => 'fecha',
            'tipo' => 'tipo',
            'descripcion' => 'descripción',
            'tecnico_responsable' => 'técnico responsable',
            'costo' => 'costo',
        ];
    }
}
