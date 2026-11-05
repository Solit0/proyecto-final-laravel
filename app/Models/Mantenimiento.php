<?php

namespace App\Models;

use Database\Factories\MantenimientoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mantenimiento extends Model
{
    public const TIPO_PREVENTIVO = 'preventivo';

    public const TIPO_CORRECTIVO = 'correctivo';

    /** @var list<string> */
    public const TIPOS = [self::TIPO_PREVENTIVO, self::TIPO_CORRECTIVO];

    /** @use HasFactory<MantenimientoFactory> */
    use HasFactory;

    /**
     * Campos asignables en masa.
     *
     * @var list<string>
     */
    protected $fillable = [
        'equipo_id',
        'fecha',
        'tipo',
        'descripcion',
        'tecnico_responsable',
        'costo',
    ];

    /**
     * [ENCAPSULAMIENTO] La fecha se expone como Carbon y el costo conserva dos decimales.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'costo' => 'decimal:2',
        ];
    }

    /**
     * [RELACION] Cada mantenimiento pertenece al equipo que lo recibió.
     */
    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }
}
