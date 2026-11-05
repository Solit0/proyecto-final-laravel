<?php

namespace App\Models;

use Database\Factories\LaboratorioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Laboratorio extends Model
{
    /** @use HasFactory<LaboratorioFactory> */
    use HasFactory;

    /**
     * Campos asignables en masa.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'ubicacion',
        'capacidad',
    ];

    /**
     * [ENCAPSULAMIENTO] La capacidad siempre se expone como entero, no como texto.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capacidad' => 'integer',
        ];
    }

    /**
     * [RELACION] Un laboratorio contiene muchos equipos.
     */
    public function equipos(): HasMany
    {
        return $this->hasMany(Equipo::class);
    }
}
